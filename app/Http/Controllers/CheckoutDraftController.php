<?php

namespace App\Http\Controllers;

use App\Models\CheckoutDraft;
use App\Services\Clients\CustomerIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutDraftController extends Controller
{
    public function store(Request $request, CustomerIdentityService $identity)
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'device_id' => ['nullable', 'integer', 'exists:devices,id'],
            'vendor' => ['nullable', 'string', 'max:30'],
            'connection_name' => ['nullable', 'string', 'max:100'],
            'connection_price' => ['nullable', 'numeric', 'min:0'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'marketing_email' => ['nullable', 'boolean'],
            'marketing_whatsapp' => ['nullable', 'boolean'],
            'marketing_ads' => ['nullable', 'boolean'],
        ]);

        $emailConsent = $request->boolean('marketing_email');
        $whatsappConsent = $request->boolean('marketing_whatsapp');
        $adsConsent = $request->boolean('marketing_ads');
        if (!$emailConsent && !$whatsappConsent && !$adsConsent) {
            return DB::transaction(function () use ($data) {
                $draft = CheckoutDraft::query()
                    ->where('token', $data['token'])
                    ->lockForUpdate()
                    ->first();

                if (!$draft) {
                    return response()->json(['saved' => false]);
                }

                if ($draft->completed_at) {
                    return response()->json(['saved' => true, 'completed' => true]);
                }

                $draft->forceFill([
                    'user_id' => null,
                    'name' => null,
                    'email' => null,
                    'phone' => null,
                    'email_consented_at' => null,
                    'whatsapp_consented_at' => null,
                    'ads_consented_at' => null,
                    'consent_version' => null,
                    'consent_ip_hash' => null,
                    'last_activity_at' => now(),
                ])->save();

                return response()->json(['saved' => false]);
            }, 3);
        }

        if (($emailConsent || $adsConsent) && empty($data['email'])) {
            return response()->json(['saved' => false]);
        }
        if ($whatsappConsent && empty($data['phone'])) {
            return response()->json(['saved' => false]);
        }

        $now = now();
        $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $ipHash = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
        $email = ($emailConsent || $adsConsent) ? ($data['email'] ?? null) : null;
        $phone = $whatsappConsent ? ($data['phone'] ?? null) : null;
        $user = $identity->resolve($email, $phone);

        $draft = CheckoutDraft::firstOrCreate(
            ['token' => $data['token']],
            [
                'locale' => app()->getLocale(),
                'last_activity_at' => $now,
                'retention_expires_at' => $now->copy()->addDays(
                    max(1, (int) config('services.marketing.draft_retention_days', 30))
                ),
            ]
        );

        return DB::transaction(function () use (
            $adsConsent,
            $data,
            $draft,
            $email,
            $emailConsent,
            $ipHash,
            $name,
            $now,
            $phone,
            $user,
            $whatsappConsent
        ) {
            $lockedDraft = CheckoutDraft::query()
                ->whereKey($draft->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDraft->completed_at) {
                return response()->json(['saved' => true, 'completed' => true]);
            }

            $lockedDraft->forceFill([
                'user_id' => $user?->id,
                'package_id' => $data['package_id'] ?? null,
                'device_id' => $data['device_id'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'connection_name' => $data['connection_name'] ?? null,
                'connection_price' => $data['connection_price'] ?? null,
                'name' => $name !== '' ? $name : null,
                'email' => $email,
                'phone' => $phone,
                'locale' => app()->getLocale(),
                'email_consented_at' => $emailConsent ? ($lockedDraft->email_consented_at ?: $now) : null,
                'whatsapp_consented_at' => $whatsappConsent
                    ? ($lockedDraft->whatsapp_consented_at ?: $now)
                    : null,
                'ads_consented_at' => $adsConsent ? ($lockedDraft->ads_consented_at ?: $now) : null,
                'consent_version' => $lockedDraft->consent_version
                    ?: config('services.marketing.consent_version'),
                'consent_ip_hash' => $lockedDraft->consent_ip_hash ?: $ipHash,
                'referral_code' => session('referral_code'),
                'last_activity_at' => $now,
                'retention_expires_at' => $now->copy()->addDays(
                    max(1, (int) config('services.marketing.draft_retention_days', 30))
                ),
            ])->save();

            return response()->json(['saved' => true]);
        }, 3);
    }
}
