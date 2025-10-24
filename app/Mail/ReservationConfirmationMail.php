<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Booking $booking)
    {
    }

    public function build()
    {
        return $this->subject('Confirmación de Reserva - TicketsAir')
            ->markdown('mail.reservation-confirmation', ['booking' => $this->booking]);
    }
}
