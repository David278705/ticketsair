<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Flight extends Model
{
    protected $fillable = [
        'code','origin_id','destination_id','aircraft_id',
        'departure_at','duration_minutes','arrival_at',
        'scope', // 'national'|'international'
        'capacity_first','capacity_economy',
        'price_per_seat','first_class_price','status','image_path'
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at'   => 'datetime',
        'price_per_seat' => 'decimal:2',
        'first_class_price' => 'decimal:2',
    ];

    // Scopes para filtrar vuelos
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Scope para vuelos disponibles (programados y no finalizados)
    public function scopeAvailable($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'scheduled')
            ->where(function($q) use ($now) {
                $q->where('departure_at', '>', $now)
                  ->orWhereRaw('DATE_ADD(departure_at, INTERVAL duration_minutes MINUTE) > ?', [$now]);
            });
    }

    // Scope para vuelos futuros (aún no han salido)
    public function scopeFuture($query)
    {
        return $query->where('departure_at', '>', Carbon::now());
    }

    // Scope para vuelos pasados
    public function scopePast($query)
    {
        return $query->whereRaw('DATE_ADD(departure_at, INTERVAL duration_minutes MINUTE) < ?', [Carbon::now()]);
    }

    // Verificar si el vuelo ya pasó
    public function isPast()
    {
        $arrivalTime = Carbon::parse($this->departure_at)->addMinutes($this->duration_minutes);
        return $arrivalTime->lessThan(Carbon::now());
    }

    // Verificar si el vuelo está en curso
    public function isInProgress()
    {
        $now = Carbon::now();
        $departureTime = Carbon::parse($this->departure_at);
        $arrivalTime = $departureTime->copy()->addMinutes($this->duration_minutes);
        
        return $now->greaterThanOrEqualTo($departureTime) && $now->lessThan($arrivalTime);
    }

    // Verificar si el vuelo está disponible para reserva
    public function isAvailableForBooking()
    {
        return $this->status === 'scheduled' && !$this->isPast();
    }

    // Accesor útil
    public function getCapacityTotalAttribute(){
        return (int)$this->capacity_first + (int)$this->capacity_economy;
    }

    // Relaciones típicas
    public function origin(){ return $this->belongsTo(City::class,'origin_id'); }
    public function destination(){ return $this->belongsTo(City::class,'destination_id'); }
    public function aircraft(){ return $this->belongsTo(Aircraft::class); }

    public function seats(){ return $this->hasMany(Seat::class); }
    public function bookings(){ return $this->hasMany(Booking::class); }
    public function tickets(){ return $this->hasManyThrough(Ticket::class, Booking::class); }
}
