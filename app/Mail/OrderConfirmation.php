<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the customer immediately after a successful checkout, and
 * resendable by an admin from the order detail screen if needed.
 */
class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build(): self
    {
        return $this
            ->subject("Order Confirmation — {$this->order->order_number}")
            ->markdown('emails.order-confirmation', [
                'order' => $this->order->loadMissing('items'),
            ]);
    }
}
