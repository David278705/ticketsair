<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BoardingPassMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public \App\Models\Ticket $ticket,
        public string $pdfPath
    ) {}

    public function build()
    {
        $booking = $this->ticket->booking;
        $flight = $booking->flight;
        
        return $this->subject('✈️ Tu Pasabordo - Vuelo ' . $flight->code)
            ->markdown('mail.boarding-pass', [
                'ticket' => $this->ticket,
                'booking' => $booking,
                'flight' => $flight,
            ])
            ->attach(storage_path('app/public/' . $this->pdfPath), [
                'as' => 'Pasabordo_' . $this->ticket->ticket_code . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
