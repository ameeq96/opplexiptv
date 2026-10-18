<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class LandingCheckoutResumeMail extends Mailable
{
    public function __construct(public string $resumeUrl, public array $ui) {}

    public function build()
    {
        return $this->subject($this->ui['resumeEmailSubject'])->view('emails.landing-checkout-resume');
    }
}
