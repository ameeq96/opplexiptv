<?php

namespace App\Services;

use App\Mail\{BuyNowAutoReply, BuyNowEmail, ContactAutoReply, ContactEmail, SubscribeEmail};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Services\FacebookCapiService;
use Illuminate\Support\Arr;

class ContactService
{
    public function contact(array $details): void
    {
        Mail::to('info@opplexiptv.com')->send(new ContactEmail($details));
        Mail::to($details['email'])->send(new ContactAutoReply($details));

        $capi = app(FacebookCapiService::class);
        $eventId = $capi->generateEventId();
        $capi->sendEvent('Lead', $eventId, [
            'email' => $details['email'] ?? null,
            'phone' => $details['phone'] ?? null,
        ]);

        Session::flash('fb_event', [
            'name' => 'Lead',
            'id'   => $eventId,
            'value' => null,
        ]);
    }

    public function buyNow(array $details): void
    {
        Mail::to('info@opplexiptv.com')->send(new BuyNowEmail($details));
        Mail::to($details['email'])->send(new BuyNowAutoReply($details));

        $capi = app(FacebookCapiService::class);
        $eventId = $capi->generateEventId();
        $value = null;
        $capi->sendEvent('Lead', $eventId, [
            'email' => $details['email'] ?? null,
            'phone' => $details['phone'] ?? null,
        ], ['value' => $value]);

        Session::flash('fb_event', [
            'name' => 'Lead',
            'id'   => $eventId,
            'value' => $value,
        ]);
    }

    public function subscribe(string $email): void
    {
        Mail::to('info@opplexiptv.com')->send(new SubscribeEmail(['email' => $email]));

        $capi = app(FacebookCapiService::class);
        $eventId = $capi->generateEventId();
        $capi->sendEvent('CompleteRegistration', $eventId, ['email' => $email]);

        Session::flash('fb_event', [
            'name' => 'CompleteRegistration',
            'id'   => $eventId,
            'value' => null,
        ]);
    }
}
