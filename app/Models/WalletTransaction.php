<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'related_id',
        'related_type',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    // Método estático para crear una transacción
    public static function createTransaction(
        $userId,
        $type,
        $amount,
        $description = null,
        $related = null,
        $meta = []
    ) {
        $user = User::findOrFail($userId);
        $balanceBefore = $user->wallet_balance ?? 0;

        // Calcular el nuevo saldo según el tipo de transacción
        $balanceAfter = match ($type) {
            'recharge', 'refund', 'bonus' => $balanceBefore + abs($amount),
            'payment', 'purchase', 'adjustment' => $balanceBefore - abs($amount),
            default => $balanceBefore,
        };

        // Crear la transacción
        $transaction = self::create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => abs($amount),
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'related_id' => $related?->id,
            'related_type' => $related ? get_class($related) : null,
            'meta' => $meta,
        ]);

        // Actualizar el saldo del usuario
        $user->update(['wallet_balance' => $balanceAfter]);

        return $transaction;
    }

    // Scopes
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
