<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model {
  protected $fillable = [
    'code','origin_id','destination_id','departure_at','duration_minutes',
    'arrival_at','is_international','capacity','price_per_seat','status'
  ];
  protected $casts = ['departure_at'=>'datetime','arrival_at'=>'datetime','is_international'=>'bool'];
  public function origin(){ return $this->belongsTo(City::class,'origin_id'); }
  public function destination(){ return $this->belongsTo(City::class,'destination_id'); }
  public function seats(){ return $this->hasMany(Seat::class); }
  public function promotions(){ return $this->hasMany(Promotion::class); }
  public function media(){ return $this->morphMany(Media::class,'mediable'); }
  public function news(){ return $this->hasMany(News::class); }
  public function bookings(){ return $this->hasMany(Booking::class); }
}