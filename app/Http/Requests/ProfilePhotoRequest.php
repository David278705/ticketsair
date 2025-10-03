<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfilePhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden subir fotos
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048', // 2MB máximo
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ]
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'profile_photo.required' => 'La foto de perfil es requerida.',
            'profile_photo.image' => 'El archivo debe ser una imagen válida.',
            'profile_photo.mimes' => 'La imagen debe ser de tipo: JPEG, JPG, PNG, GIF o WebP.',
            'profile_photo.max' => 'La imagen no puede pesar más de 2MB.',
            'profile_photo.dimensions' => 'La imagen debe tener al menos 100x100 píxeles y máximo 2000x2000 píxeles.',
        ];
    }
}
