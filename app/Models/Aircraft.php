<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'capacity_first',
        'capacity_economy',
        'speed_kmh',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function getTotalCapacityAttribute()
    {
        return $this->capacity_first + $this->capacity_economy;
    }
}
