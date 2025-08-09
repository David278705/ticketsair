<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model {
  protected $fillable=['flight_id','title','description','discount_percent','max_seats','active'];
  public function flight(){ return $this->belongsTo(Flight::class); }
}