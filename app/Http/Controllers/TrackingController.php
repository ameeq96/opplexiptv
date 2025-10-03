<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacebookCapiService;

class TrackingController extends Controller
{
    public function whatsappTrial(Request $request, FacebookCapiService $capi)
    {
        $eventId = $request->input('event_id');   // client-generated UUID
        $dest    = $request->input('destination'); // wa.me link
        $page    = $request->input('page');        // current page URL

        $payload = [
            'event_time'       => time(),
            'event_source_url' => $page ?: session('fb.last_touch_url') ?: url('/'),
            'user_data' => [
                'fbp' => request()->cookie('_fbp') ? [request()->cookie('_fbp')] : null,
                'fbc' => request()->cookie('_fbc') ? [request()->cookie('_fbc')] : null,
                'client_ip_address' => session('fb.ip') ?: $request->ip(),
                'client_user_agent' => session('fb.ua') ?: $request->userAgent(),
            ],
            'custom_data' => [
                'currency' => config('services.app.default_currency','USD'),
                'value'    => 0,
                'content_name'    => 'WhatsApp',
                'contact_channel' => 'whatsapp',
                'destination'     => $dest,
            ],
        ];

        $resp = $capi->send('StartTrial', $payload, $eventId);

        return response()->json(['ok' => true, 'resp' => $resp]);
    }
}
