<?php

namespace App\Console\Commands;

use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteFlightsCommand extends Command
{
    protected $signature = 'flights:complete';
    protected $description = 'Marca vuelos como completed cuando su hora de llegada ya pasó';

    public function handle(): int
    {
        $now = Carbon::now();

        $updated = Flight::query()
            ->where('status', 'scheduled')
            ->where('arrival_at', '<=', $now)
            ->update(['status' => 'completed']);

        $this->info("Vuelos completados: {$updated}");

        return self::SUCCESS;
    }
}
