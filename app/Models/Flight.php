<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'code','origin_id','destination_id',
        'departure_at','duration_minutes','arrival_at',
        'scope', // 'national'|'international'
        'capacity_first','capacity_economy',
        'price_per_seat','status'
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at'   => 'datetime',
        'price_per_seat' => 'decimal:2',
    ];

    // Accesor útil
    public function getCapacityTotalAttribute(){
        return (int)$this->capacity_first + (int)$this->capacity_economy;
    }

    // Relaciones típicas
    public function origin(){ return $this->belongsTo(City::class,'origin_id'); }
    public function destination(){ return $this->belongsTo(City::class,'destination_id'); }

    public function seats(){ return $this->hasMany(Seat::class); }
    public function bookings(){ return $this->hasMany(Booking::class); }
    public function tickets(){ return $this->hasManyThrough(Ticket::class, Booking::class); }
}
