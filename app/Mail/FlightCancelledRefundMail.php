<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FlightCancelledRefundMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $flight;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, Flight $flight)
    {
        $this->booking = $booking;
        $this->flight = $flight;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vuelo Cancelado - Reembolso Procesado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.flight-cancelled-refund',
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
