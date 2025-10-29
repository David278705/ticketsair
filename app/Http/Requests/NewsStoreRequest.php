<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsStoreRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  
  public function rules(): array {
    return [
      'title'        => ['required','string','max:180'],
      'body'         => ['nullable','string'],
      'flight_id'    => ['nullable','exists:flights,id'],
      'is_promotion' => ['boolean'],
      'image'        => ['required','image','mimes:jpg,jpeg,png,webp','max:2048'], // 2MB
    ];
  }
  
  public function messages(): array {
    return [
      'title.required' => 'El título es requerido.',
      'title.string' => 'El título debe ser texto.',
      'title.max' => 'El título no puede tener más de 180 caracteres.',
      
      'body.string' => 'El cuerpo debe ser texto.',
      
      'flight_id.exists' => 'El vuelo seleccionado no es válido.',
      
      'is_promotion.boolean' => 'El valor de promoción debe ser verdadero o falso.',
      
      'image.required' => 'La imagen es requerida.',
      'image.image' => 'El archivo debe ser una imagen.',
      'image.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
      'image.max' => 'La imagen no puede ser mayor a 2MB.',
    ];
  }
}
