<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BookingStoreRequest extends FormRequest
{
public function rules(): array
{
    return [
        'flight_id'      => ['required','exists:flights,id'],
        'type'           => ['required','in:reservation,purchase'],
        'travel_type'    => ['nullable','in:one_way,round_trip'],
        'passengers'     => ['required','array','min:1','max:5'],
        'passengers.*.dni'  => ['required','string'],
        'passengers.*.first_name' => ['required','string'],
        'passengers.*.last_name'  => ['required','string'],
        'passengers.*.birth_date' => ['required','date'],
        'passengers.*.gender'     => ['required','in:M,F,X'],
        'passengers.*.class'      => ['required','in:first,economy'],
        'passengers.*.phone'      => ['nullable','string'],
        'passengers.*.email'      => ['required','email'],
        'passengers.*.emergency_contact_name'  => ['nullable','string'],
        'passengers.*.emergency_contact_phone' => ['nullable','string'],
        'payment' => ['nullable','array'],
        'payment.method' => ['nullable','string','in:wallet,saved_card,new_card,card'],
        'payment.amount' => ['nullable','numeric'],
        'payment.currency' => ['nullable','string'],
        'payment.card_id' => ['nullable','integer'],
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

/**
 * Validación adicional después de las reglas básicas
 */
public function withValidator(Validator $validator): void
{
    $validator->after(function ($validator) {
        // Validar DNIs únicos dentro de la reserva
        $passengers = $this->input('passengers', []);
        $dnis = array_map(fn($p) => strtolower(trim($p['dni'] ?? '')), $passengers);
        $dniCounts = array_count_values(array_filter($dnis));
        
        foreach ($dniCounts as $dni => $count) {
            if ($count > 1) {
                $validator->errors()->add(
                    'passengers',
                    "La cédula '{$dni}' está duplicada. Cada pasajero debe tener un documento único."
                );
                break;
            }
        }

        // Validar emails únicos dentro de la reserva
        $emails = array_map(fn($p) => strtolower(trim($p['email'] ?? '')), $passengers);
        $emailCounts = array_count_values(array_filter($emails));
        
        foreach ($emailCounts as $email => $count) {
            if ($count > 1) {
                $validator->errors()->add(
                    'passengers',
                    "El email '{$email}' está duplicado. Cada pasajero debe tener un email único."
                );
                break;
            }
        }
    });
}

public function authorize(): bool
{
    // Solo clientes pueden reservar/comprar
    return $this->user()?->role?->name === 'client';
}

}
