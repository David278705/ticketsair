<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionStoreRequest extends FormRequest {
  public function authorize(): bool { return $this->user()->role?->name !== 'client'; }
  public function rules(): array {
    return [
      'title'            => ['required','string','max:150'],
      'description'      => ['nullable','string'],
      'discount_percent' => ['required','integer','min:1','max:90'],
      'starts_at'        => ['nullable','date'],
      'ends_at'          => ['nullable','date','after:starts_at'],
      'is_active'        => ['boolean'],
    ];
  }
}
