<?php

namespace App\Http\Controllers;

use App\Models\CheckoutDraft;
use App\Models\MarketingDelivery;
use App\Models\User;
use Illuminate\Http\Request;

class MarketingUnsubscribeController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'user' => ['nullable', 'integer'],
            'draft' => ['nullable', 'uuid'],
            'channel' => ['required', 'in:email,whatsapp,ads,all'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        $locale = in_array($data['locale'] ?? null, config('app.locales', ['en']), true)
            ? $data['locale']
            : config('app.fallback_locale', 'en');
        app()->setLocale($locale);

        if ($request->isMethod('get')) {
            return view('pages.marketing-unsubscribed', [
                'confirmed' => false,
                'signedAction' => $request->fullUrl(),
            ]);
        }

        if (!empty($data['user']) && ($user = User::find($data['user']))) {
            $channels = $data['channel'] === 'all' ? ['email', 'whatsapp', 'ads'] : [$data['channel']];
            foreach ($channels as $channel) {
                $user->setAttribute('marketing_' . $channel . '_opted_out_at', now());
            }
            $user->save();

            MarketingDelivery::query()
                ->where('user_id', $user->id)
                ->whereIn('channel', $channels)
                ->whereNull('sent_at')
                ->update(['failed_at' => now(), 'last_error' => 'Unsubscribed']);
        }

        if (!empty($data['draft']) && ($draft = CheckoutDraft::where('token', $data['draft'])->first())) {
            $channels = $data['channel'] === 'all' ? ['email', 'whatsapp'] : [$data['channel']];
            if (in_array($data['channel'], ['email', 'all'], true)) {
                $draft->email_consented_at = null;
            }
            if (in_array($data['channel'], ['whatsapp', 'all'], true)) {
                $draft->whatsapp_consented_at = null;
            }
            if (in_array($data['channel'], ['ads', 'all'], true)) {
                $draft->ads_consented_at = null;
            }
            $draft->save();

            MarketingDelivery::query()
                ->where('checkout_draft_id', $draft->id)
                ->whereIn('channel', $channels)
                ->whereNull('sent_at')
                ->update(['failed_at' => now(), 'last_error' => 'Unsubscribed']);
        }

        return view('pages.marketing-unsubscribed', ['confirmed' => true]);
    }
}
