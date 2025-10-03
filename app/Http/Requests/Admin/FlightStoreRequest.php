<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlightStoreRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  public function rules(): array {
    return [
      'origin_id'         => ['required','exists:cities,id'],
      'destination_id'    => ['required','different:origin_id','exists:cities,id'],
      'scope'             => ['required','in:national,international'],
      'departure_at'      => ['required','date','after:now'],
      'aircraft_id'       => ['required','string', function ($attribute, $value, $fail) {
        if (!\App\Models\Flight::isValidAircraftId($value)) {
          $fail('El avión seleccionado no es válido.');
        }
      }],
      'price_per_seat'    => ['required','numeric','min:0'],
    ];
  }
}
