<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlightStoreRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  
  public function rules(): array {
    // Tiempo mínimo antes del vuelo (en horas)
    // AJUSTA ESTE VALOR para cambiar el tiempo mínimo requerido
    $minHoursBeforeFlight = 1;
    
    return [
      'origin_id'         => ['required','exists:cities,id'],
      'destination_id'    => ['required','different:origin_id','exists:cities,id'],
      'aircraft_id'       => ['nullable','exists:aircraft,id'],
      'scope'             => ['required','in:national,international'],
      'departure_at'      => ['required','date','after:' . now()->addHours($minHoursBeforeFlight)->format('Y-m-d H:i:s')],
      'duration_minutes'  => ['required','integer','min:10','max:2000'],
      'price_per_seat'    => ['required','numeric','min:0'],
      'first_class_price' => ['nullable','numeric','min:0'],
      'capacity_first'    => ['required','integer','min:0','max:300'],
      'capacity_economy'  => ['required','integer','min:1','max:400'],
      'image'             => ['required','image','mimes:jpeg,png,jpg,gif,webp','max:2048'],
    ];
  }
  
  public function messages(): array {
    return [
      // Origin
      'origin_id.required' => 'La ciudad de origen es requerida.',
      'origin_id.exists' => 'La ciudad de origen seleccionada no es válida.',
      
      // Destination
      'destination_id.required' => 'La ciudad de destino es requerida.',
      'destination_id.different' => 'La ciudad de destino debe ser diferente al origen.',
      'destination_id.exists' => 'La ciudad de destino seleccionada no es válida.',
      
      // Aircraft
      'aircraft_id.exists' => 'La aeronave seleccionada no es válida.',
      
      // Scope
      'scope.required' => 'El alcance del vuelo es requerido.',
      'scope.in' => 'El alcance debe ser nacional o internacional.',
      
      // Departure
      'departure_at.required' => 'La fecha y hora de salida es requerida.',
      'departure_at.date' => 'La fecha de salida debe ser una fecha válida.',
      'departure_at.after' => 'La fecha de salida debe ser al menos 1 hora en el futuro.',
      
      // Duration
      'duration_minutes.required' => 'La duración del vuelo es requerida.',
      'duration_minutes.integer' => 'La duración debe ser un número entero.',
      'duration_minutes.min' => 'La duración mínima es de 10 minutos.',
      'duration_minutes.max' => 'La duración máxima es de 2000 minutos.',
      
      // Price
      'price_per_seat.required' => 'El precio por asiento es requerido.',
      'price_per_seat.numeric' => 'El precio debe ser un número válido.',
      'price_per_seat.min' => 'El precio no puede ser negativo.',
      
      // First Class Price
      'first_class_price.numeric' => 'El precio de primera clase debe ser un número válido.',
      'first_class_price.min' => 'El precio de primera clase no puede ser negativo.',
      
      // Capacity
      'capacity_first.required' => 'La capacidad de primera clase es requerida.',
      'capacity_first.integer' => 'La capacidad de primera clase debe ser un número entero.',
      'capacity_first.min' => 'La capacidad de primera clase no puede ser negativa.',
      'capacity_first.max' => 'La capacidad máxima de primera clase es 300 asientos.',
      
      'capacity_economy.required' => 'La capacidad de clase económica es requerida.',
      'capacity_economy.integer' => 'La capacidad de clase económica debe ser un número entero.',
      'capacity_economy.min' => 'La capacidad mínima de clase económica es 1 asiento.',
      'capacity_economy.max' => 'La capacidad máxima de clase económica es 400 asientos.',
      
      // Image
      'image.required' => 'La imagen del vuelo es requerida.',
      'image.image' => 'El archivo debe ser una imagen.',
      'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o webp.',
      'image.max' => 'La imagen no puede ser mayor a 2MB.',
    ];
  }
}
