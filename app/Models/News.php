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
        'promotion_id',
        'is_promotion'
    ];

    protected $casts = [
        'is_promotion' => 'boolean'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}