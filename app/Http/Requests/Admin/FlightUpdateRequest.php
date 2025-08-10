<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlightUpdateRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  public function rules(): array {
    $id = $this->route('flight')->id ?? null;
    return [
      'code'              => ['sometimes','string','max:20',"unique:flights,code,{$id}"],
      'origin_id'         => ['sometimes','exists:cities,id'],
      'destination_id'    => ['sometimes','different:origin_id','exists:cities,id'],
      'departure_at'      => ['sometimes','date','after:now'],
      'duration_minutes'  => ['sometimes','integer','min:10','max:2000'],
      'price_per_seat'    => ['sometimes','numeric','min:0'],
      // no actualizamos capacidades si ya hay ventas (se valida en controller)
    ];
  }
}
