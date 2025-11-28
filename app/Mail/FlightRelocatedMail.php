<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FlightRelocatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $originalFlight;
    public $newFlight;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, Flight $originalFlight, Flight $newFlight)
    {
        $this->booking = $booking;
        $this->originalFlight = $originalFlight;
        $this->newFlight = $newFlight;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cambio de Vuelo - ' . $this->originalFlight->code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.flight-relocated',
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
