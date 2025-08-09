<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatChange extends Model {
  protected $fillable=['booking_passenger_id','from_seat_id','to_seat_id','changed_at'];
  protected $casts=['changed_at'=>'datetime'];
  public function passenger(){ return $this->belongsTo(BookingPassenger::class,'booking_passenger_id'); }
}