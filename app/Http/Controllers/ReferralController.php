<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function capture(Request $request, string $code)
    {
        $referral = Referral::query()
            ->where('code', $code)
            ->whereIn('status', ['available', 'captured'])
            ->whereNull('referred_order_id')
            ->firstOrFail();

        session(['referral_code' => $referral->code]);

        if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'Referral applied.', 'redirect_url' => route('pricing')]);
        return redirect()->route('pricing');
    }
}
