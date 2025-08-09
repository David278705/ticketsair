<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckinStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ticket_code' => ['nullable','string'], // check-in rápido
            'dni'         => ['nullable','string'],
            // una de las dos debe llegar
        ];
}

}
