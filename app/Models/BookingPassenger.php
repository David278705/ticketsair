<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPassenger extends Model {
  protected $fillable = [
    'booking_id','user_id','dni','first_name','last_name','birth_date','gender',
    'phone','email','emergency_contact_name','emergency_contact_phone',
    'seat_id','class','seat_changed_once'
  ];
  protected $casts=['birth_date'=>'date','seat_changed_once'=>'bool'];
  public function booking(){ return $this->belongsTo(Booking::class); }
  public function seat(){ return $this->belongsTo(Seat::class); }
  public function ticket(){ return $this->hasOne(Ticket::class,'booking_passenger_id'); }
}