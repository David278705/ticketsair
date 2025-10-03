<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'code','origin_id','destination_id',
        'departure_at','arrival_at','aircraft_id',
        'scope', // 'national'|'international'
        'price_per_seat','status'
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at'   => 'datetime',
        'price_per_seat' => 'decimal:2',
    ];

    protected $appends = [
        'aircraft_data',
        'capacity_economy',
        'capacity_premium', 
        'capacity_total',
        'duration_minutes'
    ];

    // Obtener información del avión desde el JSON
    public function getAircraftDataAttribute()
    {
        if (!$this->aircraft_id) return null;
        
        $aircraftData = json_decode(file_get_contents(storage_path('app/data/aircraft_fleet.json')), true);
        return $aircraftData['aircraft'][$this->aircraft_id] ?? null;
    }

    // Obtener capacidades desde el avión
    public function getCapacityEconomyAttribute()
    {
        return $this->aircraft_data['capacity_economy'] ?? 0;
    }

    public function getCapacityPremiumAttribute()
    {
        return $this->aircraft_data['capacity_premium'] ?? 0;
    }

    public function getCapacityTotalAttribute()
    {
        return $this->aircraft_data['capacity_total'] ?? 0;
    }

    // Calcular duración del vuelo en minutos
    public function getDurationMinutesAttribute()
    {
        if (!$this->aircraft_data) return 0;
        
        $distance = $this->getDistanceKm();
        $speed = $this->aircraft_data['average_speed_kmh'];
        
        if ($distance && $speed) {
            $hours = $distance / $speed;
            return round($hours * 60); // convertir a minutos
        }
        
        return 0;
    }

    // Obtener distancia entre origen y destino
    public function getDistanceKm()
    {
        if (!$this->origin || !$this->destination) return 0;
        
        $locationsData = json_decode(file_get_contents(storage_path('app/data/locations_graph.json')), true);
        $originCode = $this->getLocationCode($this->origin->name);
        $destinationCode = $this->getLocationCode($this->destination->name);
        
        if (!$originCode || !$destinationCode) return 0;
        
        $key1 = $originCode . '-' . $destinationCode;
        $key2 = $destinationCode . '-' . $originCode;
        
        return $locationsData['distances'][$key1] ?? $locationsData['distances'][$key2] ?? 0;
    }

    // Obtener código de ubicación basado en el nombre de la ciudad
    private function getLocationCode($cityName)
    {
        $locationsData = json_decode(file_get_contents(storage_path('app/data/locations_graph.json')), true);
        
        foreach ($locationsData['locations'] as $code => $location) {
            if (strtolower($location['name']) === strtolower($cityName)) {
                return $code;
            }
        }
        
        return null;
    }

    // Calcular hora de llegada automáticamente
    public function calculateArrivalTime()
    {
        if ($this->departure_at && $this->duration_minutes) {
            return $this->departure_at->copy()->addMinutes($this->duration_minutes);
        }
        
        return null;
    }

    // Obtener lista de aviones disponibles
    public static function getAvailableAircraft()
    {
        $aircraftData = json_decode(file_get_contents(storage_path('app/data/aircraft_fleet.json')), true);
        return $aircraftData['aircraft'] ?? [];
    }

    // Validar si un aircraft_id es válido
    public static function isValidAircraftId($aircraftId)
    {
        $availableAircraft = self::getAvailableAircraft();
        return isset($availableAircraft[$aircraftId]);
    }

    // Relaciones típicas
    public function origin(){ return $this->belongsTo(City::class,'origin_id'); }
    public function destination(){ return $this->belongsTo(City::class,'destination_id'); }

    public function seats(){ return $this->hasMany(Seat::class); }
    public function bookings(){ return $this->hasMany(Booking::class); }
    public function tickets(){ return $this->hasManyThrough(Ticket::class, Booking::class); }
}
