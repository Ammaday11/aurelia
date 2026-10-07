<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the shop's admin inbox whenever a visitor submits the public
 * contact form. The submission is also always saved to the
 * contact_messages table regardless of whether this email succeeds.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function build(): self
    {
        return $this
            ->subject('New contact message: '.($this->contactMessage->subject ?: 'Website enquiry'))
            ->markdown('emails.contact-admin-notification');
    }
}
