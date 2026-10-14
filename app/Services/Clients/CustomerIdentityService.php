<?php

namespace App\Services\Clients;

use App\Models\CheckoutDraft;
use App\Models\CustomerActivity;
use App\Models\CustomerConversation;
use App\Models\Digital\DigitalOrder;
use App\Models\Order;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerIdentityService
{
    public const REVIEW_TYPES = [
        'trial_click' => TrialClick::class,
        'checkout_draft' => CheckoutDraft::class,
        'digital_order' => DigitalOrder::class,
        'conversation' => CustomerConversation::class,
        'activity' => CustomerActivity::class,
    ];

    public function unlinkedQuery(string $type): Builder
    {
        abort_unless(isset(self::REVIEW_TYPES[$type]), 422, 'Invalid activity type.');

        return self::REVIEW_TYPES[$type]::query()->whereNull('user_id')
            ->when($type === 'activity', function ($query) {
                $query->where(function ($unmatched) {
                    $unmatched->whereNull('entity_type')->orWhere('entity_type', '!=', 'shared_checkout')->orWhereNull('order_id');
                });
            });
    }

    public function previewRecord(string $type, Model $record): array
    {
        $email = match ($type) {
            'digital_order' => $record->customer_email,
            'conversation' => $record->visitor_email,
            'activity' => $record->contact_email,
            'trial_click' => $record->contact_email,
            default => $record->email,
        };
        $phone = match ($type) {
            'digital_order' => $record->customer_phone,
            'conversation' => $record->visitor_phone,
            'activity' => $record->contact_phone,
            'trial_click' => $record->phone_normalized,
            default => $record->phone,
        };
        $name = match ($type) {
            'digital_order' => $record->customer_name,
            'conversation' => $record->visitor_name,
            'activity' => $record->contact_name,
            'trial_click' => $record->contact_name,
            default => $record->name,
        };
        $normalizedEmail = User::normalizeEmail($email);
        $normalizedPhone = User::normalizePhone($phone);
        $emailMatches = $normalizedEmail === null ? collect()
            : User::query()->where('email_normalized', $normalizedEmail)->limit(2)->get(['id', 'name', 'email', 'phone']);
        $phoneMatches = $normalizedPhone === null ? collect()
            : User::query()->where('phone_normalized', $normalizedPhone)->limit(2)->get(['id', 'name', 'email', 'phone']);
        $candidate = null;
        $reason = 'No unique verified customer match. Leave this record separate.';
        if ($normalizedEmail !== null && $emailMatches->count() === 1 && $phoneMatches->count() <= 1) {
            $emailUser = $emailMatches->first();
            if ($phoneMatches->isEmpty() || $phoneMatches->first()->is($emailUser)) {
                $candidate = $emailUser;
                $reason = 'Unique normalized email match; confirm the contact belongs to this customer.';
            }
        } elseif ($normalizedEmail === null && $phoneMatches->count() === 1) {
            $candidate = $phoneMatches->first();
            $reason = 'Unique normalized phone match; shared/recycled numbers still require verification.';
        }

        $orderId = $type === 'checkout_draft' ? $record->completed_order_id
            : ($type === 'activity' ? $record->order_id : null);
        if ($orderId) {
            $orderUser = Order::query()->find($orderId)?->user;
            $candidate = $orderUser;
            $reason = $orderUser ? 'Existing completed order identifies this customer.' : 'Referenced order has no customer; manual investigation required.';
        }
        if ($type === 'activity' && $record->entity_type === 'shared_checkout') {
            $candidate = null;
            $reason = 'Payment-link actor is anonymous. An associated payer order does not identify the person who created or opened the link.';
        }

        $fingerprint = hash('sha256', json_encode([
            $type, (string) $record->getKey(), $record->user_id, $email, $phone,
            $orderId, (string) $record->updated_at, $candidate?->id,
        ], JSON_THROW_ON_ERROR));

        return compact('record', 'name', 'email', 'phone', 'candidate', 'reason', 'fingerprint');
    }

    public function linkReviewed(string $type, int $id, int $userId, string $fingerprint, int $adminId): void
    {
        abort_unless(isset(self::REVIEW_TYPES[$type]), 422, 'Invalid activity type.');

        DB::transaction(function () use ($type, $id, $userId, $fingerprint, $adminId) {
            $record = self::REVIEW_TYPES[$type]::query()->lockForUpdate()->findOrFail($id);
            abort_unless($record->user_id === null, 409, 'This record is already linked. Refresh the review.');
            User::query()->lockForUpdate()->findOrFail($userId);
            $preview = $this->previewRecord($type, $record);
            abort_unless(
                $preview['candidate'] && (int) $preview['candidate']->id === $userId
                    && hash_equals($preview['fingerprint'], $fingerprint),
                409,
                'The record or identity match changed. Preview and verify it again.'
            );
            $record->forceFill(['user_id' => $userId])->save();
            app(CustomerActivityService::class)->record([
                'source' => 'crm',
                'event_id' => (string) Str::uuid(),
                'event_type' => 'identity.linked',
                'entity_type' => $type,
                'entity_id' => (string) $id,
                'user_id' => $userId,
                'admin_id' => $adminId,
                'message' => 'An administrator verified and linked a previously unlinked record.',
                'details' => ['record_type' => $type, 'record_id' => $id],
                'occurred_at' => now(),
            ]);
        });
    }

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
