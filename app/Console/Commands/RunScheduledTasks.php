<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class RunScheduledTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:run-all {--force : Forzar ejecución de todas las tareas sin verificar horario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta todas las tareas programadas manualmente (ideal para Railway, Render, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando ejecución de tareas programadas...');
        $this->info('⏰ Fecha y hora actual: ' . now()->format('Y-m-d H:i:s'));
        $this->newLine();

        $force = $this->option('force');
        $now = Carbon::now();
        $successCount = 0;
        $errorCount = 0;

        // ====================================================================
        // TAREA 1: Actualizar estados de vuelos (cada hora)
        // ====================================================================
        if ($force || $this->shouldRunHourly('flights:update-statuses', $now)) {
            $this->info('📋 [1/4] Actualizando estados de vuelos...');
            try {
                Artisan::call('flights:update-statuses');
                $output = Artisan::output();
                $this->line($output ?: '   ✅ Estados de vuelos actualizados');
                $successCount++;
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
                $errorCount++;
            }
            $this->newLine();
        } else {
            $this->comment('⏭️  [1/4] Saltando actualización de vuelos (no es hora)');
            $this->newLine();
        }

        // ====================================================================
        // TAREA 2: Expirar reservas vencidas (cada 30 minutos)
        // ====================================================================
        if ($force || $this->shouldRunEveryThirtyMinutes('reservations:expire', $now)) {
            $this->info('📋 [2/4] Expirando reservas vencidas...');
            try {
                Artisan::call('reservations:expire');
                $output = Artisan::output();
                $this->line($output ?: '   ✅ Reservas expiradas procesadas');
                $successCount++;
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
                $errorCount++;
            }
            $this->newLine();
        } else {
            $this->comment('⏭️  [2/4] Saltando expiración de reservas (no es hora)');
            $this->newLine();
        }

        // ====================================================================
        // TAREA 3: Enviar recordatorios de reservas (cada hora)
        // ====================================================================
        if ($force || $this->shouldRunHourly('reservations:send-reminders', $now)) {
            $this->info('📋 [3/4] Enviando recordatorios de reservas...');
            try {
                Artisan::call('reservations:send-reminders');
                $output = Artisan::output();
                $this->line($output ?: '   ✅ Recordatorios enviados');
                $successCount++;
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
                $errorCount++;
            }
            $this->newLine();
        } else {
            $this->comment('⏭️  [3/4] Saltando recordatorios (no es hora)');
            $this->newLine();
        }

        // ====================================================================
        // TAREA 4: Enviar recomendaciones de vuelos (cada 10 minutos)
        // ====================================================================
        if ($force || $this->shouldRunEveryTenMinutes('flights:send-recommendations', $now)) {
            $this->info('📋 [4/4] Enviando recomendaciones de vuelos...');
            try {
                Artisan::call('flights:send-recommendations');
                $output = Artisan::output();
                $this->line($output ?: '   ✅ Recomendaciones enviadas');
                $successCount++;
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
                $errorCount++;
            }
            $this->newLine();
        } else {
            $this->comment('⏭️  [4/4] Saltando recomendaciones (no es hora)');
            $this->newLine();
        }

        // ====================================================================
        // RESUMEN
        // ====================================================================
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✨ Ejecución completada');
        $this->info("   ✅ Exitosas: {$successCount}");
        if ($errorCount > 0) {
            $this->error("   ❌ Errores: {$errorCount}");
        }
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Verificar si debe ejecutarse una tarea horaria
     */
    protected function shouldRunHourly(string $taskName, Carbon $now): bool
    {
        $lastRun = cache()->get("last_run_{$taskName}");
        
        if (!$lastRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
            return true;
        }

        $lastRunTime = Carbon::parse($lastRun);
        $shouldRun = $now->diffInMinutes($lastRunTime) >= 60;

        if ($shouldRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
        }

        return $shouldRun;
    }

    /**
     * Verificar si debe ejecutarse una tarea cada 30 minutos
     */
    protected function shouldRunEveryThirtyMinutes(string $taskName, Carbon $now): bool
    {
        $lastRun = cache()->get("last_run_{$taskName}");
        
        if (!$lastRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
            return true;
        }

        $lastRunTime = Carbon::parse($lastRun);
        $shouldRun = $now->diffInMinutes($lastRunTime) >= 30;

        if ($shouldRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
        }

        return $shouldRun;
    }

    /**
     * Verificar si debe ejecutarse una tarea cada 10 minutos
     */
    protected function shouldRunEveryTenMinutes(string $taskName, Carbon $now): bool
    {
        $lastRun = cache()->get("last_run_{$taskName}");
        
        if (!$lastRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
            return true;
        }

        $lastRunTime = Carbon::parse($lastRun);
        $shouldRun = $now->diffInMinutes($lastRunTime) >= 10;

        if ($shouldRun) {
            cache()->put("last_run_{$taskName}", $now->toDateTimeString(), now()->addHours(2));
        }

        return $shouldRun;
    }
}
