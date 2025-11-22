<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderMails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $emailSubject;
    public $headline;
    public $customMessage;

    public function __construct(Order $order, string $subject, string $headline, string $message)
    {
        $this->order = $order;
        $this->emailSubject = $subject;
        $this->headline = $headline;
        $this->customMessage = $message;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject . ' - Order #' . $this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.dynamic-update',
            with: [
                'order' => $this->order,
                'headline' => $this->headline,
                'customMessage' => $this->customMessage,
            ],
        );
    }
}