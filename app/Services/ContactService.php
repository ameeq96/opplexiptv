<?php

namespace App\Services;

use App\Mail\{BuyNowAutoReply, BuyNowEmail, ContactAutoReply, ContactEmail, SubscribeEmail};
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function contact(array $details): void
    {
        Mail::to('info@opplexiptv.com')->send(new ContactEmail($details));
        Mail::to($details['email'])->send(new ContactAutoReply($details));
    }

    public function buyNow(array $details): void
    {
        Mail::to('info@opplexiptv.com')->send(new BuyNowEmail($details));
        Mail::to($details['email'])->send(new BuyNowAutoReply($details));
    }

    public function subscribe(string $email): void
    {
        Mail::to('info@opplexiptv.com')->send(new SubscribeEmail(['email' => $email]));
    }
}
