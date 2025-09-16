<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCredentialsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo el usuario root puede actualizar credenciales de otros usuarios
        return $this->user() && $this->user()->role->name === 'root';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'email' => "sometimes|email|unique:users,email,{$userId}",
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'dni' => "sometimes|string|unique:users,dni,{$userId}",
            'birth_date' => 'sometimes|date|before:today',
            'gender' => 'sometimes|nullable|in:M,F,X',
            'username' => "sometimes|nullable|string|unique:users,username,{$userId}",
            'billing_address' => 'sometimes|nullable|string|max:500'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado en el sistema.',
            'first_name.string' => 'El nombre debe ser una cadena de texto.',
            'first_name.max' => 'El nombre no puede exceder 255 caracteres.',
            'last_name.string' => 'El apellido debe ser una cadena de texto.',
            'last_name.max' => 'El apellido no puede exceder 255 caracteres.',
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.in' => 'El género debe ser M, F o X.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'billing_address.max' => 'La dirección de facturación no puede exceder 500 caracteres.'
        ];
    }
}
