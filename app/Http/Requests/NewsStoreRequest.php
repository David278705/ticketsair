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
}
