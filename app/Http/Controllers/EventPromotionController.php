<?php

namespace App\Http\Controllers;

use App\Models\MarketingDelivery;
use App\Services\EventPromotionService;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class EventPromotionController extends Controller
{
    public function __invoke(
        Request $request,
        MarketingDelivery $delivery,
        EventPromotionService $eventPromotions
    ) {
        $locale = in_array($request->query('locale'), config('app.locales', ['en']), true)
            ? $request->query('locale')
            : config('app.fallback_locale', 'en');
        app()->setLocale($locale);

        $delivery->loadMissing('user');
        $campaign = $eventPromotions->activate($delivery);
        abort_unless($campaign, 410);

        $pricingUrl = LaravelLocalization::getLocalizedURL($locale, route('pricing'), [], true);

        return redirect()->to($pricingUrl)->with(
            'event_promotion_activated',
            $campaign['name'] . ': your 10% discount is active for checkout.'
        );
    }
}
