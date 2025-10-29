<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlightUpdateRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  
  public function rules(): array {
    // Tiempo mínimo antes del vuelo (en horas)
    // AJUSTA ESTE VALOR para cambiar el tiempo mínimo requerido
    $minHoursBeforeFlight = 1;
    
    $id = $this->route('flight')->id ?? null;
    return [
      'code'              => ['sometimes','string','max:20',"unique:flights,code,{$id}"],
      'origin_id'         => ['sometimes','exists:cities,id'],
      'destination_id'    => ['sometimes','different:origin_id','exists:cities,id'],
      'aircraft_id'       => ['nullable','exists:aircraft,id'],
      'departure_at'      => ['sometimes','date','after:' . now()->addHours($minHoursBeforeFlight)->format('Y-m-d H:i:s')],
      'duration_minutes'  => ['sometimes','integer','min:10','max:2000'],
      'price_per_seat'    => ['sometimes','numeric','min:0'],
      'first_class_price' => ['sometimes','numeric','min:0'],
      'image'             => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:2048'],
      // no actualizamos capacidades si ya hay ventas (se valida en controller)
    ];
  }
  
  public function messages(): array {
    return [
      // Code
      'code.string' => 'El código debe ser texto.',
      'code.max' => 'El código no puede tener más de 20 caracteres.',
      'code.unique' => 'Este código de vuelo ya existe.',
      
      // Origin
      'origin_id.exists' => 'La ciudad de origen seleccionada no es válida.',
      
      // Destination
      'destination_id.different' => 'La ciudad de destino debe ser diferente al origen.',
      'destination_id.exists' => 'La ciudad de destino seleccionada no es válida.',
      
      // Aircraft
      'aircraft_id.exists' => 'La aeronave seleccionada no es válida.',
      
      // Departure
      'departure_at.date' => 'La fecha de salida debe ser una fecha válida.',
      'departure_at.after' => 'La fecha de salida debe ser al menos 1 hora en el futuro.',
      
      // Duration
      'duration_minutes.integer' => 'La duración debe ser un número entero.',
      'duration_minutes.min' => 'La duración mínima es de 10 minutos.',
      'duration_minutes.max' => 'La duración máxima es de 2000 minutos.',
      
      // Price
      'price_per_seat.numeric' => 'El precio debe ser un número válido.',
      'price_per_seat.min' => 'El precio no puede ser negativo.',
      
      // First Class Price
      'first_class_price.numeric' => 'El precio de primera clase debe ser un número válido.',
      'first_class_price.min' => 'El precio de primera clase no puede ser negativo.',
      
      // Image
      'image.image' => 'El archivo debe ser una imagen.',
      'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o webp.',
      'image.max' => 'La imagen no puede ser mayor a 2MB.',
    ];
  }
}
