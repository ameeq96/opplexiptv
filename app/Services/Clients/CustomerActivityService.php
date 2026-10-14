<?php

namespace App\Services\Clients;

use App\Models\CustomerActivity;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CustomerActivityService
{
    public function __construct(private CustomerIdentityService $identity) {}

    public function record(array $attributes): CustomerActivity
    {
        $attributes = Arr::only($attributes, [
            'source', 'event_id', 'event_type', 'entity_type', 'entity_id',
            'user_id', 'order_id', 'admin_id', 'contact_name', 'contact_email',
            'contact_phone', 'message', 'details', 'payload_hash', 'source_sequence', 'occurred_at',
        ]);
        $attributes['event_id'] ??= (string) Str::uuid();
        $attributes['payload_hash'] ??= hash('sha256', json_encode(
            $this->canonical(Arr::except($attributes, ['user_id', 'order_id', 'payload_hash'])),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ));
        $attributes['occurred_at'] ??= now();
        if (!array_key_exists('user_id', $attributes)) {
            $attributes['user_id'] = $this->identity->resolve(
                $attributes['contact_email'] ?? null, $attributes['contact_phone'] ?? null
            )?->id;
        }

        $activity = CustomerActivity::query()->createOrFirst(
            Arr::only($attributes, ['source', 'event_id']),
            Arr::except($attributes, ['source', 'event_id'])
        );
        abort_unless(hash_equals($activity->payload_hash, $attributes['payload_hash']), 409, 'Event identity already has different contents.');

        return $activity;
    }

    private function canonical(array $data): array
    {
        if (!array_is_list($data)) {
            ksort($data);
        }
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->canonical($value);
            }
        }
        return $data;
    }
}
