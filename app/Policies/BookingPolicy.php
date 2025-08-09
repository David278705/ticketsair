<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function create(User $user): bool {
        // Solo clientes pueden comprar/reservar
        return $user->role?->name === 'client';
    }

    public function view(User $user, Booking $booking): bool {
        return $user->role?->name === 'admin'
            || $user->role?->name === 'root'
            || $booking->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool {
        if ($user->role?->name === 'admin' || $user->role?->name === 'root') return true;
        return $booking->user_id === $user->id;
    }
}
