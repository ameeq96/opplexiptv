<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $details;
    public bool $isAdmin;

    /**
     * Create a new message instance.
     */
    public function __construct(array $details, bool $isAdmin = false)
    {
        $this->details = $details;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $orderId = $this->details['order_id'] ?? null;
        $subject = $this->isAdmin
            ? 'New checkout order' . ($orderId ? ' #' . $orderId : '')
            : 'Your order has been received' . ($orderId ? ' (#' . $orderId . ')' : '');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout_order',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
