<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
public function rules(): array
{
    return [
        'flight_id'      => ['required','exists:flights,id'],
        'type'           => ['required','in:reservation,purchase'],
        'travel_type'    => ['nullable','in:one_way,round_trip'],
        'class'          => ['required','in:first,economy'],
        'passengers'     => ['required','array','min:1','max:5'], // máx 5 por vuelo/cliente
        'passengers.*.dni'  => ['required','string'],
        'passengers.*.first_name' => ['required','string'],
        'passengers.*.last_name'  => ['required','string'],
        'passengers.*.birth_date' => ['required','date'],
        'passengers.*.gender'     => ['required','in:M,F,X'],
        'passengers.*.phone'      => ['nullable','string'],
        'passengers.*.email'      => ['nullable','email'],
        'passengers.*.emergency_contact_name'  => ['nullable','string'],
        'passengers.*.emergency_contact_phone' => ['nullable','string'],
        // Datos de pago (opcionales, solo para compras)
        'payment' => ['nullable','array'],
        'payment.card_number' => ['nullable','string'],
        'payment.card_holder' => ['nullable','string'],
        'payment.expiry_date' => ['nullable','string'],
        'payment.cvv' => ['nullable','string'],
        'payment.card_type' => ['nullable','string'],
        'payment.save_card' => ['nullable','boolean'],
        'payment.last_four' => ['nullable','string'],
        'payment.transaction_id' => ['nullable','string'],
    ];
}
public function authorize(): bool
{
    // Solo clientes pueden reservar/comprar
    return $this->user()?->role?->name === 'client';
}

}
