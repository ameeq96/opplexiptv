<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\CheckoutDraft;
use App\Models\Device;
use App\Models\MarketingDelivery;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\Clients\CustomerIdentityService;
use App\Services\OrderPaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpplexifyOrderController extends Controller
{
    public function prepare(Request $request): JsonResponse
    {
        $this->authenticate($request, '/integrations/opplexify/orders/prepare');
        $data = $this->payload($request, [
            'device_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $package = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->whereIn('type', ['iptv', 'reseller'])
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->findOrFail($data['package_id']);
        $deviceId = isset($data['device_id']) ? (int) $data['device_id'] : null;

        if ($package->type === 'iptv') {
            abort_unless($deviceId && Device::query()->whereKey($deviceId)->exists(), 422, 'A valid device is required.');
        } else {
            abort_if($deviceId !== null, 422, 'Reseller checkout does not accept a device.');
        }

        $baseMinor = (int) round((float) $package->price_amount * 100);
        $expectedMinor = $baseMinor;
        if ($data['currency'] === 'USD') {
            $expectedMinor += array_sum(array_map(
                static fn (int $sample): int => (int) round($baseMinor * $sample / 1199),
                [71, 5, 12, 24]
            ));
        }
        abort_unless($expectedMinor === (int) $data['amount_minor'], 409, 'Checkout amount does not match the package.');

        return $this->transaction(function () use ($data, $deviceId, $package): array {
            $now = now();
            $draft = CheckoutDraft::firstOrCreate(
                ['token' => $this->checkoutKey($data['reference'])],
                [
                    'package_id' => $package->id,
                    'device_id' => $deviceId,
                    'vendor' => $package->vendor,
                    'locale' => 'en',
                    'last_activity_at' => $now,
                    'retention_expires_at' => $now->copy()->addDays(
                        max(1, (int) config('services.marketing.draft_retention_days', 30))
                    ),
                ]
            );
            $draft = CheckoutDraft::query()->whereKey($draft->id)->lockForUpdate()->firstOrFail();
            abort_unless(
                (int) $draft->package_id === (int) $package->id
                    && (int) $draft->device_id === (int) $deviceId,
                409,
                'Checkout reference already belongs to another selection.'
            );

            if (!$draft->completed_at) {
                $draft->forceFill([
                    'last_activity_at' => $now,
                    'retention_expires_at' => $now->copy()->addDays(
                        max(1, (int) config('services.marketing.draft_retention_days', 30))
                    ),
                ])->save();
            }

            return ['prepared' => true];
        });
    }

    public function complete(
        Request $request,
        CustomerIdentityService $identity,
        OrderPaymentService $payments
    ): JsonResponse {
        $this->authenticate($request, '/integrations/opplexify/orders/complete');
        $data = $this->payload($request, [
            'tracker' => ['required', 'string', 'max:191', 'regex:/^track_[A-Za-z0-9_-]+$/'],
            'customer' => ['required', 'array:first_name,last_name,email,phone'],
            'customer.first_name' => ['nullable', 'string', 'max:191'],
            'customer.last_name' => ['nullable', 'string', 'max:191'],
            'customer.email' => ['required', 'email', 'max:191'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
        ]);
        $name = trim(($data['customer']['first_name'] ?? '') . ' ' . ($data['customer']['last_name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'customer.first_name' => 'A customer name is required.',
            ]);
        }

        return $this->transaction(function () use ($data, $identity, $name, $payments): array {
            $key = $this->checkoutKey($data['reference']);
            $draft = CheckoutDraft::query()->where('token', $key)->lockForUpdate()->first();
            $order = Order::query()->where('checkout_key', $key)->lockForUpdate()->first();
            abort_unless($draft || $order, 404, 'Prepared checkout not found.');
            if ($draft) {
                abort_unless((int) $draft->package_id === (int) $data['package_id'], 409, 'Checkout package does not match.');
            }

            if ($draft?->completed_order_id) {
                $linkedOrder = Order::query()->whereKey($draft->completed_order_id)->lockForUpdate()->first();
                abort_unless(
                    $linkedOrder && (!$order || (int) $order->id === (int) $linkedOrder->id),
                    409,
                    'Checkout order linkage does not match.'
                );
                $order = $linkedOrder;
            }

            $sandbox = $data['environment'] === 'sandbox';
            $provider = $sandbox ? 'safepay_sandbox' : 'safepay';
            if ($order) {
                abort_unless(
                    $order->checkout_key === $key
                        && (int) $order->package_id === (int) $data['package_id']
                        && (!$draft || (int) $order->device_id === (int) $draft->device_id)
                        && $order->payment_provider === $provider
                        && $order->provider_transaction_id === $data['tracker']
                        && $order->payment_status === ($sandbox ? 'test_paid' : 'paid')
                        && $order->currency === $data['currency']
                        && $order->paid_currency === $data['currency']
                        && (int) round((float) $order->paid_amount * 100) === (int) $data['amount_minor']
                        && (int) round((float) ($order->sell_price ?? $order->price) * 100) === ($sandbox ? 0 : (int) $data['amount_minor']),
                    409,
                    'Checkout payment does not match the recorded order.'
                );

                if ($draft && (!$draft->completed_order_id || !$draft->completed_at)) {
                    $this->completeDraft($draft, $order);
                }

                return ['order_id' => $order->id, 'created' => false];
            }

            abort_if($draft->completed_at, 409, 'Checkout has already been completed.');
            abort_if(
                Order::query()->where('payment_provider', $provider)
                    ->where('provider_transaction_id', $data['tracker'])->exists(),
                409,
                'Payment tracker already belongs to another order.'
            );

            $package = Package::query()
                ->whereIn('type', ['iptv', 'reseller'])
                ->whereIn('vendor', ['opplex', 'starshare'])
                ->findOrFail($data['package_id']);
            abort_if($package->type === 'iptv' && !$draft->device_id, 409, 'Prepared checkout device is no longer available.');

            $email = User::normalizeEmail($data['customer']['email']);
            $phone = $data['customer']['phone'] ?? null;
            $user = $identity->resolve($email, null)
                ?? User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => mb_substr($name, 0, 191),
                        'phone' => $phone,
                        'password' => bcrypt(Str::random(32)),
                    ]
                );

            $now = now();
            $amount = (int) $data['amount_minor'] / 100;
            $saleAmount = $sandbox ? 0.0 : $amount;
            $cost = $sandbox ? 0.0 : (float) ($package->cost_price ?? 0);
            $duration = (int) ($package->duration_months ?? 0);
            $type = $package->type === 'reseller' ? 'reseller' : 'package';
            $note = ($sandbox ? 'SafePay TEST MODE — no real payment' : 'SafePay customer')
                . ($sandbox ? "\nquoted_amount: " . number_format($amount, 2, '.', '') . "\ncurrency: " . $data['currency'] : '')
                . "\n" . json_encode(
                    ['name' => $name, 'email' => $email, 'phone' => $phone],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                );
            $order = Order::create([
                'checkout_key' => $key,
                'user_id' => $user->id,
                'package' => $package->title,
                'custom_package' => $package->title,
                'package_id' => $package->id,
                'device_id' => $draft->device_id,
                'type' => $type,
                'price' => $saleAmount,
                'sell_price' => $saleAmount,
                'subtotal' => $saleAmount,
                'discount' => 0,
                'cost_price' => $cost,
                'profit' => $saleAmount - $cost,
                'duration' => $duration,
                'credits' => (int) ($package->credits ?? 0),
                'status' => 'pending',
                'payment_method' => 'SafePay',
                'custom_payment_method' => null,
                'payment_status' => 'unpaid',
                'currency' => $data['currency'],
                'buying_date' => $now,
                'expiry_date' => !$sandbox && $duration > 0 ? $now->copy()->addMonths($duration) : null,
                'note' => $note,
                'messaged_by' => false,
                'messaged_at' => null,
                'iptv_username' => null,
                'locale' => 'en',
            ]);

            if ($sandbox) {
                $order->forceFill([
                    'payment_status' => 'test_paid',
                    'payment_provider' => $provider,
                    'provider_transaction_id' => $data['tracker'],
                    'paid_amount' => $amount,
                    'paid_currency' => $data['currency'],
                    'paid_at' => null,
                ])->save();
            } else {
                $payments->markPaid($order, $provider, $data['tracker'], $amount, $data['currency']);
            }

            $this->completeDraft($draft, $order);
            $admins = Admin::query()
                ->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT])
                ->get();
            Notification::send($admins, new NewOrderNotification([
                'title' => $sandbox ? 'Test SafePay checkout' : 'New order received',
                'body' => $sandbox
                    ? "{$name} completed a SafePay test checkout ({$type}). No real payment was made."
                    : "{$name} placed an order ({$type}).",
                'order_id' => $order->id,
                'package' => $package->title,
                'type' => $type,
                'client' => $name,
                'phone' => $phone,
                'payment' => 'SafePay',
                'price' => $amount,
                'created' => $now->toDateTimeString(),
            ]));

            return ['order_id' => $order->id, 'created' => true];
        });
    }

    private function authenticate(Request $request, string $path): void
    {
        $secret = (string) config('services.opplexify.shared_secret');
        abort_if($secret === '', 503, 'Order integration is not configured.');
        abort_unless($request->isMethod('POST') && $request->getPathInfo() === $path, 401, 'Invalid integration request.');

        $timestamp = (string) $request->header('X-Opplexify-Timestamp', '');
        $signature = (string) $request->header('X-Opplexify-Signature', '');
        abort_unless(
            preg_match('/^\d{10}$/', $timestamp) === 1 && abs(time() - (int) $timestamp) <= 300,
            401,
            'Expired or invalid timestamp.'
        );
        $expected = hash_hmac(
            'sha256',
            "POST\n" . $path . "\n" . $timestamp . "\n" . hash('sha256', $request->getContent()),
            $secret
        );
        abort_unless(
            preg_match('/^[a-f0-9]{64}$/', $signature) === 1 && hash_equals($expected, $signature),
            401,
            'Invalid integration signature.'
        );
        abort_unless($request->isJson(), 415, 'A JSON request is required.');
    }

    private function payload(Request $request, array $rules): array
    {
        $payload = $request->json()->all();
        $currency = strtoupper((string) config('services.app.default_currency', 'USD'));
        $payload['currency'] ??= $currency;

        return Validator::make($payload, array_merge([
            'reference' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{16,128}$/'],
            'package_id' => ['required', 'integer', 'min:1'],
            'amount_minor' => ['required', 'integer', 'min:1', 'max:99999999'],
            'currency' => ['required', 'string', 'size:3', 'in:' . $currency],
            'environment' => ['required', 'in:sandbox,production'],
        ], $rules))->validate();
    }

    private function checkoutKey(string $reference): string
    {
        $hex = substr(hash('sha256', 'opplexify-order:' . $reference), 0, 32);
        $hex[12] = '4';
        $hex[16] = dechex((hexdec($hex[16]) & 3) | 8);

        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }

    private function completeDraft(CheckoutDraft $draft, Order $order): void
    {
        $now = now();
        $draft->forceFill([
            'user_id' => $order->user_id,
            'name' => null,
            'email' => null,
            'phone' => null,
            'email_consented_at' => null,
            'whatsapp_consented_at' => null,
            'ads_consented_at' => null,
            'consent_version' => null,
            'consent_ip_hash' => null,
            'last_activity_at' => $now,
            'completed_order_id' => $order->id,
            'completed_at' => $draft->completed_at ?: $now,
        ])->save();

        MarketingDelivery::query()
            ->where('checkout_draft_id', $draft->id)
            ->where('workflow', 'abandoned')
            ->whereNull('sent_at')
            ->update([
                'failed_at' => $now,
                'last_error' => 'Checkout completed',
                'processing_at' => null,
                'processing_token' => null,
            ]);
    }

    private function transaction(callable $operation): JsonResponse
    {
        try {
            return response()->json(DB::transaction($operation, 3));
        } catch (QueryException $exception) {
            if (!in_array($exception->errorInfo[0] ?? null, ['23000', '23505'], true)) {
                throw $exception;
            }

            return response()->json(['message' => 'Checkout or payment reference conflicts with an existing record.'], 409);
        }
    }
}
