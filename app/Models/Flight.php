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
                // Incluir vuelos futuros Y vuelos de hoy que aún no han salido
                $q->whereDate('departure_at', '>', $now->toDateString())
                  ->orWhere(function($q2) use ($now) {
                      $q2->whereDate('departure_at', '=', $now->toDateString())
                         ->whereTime('departure_at', '>', $now->toTimeString());
                  });
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
    
    /**
     * Calcular la hora de llegada en la zona horaria del destino
     */
    public function getArrivalTimeInDestinationTimezone()
    {
        $departure = Carbon::parse($this->departure_at);
        $arrival = $departure->copy()->addMinutes($this->duration_minutes);
        
        // Si hay destino con timezone, convertir a esa zona horaria
        if ($this->destination) {
            $destinationTimezone = $this->destination->getTimezone();
            return $arrival->setTimezone($destinationTimezone);
        }
        
        // Fallback: devolver en la zona horaria por defecto
        return $arrival;
    }
    
    /**
     * Obtener la hora de llegada formateada con zona horaria
     */
    public function getFormattedArrivalTime()
    {
        // Asegurarse de que destination esté cargado
        if (!$this->relationLoaded('destination')) {
            $this->load('destination');
        }
        
        $arrival = $this->getArrivalTimeInDestinationTimezone();
        $timezone = $this->destination ? $this->destination->getTimezone() : 'America/Bogota';
        
        // Obtener el nombre corto de la zona horaria (ej: COT, CET, PST)
        $timezoneAbbr = $arrival->format('T');
        
        return [
            'datetime' => $arrival->format('Y-m-d H:i:s'),
            'formatted' => $arrival->format('d/m/Y H:i'),
            'timezone' => $timezone,
            'timezone_abbr' => $timezoneAbbr,
            'is_different_day' => $arrival->format('Y-m-d') !== Carbon::parse($this->departure_at)->format('Y-m-d')
        ];
    }

    // Accesor útil
    public function getCapacityTotalAttribute(){
        return (int)$this->capacity_first + (int)$this->capacity_economy;
    }

    /**
     * Obtener la capacidad de primera clase según el scope del vuelo
     * Nacional: 25 asientos
     * Internacional: 50 asientos
     */
    public static function getDefaultFirstClassCapacity($scope) {
        return $scope === 'international' ? 50 : 25;
    }

    /**
     * Obtener la capacidad de clase económica según el scope del vuelo
     * Nacional: 125 asientos (26-150)
     * Internacional: 200 asientos (51-250)
     */
    public static function getDefaultEconomyCapacity($scope) {
        return $scope === 'international' ? 200 : 125;
    }

    /**
     * Obtener la velocidad del avión según el scope del vuelo
     * Nacional: 850 km/h
     * Internacional: 900 km/h
     */
    public static function getDefaultSpeed($scope) {
        return $scope === 'international' ? 900 : 850;
    }

    // Relaciones típicas
    public function origin(){ return $this->belongsTo(City::class,'origin_id'); }
    public function destination(){ return $this->belongsTo(City::class,'destination_id'); }
    public function aircraft(){ return $this->belongsTo(Aircraft::class); }

    public function seats(){ return $this->hasMany(Seat::class); }
    public function bookings(){ return $this->hasMany(Booking::class); }
    public function tickets(){ return $this->hasManyThrough(Ticket::class, Booking::class); }
    public function promotions(){ return $this->hasMany(Promotion::class); }
    
    // Obtener la promoción activa actual (ya iniciada y no expirada)
    public function activePromotion() {
        return $this->hasOne(Promotion::class)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest();
    }
    
    // Obtener cualquier promoción válida (incluyendo futuras)
    public function anyValidPromotion() {
        return $this->hasOne(Promotion::class)
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->latest();
    }
    
    // Verificar si el vuelo tiene una promoción activa
    public function hasActivePromotion() {
        return $this->activePromotion()->exists();
    }
    
    // Verificar si el vuelo tiene alguna promoción válida (incluyendo futuras)
    public function hasAnyValidPromotion() {
        return $this->anyValidPromotion()->exists();
    }
    
    // Obtener el precio con descuento si aplica
    public function getDiscountedPrice($basePrice) {
        $promo = $this->activePromotion;
        if ($promo) {
            return $basePrice * (1 - ($promo->discount_percent / 100));
        }
        return $basePrice;
    }
}
