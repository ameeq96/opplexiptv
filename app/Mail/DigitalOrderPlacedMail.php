<?php

namespace App\Mail;

use App\Models\Digital\DigitalOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DigitalOrderPlacedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public DigitalOrder $order,
        public bool $isAdmin = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isAdmin
                ? 'New digital order ' . $this->order->order_number
                : 'Your digital order ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.digital-commerce.order-placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
