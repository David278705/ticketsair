<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Booking $booking)
    {
    }

    public function build()
    {
        return $this->subject('Tu Reserva Expira Pronto - TicketsAir')
            ->markdown('mail.reservation-reminder', ['booking' => $this->booking]);
    }
}
