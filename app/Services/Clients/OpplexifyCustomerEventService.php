<?php

namespace App\Services\Clients;

use App\Models\CustomerActivity;
use App\Models\CustomerExternalLink;
use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OpplexifyCustomerEventService
{
    private const EVENTS = [
        'checkout.started', 'checkout.tracker_attached', 'checkout.failed',
        'payment.verified', 'payment.bridge_completed', 'payment.bridge_failed',
        'share.created', 'share.opened', 'invoice.pending', 'invoice.sending',
        'invoice.sent', 'invoice.failed', 'invoice.unknown', 'contact.submitted',
    ];

    public function __construct(private CustomerActivityService $activities, private CustomerIdentityService $identity) {}

    public function receive(array $payload, string $hash, callable $checkoutKey): array
    {
        abort_if(array_diff(array_keys($payload), ['event_id', 'event_type', 'entity_type', 'entity_id', 'sequence', 'occurred_at', 'data']), 422);
        $event = Validator::make($payload, [
            'event_id' => ['required', 'string', 'max:191'],
            'event_type' => ['required', Rule::in(self::EVENTS)],
            'entity_type' => ['required', Rule::in(['streaming_order', 'shared_checkout', 'contact_message'])],
            'entity_id' => ['required', 'string', 'max:191'],
            'sequence' => ['required', 'integer', 'min:1', 'max:9007199254740991'],
            'occurred_at' => ['required', 'date'],
            'data' => ['required', 'array:reference,checkout_key,tracker,amount_minor,currency,status,customer,message,subject,invoice_status,recipient,error_code,actor_hash,package_id'],
            'data.reference' => ['sometimes', 'string', 'regex:/^[A-Za-z0-9_-]{16,128}$/'],
            'data.checkout_key' => ['sometimes', 'string', 'max:191'],
            'data.tracker' => ['sometimes', 'nullable', 'string', 'max:191', 'regex:/^track_[A-Za-z0-9_-]+$/'],
            'data.amount_minor' => ['sometimes', 'integer', 'min:1', 'max:2147483647'],
            'data.currency' => ['sometimes', 'string', 'regex:/^[A-Z]{3}$/'],
            'data.status' => ['sometimes', Rule::in(['PENDING', 'CHECKOUT_STARTED', 'PAID', 'FAILED', 'CANCELLED'])],
            'data.customer' => ['sometimes', 'array:name,email,phone'],
            'data.customer.name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'data.customer.email' => ['sometimes', 'nullable', 'email', ($payload['event_type'] ?? '') === 'contact.submitted' ? 'max:254' : 'max:191'],
            'data.customer.phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'data.message' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'data.subject' => ['sometimes', 'nullable', 'string', 'max:191'],
            'data.invoice_status' => ['sometimes', Rule::in(['PENDING', 'SENDING', 'SENT', 'FAILED', 'UNKNOWN', 'RETRY', 'BLOCKED'])],
            'data.recipient' => ['sometimes', 'nullable', 'email', 'max:191'],
            'data.error_code' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_.:-]+$/'],
            'data.actor_hash' => ['sometimes', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'data.package_id' => ['sometimes', 'integer', 'min:1'],
        ])->validate();
        $group = explode('.', $event['event_type'])[0];
        $expectedEntity = match ($group) {
            'share' => 'shared_checkout', 'contact' => 'contact_message', default => 'streaming_order',
        };
        abort_unless($event['entity_type'] === $expectedEntity, 422, 'Event entity does not match its type.');
        abort_if($expectedEntity === 'streaming_order' && empty($event['data']['reference']), 422, 'Order reference is required.');
        abort_if($group !== 'contact' && isset($event['data']['message']), 422);
        abort_if($group === 'share' && isset($event['data']['customer']), 422, 'A shared link does not identify its payer.');

        return DB::transaction(function () use ($event, $hash, $checkoutKey, $group) {
            $data = $event['data'];
            $link = CustomerExternalLink::query()->createOrFirst([
                'source' => 'opplexify', 'entity_type' => $event['entity_type'], 'external_id' => $event['entity_id'],
            ]);
            $link = CustomerExternalLink::query()->whereKey($link->id)->lockForUpdate()->firstOrFail();
            $order = null;
            if ($event['entity_type'] === 'streaming_order') {
                $order = Order::query()->where('checkout_key', $checkoutKey($data['reference']))
                    ->whereIn('payment_provider', ['safepay', 'safepay_sandbox'])
                    ->whereIn('payment_status', ['paid', 'test_paid'])->first();
                if ($order) {
                    abort_if(isset($data['tracker']) && $order->provider_transaction_id !== $data['tracker'], 409, 'Payment tracker mismatch.');
                    abort_if(isset($data['currency']) && $order->paid_currency !== $data['currency'], 409, 'Payment currency mismatch.');
                    abort_if(isset($data['amount_minor']) && (int) round((float) $order->paid_amount * 100) !== $data['amount_minor'], 409, 'Payment amount mismatch.');
                    abort_if($link->order_id !== null && (int) $link->order_id !== (int) $order->id, 409, 'Order linkage conflict.');
                }
            }
            $customer = $data['customer'] ?? [];
            $userId = $order?->user_id ?? $link->user_id;
            if ($event['entity_type'] === 'contact_message' && $userId === null) {
                $userId = $this->identity->resolve($customer['email'] ?? null, $customer['phone'] ?? null)?->id;
            }
            $activity = $this->activities->record([
                'source' => 'opplexify', 'event_id' => $event['event_id'], 'event_type' => $event['event_type'],
                'entity_type' => $event['entity_type'], 'entity_id' => $event['entity_id'],
                'source_sequence' => $event['sequence'], 'occurred_at' => $event['occurred_at'],
                'payload_hash' => $hash, 'user_id' => $userId, 'order_id' => $order?->id ?? $link->order_id,
                'contact_name' => $customer['name'] ?? null, 'contact_email' => $customer['email'] ?? null,
                'contact_phone' => $customer['phone'] ?? null, 'message' => $data['message'] ?? null,
                'details' => Arr::except($data, ['customer', 'message']),
            ]);
            if ($activity->wasRecentlyCreated) {
                $state = $link->state ?? [];
                if (($state[$group]['sequence'] ?? 0) < $event['sequence']) {
                    $currentData = array_replace($state[$group]['data'] ?? [], Arr::except($data, ['customer', 'message']));
                    if (!isset($data['error_code'])) {
                        unset($currentData['error_code']);
                    }
                    $state[$group] = [
                        'sequence' => $event['sequence'], 'event_type' => $event['event_type'],
                        'data' => $currentData,
                    ];
                }
                $link->forceFill([
                    'state' => $state, 'last_sequence' => max($link->last_sequence, $event['sequence']),
                    'user_id' => $userId, 'order_id' => $order?->id ?? $link->order_id,
                ])->save();
            }
            if ($order) {
                // Correlate anonymous checkout history only to the verified payer, never to the link creator.
                CustomerActivity::query()->where('source', 'opplexify')->where('entity_type', 'streaming_order')
                    ->where('entity_id', $event['entity_id'])->whereNull('user_id')
                    ->update(['user_id' => $order->user_id, 'order_id' => $order->id]);
                if (preg_match('/^shared_[a-f0-9]{64}$/', $data['checkout_key'] ?? '') === 1) {
                    $shared = CustomerExternalLink::query()->createOrFirst([
                        'source' => 'opplexify', 'entity_type' => 'shared_checkout', 'external_id' => $data['checkout_key'],
                    ]);
                    $shared = CustomerExternalLink::query()->whereKey($shared->id)->lockForUpdate()->firstOrFail();
                    abort_if($shared->order_id !== null && (int) $shared->order_id !== (int) $order->id, 409, 'Shared checkout linkage conflict.');
                    $shared->forceFill(['order_id' => $order->id])->save();
                    CustomerActivity::query()->where('source', 'opplexify')->where('entity_type', 'shared_checkout')
                        ->where('entity_id', $data['checkout_key'])->whereNull('order_id')->whereNull('user_id')
                        ->update(['order_id' => $order->id]);
                }
            }
            return ['received' => true, 'event_id' => $event['event_id']];
        }, 3);
    }
}
