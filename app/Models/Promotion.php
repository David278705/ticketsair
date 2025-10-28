<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model {
  protected $fillable = [
    'flight_id',
    'title',
    'description',
    'discount_percent',
    'starts_at',
    'ends_at',
    'is_active'
  ];

  protected $casts = [
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
    'is_active' => 'boolean',
  ];

  public function flight() { 
    return $this->belongsTo(Flight::class); 
  }

  // Scope para promociones activas
  public function scopeActive($query)
  {
    $now = Carbon::now();
    return $query->where('is_active', true)
      ->where(function($q) use ($now) {
        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
      })
      ->where(function($q) use ($now) {
        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
      });
  }

  // Verificar si la promoción está activa
  public function isActive()
  {
    if (!$this->is_active) return false;
    
    $now = Carbon::now();
    
    if ($this->starts_at && $this->starts_at->greaterThan($now)) {
      return false;
    }
    
    if ($this->ends_at && $this->ends_at->lessThan($now)) {
      return false;
    }
    
    return true;
  }
}