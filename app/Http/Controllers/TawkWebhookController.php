<?php

namespace App\Http\Controllers;

use App\Services\Clients\TawkWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JsonException;

class TawkWebhookController extends Controller
{
    public function __invoke(Request $request, TawkWebhookService $webhooks): JsonResponse
    {
        $secret = (string) config('services.tawk.webhook_secret', '');
        $propertyId = (string) config('services.tawk.property_id', '');
        abort_if($secret === '' || $propertyId === '', 503, 'Tawk webhook is not configured.');

        $body = $request->getContent();
        abort_if(strlen($body) > 1000000, 413, 'Webhook payload is too large.');
        $signature = (string) $request->header('X-Tawk-Signature', '');
        abort_unless(
            preg_match('/^[a-f0-9]{40}$/i', $signature) === 1
                && hash_equals(hash_hmac('sha1', $body, $secret), strtolower($signature)),
            401,
            'Invalid webhook signature.'
        );
        abort_unless($request->isJson(), 415, 'A JSON payload is required.');

        $eventId = (string) $request->header('X-Hook-Event-Id', '');
        abort_unless(preg_match('/^[A-Za-z0-9._:-]{1,191}$/', $eventId) === 1, 422, 'A valid event ID is required.');

        try {
            $payload = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            abort(422, 'Invalid JSON payload.');
        }
        abort_unless(is_array($payload) && !array_is_list($payload), 422, 'Invalid webhook payload.');
        $transcript = ($payload['event'] ?? null) === 'chat:transcript_created';

        $rules = [
            'event' => ['required', Rule::in(['chat:start', 'chat:end', 'chat:transcript_created'])],
            'time' => ['required', 'string', 'max:64', 'date'],
            'property' => ['required', 'array'],
            'property.id' => ['required', 'string', 'max:100'],
            'chatId' => [Rule::requiredIf(!$transcript), 'string', 'max:191'],
            'visitor' => [Rule::requiredIf(!$transcript), 'array'],
            'visitor.name' => ['nullable', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email:rfc', 'max:254'],
            'message' => ['nullable', 'array'],
            'message.text' => ['nullable', 'string', 'max:10000'],
            'chat' => [Rule::requiredIf($transcript), 'array'],
            'chat.id' => [Rule::requiredIf($transcript), 'string', 'max:191'],
            'chat.visitor' => [Rule::requiredIf($transcript), 'array'],
            'chat.visitor.name' => ['nullable', 'string', 'max:255'],
            'chat.visitor.email' => ['nullable', 'email:rfc', 'max:254'],
            'chat.messages' => [Rule::requiredIf($transcript), 'array', 'max:1000'],
            'chat.messages.*' => ['array'],
            'chat.messages.*.type' => ['required', 'string', 'max:32'],
            'chat.messages.*.msg' => ['nullable', 'string', 'max:10000'],
            'chat.messages.*.time' => ['required', 'string', 'max:64', 'date'],
            'chat.messages.*.sender' => ['required', 'array'],
            'chat.messages.*.sender.t' => ['required', Rule::in(['a', 'v', 's'])],
            'chat.messages.*.sender.n' => ['nullable', 'string', 'max:191'],
            'chat.messages.*.attchs' => ['nullable', 'array', 'max:20'],
            'chat.messages.*.attchs.*' => ['array'],
            'chat.messages.*.attchs.*.content' => ['nullable', 'array'],
            'chat.messages.*.attchs.*.content.file' => ['nullable', 'array'],
            'chat.messages.*.attchs.*.content.file.name' => ['nullable', 'string', 'max:191'],
        ];
        $data = Validator::make($payload, $rules)->validate();
        abort_unless(hash_equals($propertyId, $data['property']['id']), 403, 'Unexpected Tawk property.');

        $created = $webhooks->receive($data, $eventId, hash('sha256', $body));

        return response()->json(['ok' => true, 'duplicate' => !$created])->header('Cache-Control', 'no-store');
    }
}
