<?php

namespace App\Http\Controllers;

use App\Jobs\SendFacebookCapiEvent;
use App\Models\TrialClick;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function whatsappTrial(Request $request)
    {
        if (!$this->hasMarketingConsent($request)) {
            return response()->json(['ok' => true, 'tracked' => false], 202);
        }

        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'destination' => ['required', 'string', 'max:512'],
            'page' => ['nullable', 'url', 'max:512'],
            'fbp' => ['nullable', 'string', 'max:128'],
            'fbc' => ['nullable', 'string', 'max:256'],
        ]);

        $eventId = $data['event_id'];
        $dest = $data['destination'];
        $page = $data['page'] ?? null;
        $normalize = static function ($value, int $maxLength): ?string {
            if (!is_string($value) && !is_numeric($value)) {
                return null;
            }

            $value = trim((string) $value);
            return $value === '' ? null : mb_substr($value, 0, $maxLength);
        };

        $fbp = $normalize(($data['fbp'] ?? null) ?: $request->cookie('_fbp'), 128);
        $fbc = $normalize(($data['fbc'] ?? null) ?: $request->cookie('_fbc'), 256);

        $utm = [
            'utm_source' => $normalize($request->session()->get('fb.utm_source'), 128),
            'utm_medium' => $normalize($request->session()->get('fb.utm_medium'), 128),
            'utm_campaign' => $normalize($request->session()->get('fb.utm_campaign'), 128),
            'utm_term' => $normalize($request->session()->get('fb.utm_term'), 128),
            'utm_content' => $normalize($request->session()->get('fb.utm_content'), 128),
        ];
        if ($page) {
            $qs = parse_url($page, PHP_URL_QUERY);
            if ($qs) {
                parse_str($qs, $out);
                foreach ($utm as $k => $v) {
                    $pageValue = $normalize($out[$k] ?? null, 128);
                    if ($pageValue !== null) $utm[$k] = $pageValue;
                }
            }
        }

        $click = TrialClick::firstOrCreate(
            ['event_id' => $eventId],
            [
                'destination'  => $dest,
                'page'         => $page,
                'fbp'          => $fbp,
                'fbc'          => $fbc,
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'utm_source'   => $utm['utm_source'],
                'utm_medium'   => $utm['utm_medium'],
                'utm_campaign' => $utm['utm_campaign'],
                'utm_term'     => $utm['utm_term'],
                'utm_content'  => $utm['utm_content'],
                'referrer'     => $request->headers->get('referer'),
            ]
        );

        $payload = [
            'event_time'       => time(),
            'event_source_url' => $page ?: url('/'),
            'user_data' => [
                'fbp' => $fbp ?: null,
                'fbc' => $fbc ?: null,
                'client_ip_address' => $request->ip(),
                'client_user_agent' => $request->userAgent(),
            ],
            'custom_data' => [
                'currency' => config('services.app.default_currency', 'USD'),
                'value'    => 0,
                'content_name'    => 'WhatsApp',
                'contact_channel' => 'whatsapp',
                'destination'     => $dest,
            ],
        ];

        if ($click->wasRecentlyCreated) {
            SendFacebookCapiEvent::dispatchAfterResponse('StartTrial', $payload, $eventId);
        }

        return response()->json([
            'ok' => true,
            'duplicate' => !$click->wasRecentlyCreated,
        ], 202);
    }

    private function hasMarketingConsent(Request $request): bool
    {
        foreach (explode(';', (string) $request->headers->get('cookie')) as $cookie) {
            [$key, $value] = array_pad(explode('=', trim($cookie), 2), 2, null);
            if ($key === 'opplex_consent' && $value !== null) {
                $preference = json_decode(urldecode($value), true);

                return is_array($preference)
                    && ($preference['marketing'] ?? false) === true
                    && ($preference['version'] ?? null) === config('services.marketing.tracking_consent_version');
            }
        }

        return false;
    }
}
