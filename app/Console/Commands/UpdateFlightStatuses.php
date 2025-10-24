<?php

namespace App\Console\Commands;

use App\Models\Flight;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateFlightStatuses extends Command
{
    protected $signature = 'flights:update-statuses';
    protected $description = 'Update flight statuses based on departure time and duration';

    public function handle()
    {
        $now = Carbon::now();
        $updated = 0;

        // Obtener todos los vuelos programados
        $flights = Flight::where('status', 'scheduled')->get();

        foreach ($flights as $flight) {
            $departureTime = Carbon::parse($flight->departure_at);
            $arrivalTime = $departureTime->copy()->addMinutes($flight->duration_minutes);

            // Si ya llegó el vuelo (hora de salida + duración < ahora)
            if ($arrivalTime->lessThan($now)) {
                $flight->update(['status' => 'completed']);
                $this->info("✅ Vuelo {$flight->code} marcado como completado");
                $updated++;
            }
        }

        $this->info("Total de vuelos actualizados: {$updated}");
        return Command::SUCCESS;
    }
}
