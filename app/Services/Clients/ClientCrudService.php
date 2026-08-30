<?php

namespace App\Services\Clients;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\MarketingDelivery;

class ClientCrudService
{
    public function create(array $validated): User
    {
        $data = [
            'name'    => $validated['name'],
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'],
            'country' => $validated['country'] ?? null,
            'password' => Hash::make('defaultpassword'),
        ];

        return User::create($data);
    }

    public function update(array $validated, User $client): void
    {
        $emailChanged = strtolower(trim((string) $client->email))
            !== strtolower(trim((string) ($validated['email'] ?? '')));
        $phoneChanged = preg_replace('/\D+/', '', (string) $client->phone)
            !== preg_replace('/\D+/', '', (string) $validated['phone']);

        $updates = [
            'name'    => $validated['name'],
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'],
            'country' => $validated['country'] ?? null,
        ];

        $invalidatedChannels = [];
        if ($emailChanged) {
            $updates['marketing_email_opted_out_at'] = now();
            $invalidatedChannels[] = 'email';
        }
        if ($phoneChanged) {
            $updates['marketing_whatsapp_opted_out_at'] = now();
            $invalidatedChannels[] = 'whatsapp';
        }
        if ($emailChanged || $phoneChanged) {
            $updates['marketing_ads_opted_out_at'] = now();
        }

        $client->update($updates);

        if ($invalidatedChannels !== []) {
            MarketingDelivery::query()
                ->where('user_id', $client->id)
                ->whereIn('channel', $invalidatedChannels)
                ->whereNull('sent_at')
                ->update(['failed_at' => now(), 'last_error' => 'Customer contact changed']);
        }
    }

    public function delete(User $client): void
    {
        $client->delete();
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        return User::whereIn('id', $ids)->delete();
    }
}
