<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendDailyFlightRecommendations;

class SendFlightRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flights:send-recommendations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recomendaciones diarias de vuelos a clientes suscritos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando envío de recomendaciones de vuelos...');
        $this->newLine();

        // Despachar el job
        SendDailyFlightRecommendations::dispatch();

        $this->info('✅ Job despachado exitosamente a la cola.');
        $this->info('💡 Ejecuta "php artisan queue:work" para procesar el job.');
        $this->newLine();
        $this->comment('Para ver los logs en tiempo real: tail -f storage/logs/laravel.log');

        return Command::SUCCESS;
    }
}
