<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model {
  protected $fillable=['title','body','flight_id','is_promotion'];
  public function flight(){ return $this->belongsTo(Flight::class); }
}