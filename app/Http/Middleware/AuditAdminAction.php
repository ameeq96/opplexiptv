<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditAdminAction
{
    private const MUTATING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    private const SENSITIVE_READ_ROUTES = [
        'admin.clients.export.facebook',
        'admin.trial_clicks.export',
    ];

    private const SENSITIVE_KEY_PATTERN = '/password|passwd|secret|token|captcha|otp|two.?factor|2fa|recovery|authorization|cookie|api[_-]?key|private[_-]?key|card|cvv|cvc|payment[_-]?(proof|screenshot)|^code$/i';

    public function handle(Request $request, Closure $next): mixed
    {
        $admin = auth('admin')->user();
        $shouldAudit = ($request->routeIs('admin.*') || $request->is('admin/*'))
            && (in_array($request->method(), self::MUTATING_METHODS, true)
                || $request->routeIs(...self::SENSITIVE_READ_ROUTES));

        $responseStatus = null;

        try {
            $response = $next($request);
            $responseStatus = method_exists($response, 'getStatusCode')
                ? $response->getStatusCode()
                : null;

            return $response;
        } catch (Throwable $exception) {
            $responseStatus = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : ($exception instanceof ValidationException ? 422 : 500);

            throw $exception;
        } finally {
            $admin ??= auth('admin')->user();

            if ($shouldAudit && ($admin !== null || $request->routeIs('admin.login.attempt'))) {
                $this->record($request, $admin, $responseStatus);
            }
        }
    }

    private function record(Request $request, ?Admin $admin, ?int $responseStatus): void
    {
        try {
            [$targetType, $targetId] = $this->resolveTarget($request);

            AdminAuditLog::create([
                'admin_id' => $admin?->getAuthIdentifier(),
                'admin_name' => $admin?->name,
                'admin_email' => $admin?->email
                    ?? ($request->routeIs('admin.login.attempt') && is_string($request->input('email'))
                        ? Str::lower($request->input('email'))
                        : null),
                'action' => $this->resolveAction($request),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => $this->sanitizedPath($request),
                'target_type' => $targetType,
                'target_id' => $targetId,
                'request_data' => $this->requestMetadata($request),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'response_status' => $responseStatus,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function resolveAction(Request $request): string
    {
        $route = $request->route();
        $action = is_object($route) ? $route->getActionMethod() : null;

        if (! is_string($action) || $action === '' || $action === '__invoke') {
            $action = Str::afterLast((string) $route?->getName(), '.');
        }

        return Str::limit($action !== '' ? $action : strtolower($request->method()), 100, '');
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveTarget(Request $request): array
    {
        $parameters = $request->route()?->parameters() ?? [];

        foreach (array_reverse($parameters, true) as $name => $value) {
            if (preg_match(self::SENSITIVE_KEY_PATTERN, (string) $name) === 1) {
                continue;
            }

            if ($value instanceof Model) {
                return [$value->getMorphClass(), (string) $value->getRouteKey()];
            }

            if (is_scalar($value) && (string) $value !== '') {
                return [(string) $name, Str::limit((string) $value, 255, '')];
            }
        }

        return [null, null];
    }

    private function sanitizedPath(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');

        foreach ($request->route()?->parameters() ?? [] as $name => $value) {
            if (preg_match(self::SENSITIVE_KEY_PATTERN, (string) $name) !== 1 || ! is_scalar($value)) {
                continue;
            }

            $path = str_replace([(string) $value, rawurlencode((string) $value)], '[REDACTED]', $path);
        }

        return Str::limit($path, 2000, '');
    }

    /**
     * @return array<string, mixed>
     */
    private function requestMetadata(Request $request): array
    {
        $metadata = [
            'input' => $this->sanitize($request->request->all()),
            'query' => $this->sanitize($request->query->all()),
            'file_fields' => $this->fileFields($request->allFiles()),
        ];

        $encoded = json_encode($metadata);
        if (is_string($encoded) && strlen($encoded) > 20000) {
            return [
                'input_fields' => array_keys($request->request->all()),
                'query_fields' => array_keys($request->query->all()),
                'file_fields' => $metadata['file_fields'],
                'truncated' => true,
            ];
        }

        return $metadata;
    }

    private function sanitize(mixed $value, ?string $key = null, int $depth = 0): mixed
    {
        if ($key !== null && preg_match(self::SENSITIVE_KEY_PATTERN, $key) === 1) {
            return '[REDACTED]';
        }

        if ($value instanceof UploadedFile) {
            return '[FILE]';
        }

        if (is_array($value)) {
            if ($depth >= 4) {
                return '[MAX DEPTH]';
            }

            $sanitized = [];
            foreach ($value as $childKey => $childValue) {
                if (count($sanitized) >= 50) {
                    $sanitized['_truncated'] = true;
                    break;
                }

                $sanitized[$childKey] = $this->sanitize($childValue, (string) $childKey, $depth + 1);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return Str::limit($value, 1000, '');
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return '[UNSUPPORTED VALUE]';
    }

    /**
     * @param  array<string, mixed>  $files
     * @return array<int, string>
     */
    private function fileFields(array $files, string $prefix = ''): array
    {
        $fields = [];

        foreach ($files as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $fields = array_merge($fields, $this->fileFields($value, $path));
            } else {
                $fields[] = $path;
            }
        }

        return array_slice($fields, 0, 50);
    }
}
