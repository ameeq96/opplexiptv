<?php

namespace App\Services;

use App\Mail\{BuyNowAutoReply, BuyNowEmail, ContactAutoReply, ContactEmail, SubscribeEmail};
use App\Models\User;
use App\Services\Clients\CustomerActivityService;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function contact(array $details): void
    {
        $this->recordInquiry('contact', $details);
        Mail::to('info@opplexiptv.com')->queue(new ContactEmail($details));
        Mail::to($details['email'])->queue(
            (new ContactAutoReply($details))->locale(app()->getLocale())
        );
    }

    public function buyNow(array $details): void
    {
        $this->recordInquiry('buy_now', $details);
        Mail::to('info@opplexiptv.com')->queue(new BuyNowEmail($details));
        Mail::to($details['email'])->queue(
            (new BuyNowAutoReply($details))->locale(app()->getLocale())
        );
    }

    public function subscribe(string $email): void
    {
        Mail::to('info@opplexiptv.com')->queue(new SubscribeEmail(['email' => $email]));
    }

    private function recordInquiry(string $source, array $details): void
    {
        app(CustomerActivityService::class)->record([
            'source' => $source,
            'event_type' => $source.'.inquiry_submitted',
            'contact_name' => mb_substr(trim((string) ($details['username'] ?? '')), 0, 255),
            'contact_email' => mb_substr(mb_strtolower(trim((string) ($details['email'] ?? ''))), 0, 255),
            'contact_phone' => User::normalizePhone($details['phone'] ?? null),
            'message' => $details['message'] ?? null,
            'details' => [
                'contact_verified' => false,
                'package' => isset($details['package']) ? mb_substr((string) $details['package'], 0, 191) : null,
            ],
        ]);
    }
}
