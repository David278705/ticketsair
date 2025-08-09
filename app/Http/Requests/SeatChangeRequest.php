<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeatChangeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'booking_passenger_id' => ['required','exists:booking_passengers,id'],
            'to_seat_id'           => ['required','exists:seats,id'],
        ];
    }

}
