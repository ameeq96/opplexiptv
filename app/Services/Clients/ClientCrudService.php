<?php

namespace App\Services\Clients;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $client->update([
            'name'    => $validated['name'],
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'],
            'country' => $validated['country'] ?? null,
        ]);
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
