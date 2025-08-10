<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlightStoreRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  public function rules(): array {
    return [
      'code'              => ['required','string','max:20','unique:flights,code'],
      'origin_id'         => ['required','exists:cities,id'],
      'destination_id'    => ['required','different:origin_id','exists:cities,id'],
      'scope'             => ['required','in:national,international'],
      'departure_at'      => ['required','date','after:now'],
      'duration_minutes'  => ['required','integer','min:10','max:2000'],
      'price_per_seat'    => ['required','numeric','min:0'],
      'capacity_first'    => ['required','integer','min:0','max:300'],
      'capacity_economy'  => ['required','integer','min:1','max:400'],
    ];
  }
}
