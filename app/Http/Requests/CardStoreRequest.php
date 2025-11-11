<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'expiry_month' => ['required', 'string', 'size:2', 'regex:/^(0[1-9]|1[0-2])$/'],
            'expiry_year' => ['required', 'integer', 'min:' . date('Y'), 'max:' . (date('Y') + 20)],
            'cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'card_type' => ['nullable', 'in:credit,debit'],
            'brand' => ['nullable', 'in:visa,mastercard,amex,discover,unknown'],
            'is_default' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'card_holder_name.required' => 'El nombre del titular es requerido.',
            'card_number.required' => 'El número de tarjeta es requerido.',
            'card_number.min' => 'El número de tarjeta debe tener al menos 13 dígitos.',
            'card_number.max' => 'El número de tarjeta no debe tener más de 19 dígitos.',
            'expiry_month.required' => 'El mes de expiración es requerido.',
            'expiry_month.regex' => 'El mes de expiración debe estar entre 01 y 12.',
            'expiry_year.required' => 'El año de expiración es requerido.',
            'expiry_year.min' => 'El año de expiración no puede ser menor al año actual.',
            'cvv.required' => 'El CVV es requerido.',
            'cvv.regex' => 'El CVV debe tener 3 o 4 dígitos.',
            'card_type.in' => 'El tipo de tarjeta debe ser crédito o débito.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que la tarjeta no esté expirada
            if ($this->expiry_year && $this->expiry_month) {
                $currentYear = (int) date('Y');
                $currentMonth = (int) date('m');
                $expYear = (int) $this->expiry_year;
                $expMonth = (int) $this->expiry_month;

                if ($expYear < $currentYear || 
                    ($expYear == $currentYear && $expMonth < $currentMonth)) {
                    $validator->errors()->add('expiry_month', 'La tarjeta está expirada.');
                }
            }
        });
    }
}
