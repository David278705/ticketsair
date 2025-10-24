<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseMail extends Mailable {
  public function __construct(public \App\Models\Booking $booking) {}
  public function build(){
    return $this->subject('✅ Confirmación de Compra - Vuelo ' . $this->booking->flight->code)
      ->markdown('mail.purchase', ['booking'=>$this->booking]);
  }
}

