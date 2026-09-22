<?php

namespace App\Services\Clients;

use App\Models\CheckoutDraft;
use App\Models\Digital\DigitalOrder;
use App\Models\TrialClick;
use App\Models\User;

class CustomerIdentityService
{
    public function resolve(?string $email, ?string $phone): ?User
    {
        $email = User::normalizeEmail($email);
        $phone = User::normalizePhone($phone);

        $emailMatches = $email === null
            ? collect()
            : User::query()->where('email_normalized', $email)->limit(2)->get();
        $phoneMatches = $phone === null
            ? collect()
            : User::query()->where('phone_normalized', $phone)->limit(2)->get();

        if ($email !== null) {
            if ($emailMatches->count() !== 1) {
                return null;
            }

            $emailUser = $emailMatches->first();
            $phoneUser = $phoneMatches->count() === 1 ? $phoneMatches->first() : null;

            return $phoneUser && ! $emailUser->is($phoneUser) ? null : $emailUser;
        }

        return $phoneMatches->count() === 1 ? $phoneMatches->first() : null;
    }

    public function linkUser(User $user): void
    {
        $email = User::normalizeEmail($user->email);
        $phone = User::normalizePhone($user->phone);

        if ($user->email_normalized !== $email || $user->phone_normalized !== $phone) {
            $user->forceFill([
                'email_normalized' => $email,
                'phone_normalized' => $phone,
            ])->saveQuietly();
        }

        if ($phone !== null) {
            $phoneUser = $this->resolve(null, $phone);

            TrialClick::query()
                ->where('phone_normalized', $phone)
                ->update(['user_id' => $phoneUser?->id]);
        }

        if ($email !== null || $phone !== null) {
            CheckoutDraft::query()
                ->whereNull('completed_order_id')
                ->where(function ($query) use ($email, $phone) {
                    if ($email !== null) {
                        $query->where('email_normalized', $email);
                        if ($phone !== null) {
                            $query->orWhere(function ($phoneQuery) use ($phone) {
                                $phoneQuery->whereNull('email_normalized')
                                    ->where('phone_normalized', $phone);
                            });
                        }
                    } else {
                        $query->where('phone_normalized', $phone);
                    }
                })
                ->get()
                ->each(function (CheckoutDraft $draft) {
                    $resolvedUserId = $this->resolve($draft->email, $draft->phone)?->id;
                    $currentUserId = $draft->user_id === null ? null : (int) $draft->user_id;

                    if ($currentUserId !== $resolvedUserId) {
                        $draft->forceFill(['user_id' => $resolvedUserId])->save();
                    }
                });
        }

        if ($email !== null) {
            DigitalOrder::query()
                ->whereNull('user_id')
                ->whereRaw('LOWER(TRIM(customer_email)) = ?', [$email])
                ->get()
                ->each(function (DigitalOrder $order) use ($user) {
                    if ($this->resolve($order->customer_email, $order->customer_phone)?->is($user)) {
                        $order->forceFill(['user_id' => $user->id])->save();
                    }
                });
        }
    }
}
