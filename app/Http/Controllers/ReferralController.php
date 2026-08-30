<?php

namespace App\Http\Controllers;

use App\Models\Referral;

class ReferralController extends Controller
{
    public function capture(string $code)
    {
        $referral = Referral::query()
            ->where('code', $code)
            ->whereIn('status', ['available', 'captured'])
            ->whereNull('referred_order_id')
            ->firstOrFail();

        session(['referral_code' => $referral->code]);

        return redirect()->route('pricing');
    }
}
