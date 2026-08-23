<?php

namespace App\Http\Controllers;

use App\Jobs\SendFacebookCapiEvent;
use App\Models\TrialClick;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function whatsappTrial(Request $request)
    {
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
        $fbp = ($data['fbp'] ?? null) ?: $request->cookie('_fbp');
        $fbc = ($data['fbc'] ?? null) ?: $request->cookie('_fbc');

        $utm = ['utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null, 'utm_term' => null, 'utm_content' => null];
        if ($page) {
            $qs = parse_url($page, PHP_URL_QUERY);
            if ($qs) {
                parse_str($qs, $out);
                foreach ($utm as $k => $v) {
                    if (!empty($out[$k])) $utm[$k] = $out[$k];
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
}
