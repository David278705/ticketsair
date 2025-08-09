<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireReservationsCommand extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Libera reservas vencidas (24h) y asientos asociados';

    public function handle(): int
    {
        $now = Carbon::now();

        $expired = Booking::query()
            ->where('type', 'reservation')
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->with(['passengers.seat'])
            ->chunkById(200, function ($bookings) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking) {
                        // Liberar asientos
                        foreach ($booking->passengers as $bp) {
                            if ($bp->seat) {
                                $bp->seat->update(['status' => 'available']);
                                $bp->update(['seat_id' => null]);
                            }
                        }
                        // Marcar reserva expirada
                        $booking->update(['status' => 'expired']);
                    });
                    $this->info("Reserva {$booking->id} expirada y asientos liberados.");
                }
            });

        return self::SUCCESS;
    }
}
