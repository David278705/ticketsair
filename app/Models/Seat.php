<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model {
  protected $fillable=['flight_id','number','class','status'];
  
  public function flight(){ return $this->belongsTo(Flight::class); }
}
