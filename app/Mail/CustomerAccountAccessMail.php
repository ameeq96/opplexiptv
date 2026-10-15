<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CustomerAccountAccessMail extends Mailable
{
    public function __construct(public string $accessUrl) {}

    public function build()
    {
        return $this->subject('Your secure Opplex account sign-in link')
            ->view('emails.customer-account-access');
    }
}
