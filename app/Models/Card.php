<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model {
  protected $fillable=[
    'user_id', 'brand', 'card_type', 'holder_name', 'last4', 
    'exp_month', 'exp_year', 'token', 'is_default'
  ];

  protected $casts = [
    'is_default' => 'boolean',
  ];

  protected $appends = ['masked_number', 'is_expired'];

  // Relaciones
  public function user() { 
    return $this->belongsTo(User::class); 
  }

  public function payments() {
    return $this->hasMany(Payment::class);
  }

  // Accesor: Número enmascarado de la tarjeta
  public function getMaskedNumberAttribute() {
    return '**** **** **** ' . $this->last4;
  }

  // Accesor: Verificar si la tarjeta está expirada
  public function getIsExpiredAttribute() {
    $currentYear = (int) date('Y');
    $currentMonth = (int) date('m');
    $expYear = (int) $this->exp_year;
    $expMonth = (int) $this->exp_month;

    if ($expYear < $currentYear) {
      return true;
    }
    if ($expYear == $currentYear && $expMonth < $currentMonth) {
      return true;
    }
    return false;
  }

  // Método: Marcar esta tarjeta como predeterminada
  public function makeDefault() {
    // Desmarcar todas las tarjetas del usuario
    self::where('user_id', $this->user_id)
        ->where('id', '!=', $this->id)
        ->update(['is_default' => false]);

    // Marcar esta tarjeta como predeterminada
    $this->update(['is_default' => true]);

    return $this;
  }

  // Scope: Solo tarjetas no expiradas
  public function scopeActive($query) {
    $currentYear = date('Y');
    $currentMonth = date('m');

    return $query->where(function($q) use ($currentYear, $currentMonth) {
      $q->where('exp_year', '>', $currentYear)
        ->orWhere(function($q2) use ($currentYear, $currentMonth) {
          $q2->where('exp_year', '=', $currentYear)
             ->where('exp_month', '>=', $currentMonth);
        });
    });
  }

  // Scope: Tarjeta predeterminada del usuario
  public function scopeDefault($query, $userId) {
    return $query->where('user_id', $userId)
                 ->where('is_default', true);
  }
}