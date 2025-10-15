<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {
  protected $fillable=['name','scope','distances'];
  
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
}
