<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model {
  protected $fillable = [
    'user_id','flight_id','promotion_id','type','status','reservation_code',
    'travel_type','expires_at','notified_at','seats_count','total_amount',
    'original_amount','discount_amount'
  ];
  protected $casts=[
    'expires_at'=>'datetime',
    'notified_at'=>'datetime'
  ];
  public function user(){ return $this->belongsTo(User::class); }
  public function flight(){ return $this->belongsTo(Flight::class); }
  public function promotion(){ return $this->belongsTo(Promotion::class); }
  public function passengers(){ return $this->hasMany(BookingPassenger::class); }
  public function tickets(){ return $this->hasMany(Ticket::class); }
  public function payments(){ return $this->morphMany(Payment::class,'payable'); }
}
