<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo el usuario root puede crear administradores
        return $this->user() && $this->user()->role->name === 'root';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'full_name' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado en el sistema.',
            'full_name.required' => 'El nombre completo es obligatorio.',
            'full_name.string' => 'El nombre completo debe ser una cadena de texto.',
            'full_name.max' => 'El nombre completo no puede exceder 255 caracteres.',
        ];
    }
}
