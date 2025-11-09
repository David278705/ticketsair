<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {
  protected $fillable=['name','scope','distances','timezone','country'];
  
  protected $casts = [
    'distances' => 'array'
  ];
  
  /**
   * Obtiene la distancia en km hacia otra ciudad
   */
  public function getDistanceTo($cityId)
  {
    return $this->distances[$cityId] ?? null;
  }
  
  /**
   * Obtiene la zona horaria de la ciudad
   */
  public function getTimezone()
  {
    return $this->timezone ?? 'America/Bogota';
  }
}

