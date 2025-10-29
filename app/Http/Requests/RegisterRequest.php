<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'password'     => [
                'required', 
                'confirmed', 
                'min:8',
                'regex:/[a-z]/',      // Al menos una letra minúscula
                'regex:/[A-Z]/',      // Al menos una letra mayúscula
                'regex:/[0-9]/',      // Al menos un número
            ], 
            'first_name'   => ['required', 'string', 'max:100'], 
            'last_name'    => ['required', 'string', 'max:100'], 
            'dni'          => ['required', 'string', 'max:40', 'unique:users,dni'], 
            'birth_date'   => ['required', 'date', 'before:today', 'after_or_equal:' . now()->subYears(80)->format('Y-m-d'), 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')], 
            'gender'       => ['nullable', 'in:M,F,X'], 
            'username'     => ['nullable', 'alpha_dash', 'min:3', 'max:60', 'unique:users,username'], 
            'billing_address' => ['nullable', 'string', 'max:200'], 
            'news_opt_in'  => ['boolean'],
            // Campos de ubicación
            'country'      => ['nullable', 'string', 'max:3'], 
            'country_name' => ['nullable', 'string', 'max:100'], 
            'state'        => ['nullable', 'string', 'max:100'], 
            'state_name'   => ['nullable', 'string', 'max:100'], 
            'city'         => ['nullable', 'string', 'max:100'], 
            'city_name'    => ['nullable', 'string', 'max:100'], 
        ];
    }

    public function messages(): array
    {
        return [
            // Email
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede tener más de 150 caracteres.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            
            // Password
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula y un número.',
            
            // First Name
            'first_name.required' => 'El nombre es requerido.',
            'first_name.string' => 'El nombre debe ser texto.',
            'first_name.max' => 'El nombre no puede tener más de 100 caracteres.',
            
            // Last Name
            'last_name.required' => 'El apellido es requerido.',
            'last_name.string' => 'El apellido debe ser texto.',
            'last_name.max' => 'El apellido no puede tener más de 100 caracteres.',
            
            // DNI
            'dni.required' => 'El documento de identidad es requerido.',
            'dni.string' => 'El documento de identidad debe ser texto.',
            'dni.max' => 'El documento de identidad no puede tener más de 40 caracteres.',
            'dni.unique' => 'Este documento ya está registrado.',
            
            // Birth Date
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años para registrarte.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años para registrarte.',
            
            // Gender
            'gender.in' => 'El género seleccionado no es válido.',
            
            // Username
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'username.max' => 'El nombre de usuario no puede tener más de 60 caracteres.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            
            // Billing Address
            'billing_address.string' => 'La dirección de facturación debe ser texto.',
            'billing_address.max' => 'La dirección de facturación no puede tener más de 200 caracteres.',
            
            // News Opt In
            'news_opt_in.boolean' => 'El valor de suscripción a noticias debe ser verdadero o falso.',
            
            // Location Fields
            'country.string' => 'El país debe ser texto.',
            'country.max' => 'El código de país no puede tener más de 3 caracteres.',
            'country_name.string' => 'El nombre del país debe ser texto.',
            'country_name.max' => 'El nombre del país no puede tener más de 100 caracteres.',
            'state.string' => 'El estado debe ser texto.',
            'state.max' => 'El código de estado no puede tener más de 100 caracteres.',
            'state_name.string' => 'El nombre del estado debe ser texto.',
            'state_name.max' => 'El nombre del estado no puede tener más de 100 caracteres.',
            'city.string' => 'La ciudad debe ser texto.',
            'city.max' => 'El código de ciudad no puede tener más de 100 caracteres.',
            'city_name.string' => 'El nombre de la ciudad debe ser texto.',
            'city_name.max' => 'El nombre de la ciudad no puede tener más de 100 caracteres.',
        ];
    }
}