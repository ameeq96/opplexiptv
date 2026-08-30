<?php

namespace App\Http\Controllers;

use App\Models\CheckoutDraft;
use Illuminate\Http\Request;

class CheckoutDraftController extends Controller
{
    public function store(Request $request)
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
        $existing = CheckoutDraft::where('token', $data['token'])->first();

        if ($existing?->completed_at) {
            return response()->json(['saved' => true, 'completed' => true]);
        }

        if (!$emailConsent && !$whatsappConsent && !$adsConsent) {
            $existing?->delete();
            return response()->json(['saved' => false]);
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

        CheckoutDraft::updateOrCreate(
            ['token' => $data['token']],
            [
                'package_id' => $data['package_id'] ?? null,
                'device_id' => $data['device_id'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'connection_name' => $data['connection_name'] ?? null,
                'connection_price' => $data['connection_price'] ?? null,
                'name' => $name !== '' ? $name : null,
                'email' => ($emailConsent || $adsConsent) ? ($data['email'] ?? null) : null,
                'phone' => $whatsappConsent ? ($data['phone'] ?? null) : null,
                'locale' => app()->getLocale(),
                'email_consented_at' => $emailConsent ? ($existing?->email_consented_at ?: $now) : null,
                'whatsapp_consented_at' => $whatsappConsent ? ($existing?->whatsapp_consented_at ?: $now) : null,
                'ads_consented_at' => $adsConsent ? ($existing?->ads_consented_at ?: $now) : null,
                'consent_version' => $existing?->consent_version ?: config('services.marketing.consent_version'),
                'consent_ip_hash' => $existing?->consent_ip_hash ?: $ipHash,
                'referral_code' => session('referral_code'),
                'last_activity_at' => $now,
                'retention_expires_at' => $now->copy()->addDays(
                    max(1, (int) config('services.marketing.draft_retention_days', 30))
                ),
            ]
        );

        return response()->json(['saved' => true]);
    }
}
