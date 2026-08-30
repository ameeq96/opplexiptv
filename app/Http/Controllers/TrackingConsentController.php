<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingConsentController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'analytics' => ['required', 'boolean'],
            'marketing' => ['required', 'boolean'],
        ]);

        if (!$data['analytics']) {
            $orderIds = array_values(array_unique(array_map(
                'intval',
                (array) session('analytics_order_ids', [])
            )));

            Order::query()
                ->whereIn('id', $orderIds)
                ->whereNull('ga_purchase_sent_at')
                ->update([
                    'analytics_consented_at' => null,
                    'ga_client_id' => null,
                    'ga_purchase_processing_at' => null,
                    'ga_purchase_processing_token' => null,
                ]);
        }

        if (!$data['marketing']) {
            foreach (['fb.fbp', 'fb.fbc', 'fb.last_touch_url', 'fb.last_referrer', 'fb.ip', 'fb.ua'] as $key) {
                session()->forget($key);
            }
        }

        return response()->json(['saved' => true]);
    }
}
