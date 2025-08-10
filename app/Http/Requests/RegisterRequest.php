<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'email'        => ['required', 'email', 'max:150', 'unique:users,email'], 
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()], 
            'first_name'   => ['required', 'string', 'max:100'], 
            'last_name'    => ['required', 'string', 'max:100'], 
            'dni'          => ['required', 'string', 'max:40', 'unique:users,dni'], 
            'birth_date'   => ['required', 'date', 'before:today'], 
            'gender'       => ['nullable', 'in:M,F,X'], 
            'username'     => ['nullable', 'alpha_dash', 'min:3', 'max:60', 'unique:users,username'], 
            'billing_address' => ['nullable', 'string', 'max:200'], 
            'news_opt_in'  => ['boolean'], 
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'birth_date.before'  => 'La fecha de nacimiento debe ser anterior a hoy.', 
            'dni.unique'         => 'Este documento ya está registrado.',
            'email.unique'       => 'Este correo electrónico ya está en uso.',
            'username.unique'    => 'Este nombre de usuario ya está en uso.',
        ];
    }
}