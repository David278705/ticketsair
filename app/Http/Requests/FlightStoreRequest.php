<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlightStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'origin_id'       => ['required','exists:cities,id'],
            'destination_id'  => ['required','different:origin_id','exists:cities,id'],
            'departure_at'    => ['required','date','after:now'],
            'duration_minutes'=> ['required','integer','min:1'],
            'price_per_seat'  => ['required','numeric','min:0'],
            'is_international'=> ['required','boolean'],
        ];
    }
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Flight::class) ?? false;
    }

}
