<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacebookCapiService;

class TrackingController extends Controller
{
    public function whatsapp(Request $request, FacebookCapiService $capi)
    {
        $eventId = $request->query('eid');
        $href    = $request->query('href');
        $phone   = $request->query('phone');

        if ($eventId) {
            $capi->sendEvent('Contact', $eventId, ['phone' => $phone], [
                'content_name'     => 'WhatsApp',
                'content_category' => 'click',
            ]);
        }
        return response()->noContent();
    }
}
