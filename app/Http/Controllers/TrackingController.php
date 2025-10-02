<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacebookCapiService;

class TrackingController extends Controller
{
    public function whatsapp(Request $request, FacebookCapiService $capi)
    {
        $eventId = $request->query('eid');   // browser se bheja gaya eventID
        $href    = $request->query('href');  // jis WA link par click hua
        $phone   = $request->query('phone'); // wa.me / ?phone= se nikala hua number (optional)

        if ($eventId) {
            // Server-side Conversions API: Contact
            $capi->sendEvent('Contact', $eventId, [
                'phone' => $phone, // hashed automatically in service
            ], [
                'content_name'     => 'WhatsApp',
                'content_category' => 'click',
                // 'value' => 0, // agar koi value deni ho to yahan set karo
            ]);
        }

        return response()->noContent(); // 204 — fast, no body
    }
}
