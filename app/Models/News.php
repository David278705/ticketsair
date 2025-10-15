<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'flight_id',
        'is_promotion'
    ];

    protected $casts = [
        'is_promotion' => 'boolean'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        // Create news automatically when a new flight is added
        Flight::created(function ($flight) {
            self::create([
                'title' => 'Nuevo Vuelo Disponible',
                'body' => "Se ha agregado un nuevo vuelo {$flight->code} desde {$flight->origin->name} hasta {$flight->destination->name}.",
                'flight_id' => $flight->id,
                'is_promotion' => false
            ]);
        });
    }
}