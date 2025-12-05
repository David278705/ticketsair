<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Flight;

class DailyFlightRecommendation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $flight;
    public $flightUrl;
    
    // Datos específicos del vuelo (para evitar problemas de serialización)
    public $originCity;
    public $destinationCity;
    public $aircraftModel;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Flight $flight)
    {
        $this->user = $user;
        
        // Cargar las relaciones para acceder a los datos
        $flight->load(['origin', 'destination', 'aircraft']);
        
        // Guardar datos específicos como propiedades simples
        $this->originCity = $flight->origin->name ?? 'Origen';
        $this->destinationCity = $flight->destination->name ?? 'Destino';
        $this->aircraftModel = $flight->aircraft->model ?? null;
        
        // Asignar el vuelo (se serializará solo con su ID)
        $this->flight = $flight;
        
        // Crear URL con el flight_id para abrir directamente el modal de compra
        $this->flightUrl = env('APP_URL') . '/?flight_id=' . $flight->id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vuelo Recomendado - TicketsAir',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-flight-recommendation',
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
