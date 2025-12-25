<?php

namespace App\Services;

use App\Mail\{BuyNowAutoReply, BuyNowEmail, ContactAutoReply, ContactEmail, SubscribeEmail};
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function contact(array $details): void
    {
        Mail::to('info@opplexiptv.com')->queue(new ContactEmail($details));
        Mail::to($details['email'])->queue(new ContactAutoReply($details));
    }

    public function buyNow(array $details): void
    {
        Mail::to('info@opplexiptv.com')->queue(new BuyNowEmail($details));
        Mail::to($details['email'])->queue(new BuyNowAutoReply($details));
    }

    public function subscribe(string $email): void
    {
        Mail::to('info@opplexiptv.com')->queue(new SubscribeEmail(['email' => $email]));
    }
}
