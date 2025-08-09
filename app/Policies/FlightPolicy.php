<?php

namespace App\Policies;

use App\Models\Flight;
use App\Models\User;

class FlightPolicy
{
    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Flight $flight): bool { return true; }

    public function create(User $user): bool {
        return in_array($user->role?->name, ['admin', 'root'], true);
    }
    public function update(User $user, Flight $flight): bool {
        // Admin/root y que el vuelo NO tenga ventas ni haya sido realizado
        return in_array($user->role?->name, ['admin', 'root'], true)
            && $flight->status === 'scheduled'
            && !$flight->bookings()->where('type', 'purchase')->exists();
        // Edición solo si no hay ventas/ni realizado. :contentReference[oaicite:17]{index=17}
    }
    public function delete(User $user, Flight $flight): bool {
        return in_array($user->role?->name, ['admin', 'root'], true);
    }
}
