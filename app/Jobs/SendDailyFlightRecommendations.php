<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Flight;
use App\Mail\DailyFlightRecommendation;
use Carbon\Carbon;

class SendDailyFlightRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🚀 Iniciando envío de recomendaciones diarias de vuelos');

        // Obtener usuarios con rol de cliente que aceptaron recibir noticias
        $clients = User::whereHas('role', function ($query) {
            $query->where('name', 'client');
        })
        ->where('news_opt_in', true)
        ->whereNotNull('email')
        ->get();

        Log::info("📊 Total de clientes suscritos: {$clients->count()}");

        if ($clients->isEmpty()) {
            Log::info('⚠️ No hay clientes suscritos para enviar recomendaciones');
            return;
        }

        // Obtener vuelos disponibles (próximos 30 días preferentemente)
        $flights = Flight::with(['origin', 'destination', 'aircraft'])
            ->where('status', 'scheduled')
            ->where('departure_at', '>', Carbon::now())
            ->where('departure_at', '<', Carbon::now()->addDays(30))
            ->get();

        // Si no hay vuelos en los próximos 30 días, buscar CUALQUIER vuelo programado futuro
        if ($flights->isEmpty()) {
            Log::info('⚠️ No hay vuelos en los próximos 30 días, buscando cualquier vuelo futuro...');
            $flights = Flight::with(['origin', 'destination', 'aircraft'])
                ->where('status', 'scheduled')
                ->where('departure_at', '>', Carbon::now())
                ->orderBy('departure_at', 'asc')
                ->limit(10) // Limitar a 10 vuelos más cercanos
                ->get();
        }

        // Si aún no hay vuelos futuros, usar los últimos vuelos programados (sin importar fecha)
        if ($flights->isEmpty()) {
            Log::info('⚠️ No hay vuelos futuros, usando los últimos vuelos disponibles...');
            $flights = Flight::with(['origin', 'destination', 'aircraft'])
                ->where('status', 'scheduled')
                ->orderBy('departure_at', 'desc')
                ->limit(10)
                ->get();
        }

        if ($flights->isEmpty()) {
            Log::warning('❌ No hay vuelos disponibles en el sistema para recomendar');
            return;
        }

        // Filtrar vuelos con asientos disponibles
        $availableFlights = $flights->filter(function($flight) {
            // Calcular asientos ocupados a través de las reservas
            $occupiedSeats = $flight->bookings()
                ->where('status', '!=', 'cancelled')
                ->withCount('passengers')
                ->get()
                ->sum('passengers_count');
            
            $totalCapacity = ($flight->capacity_economy ?? 0) + ($flight->capacity_first ?? 0);
            return ($totalCapacity - $occupiedSeats) > 0;
        });

        // Si no hay vuelos con asientos disponibles, usar todos los encontrados
        if ($availableFlights->isEmpty()) {
            Log::info('⚠️ No hay vuelos con asientos calculados como disponibles, usando todos los encontrados');
            $availableFlights = $flights;
        }

        Log::info("✈️ Total de vuelos disponibles: {$availableFlights->count()}");

        if ($availableFlights->isEmpty()) {
            Log::warning('⚠️ No hay vuelos disponibles para recomendar');
            return;
        }

        Log::info("✈️ Total de vuelos disponibles: {$availableFlights->count()}");

        $sentCount = 0;
        $errorCount = 0;

        // Enviar un correo a cada cliente con un vuelo recomendado
        foreach ($clients as $client) {
            try {
                // Obtener IDs de vuelos que el usuario ya ha reservado o comprado
                $bookedFlightIds = \App\Models\Booking::where('user_id', $client->id)
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->pluck('flight_id')
                    ->toArray();

                // Filtrar vuelos que el usuario NO ha reservado
                $recommendableFlights = $availableFlights->filter(function($flight) use ($bookedFlightIds) {
                    return !in_array($flight->id, $bookedFlightIds);
                });

                // Si filtró todos los vuelos, usar todos los disponibles de todas formas
                if ($recommendableFlights->isEmpty()) {
                    Log::info("Usuario {$client->email} ya tiene reservas en todos los vuelos, recomendando de todas formas");
                    $recommendableFlights = $availableFlights;
                }

                // Seleccionar un vuelo aleatorio diferente para cada cliente
                $recommendedFlight = $recommendableFlights->random();

                // Enviar el correo
                Mail::to($client->email)->send(
                    new DailyFlightRecommendation($client, $recommendedFlight)
                );

                $sentCount++;
                Log::info("✅ Correo enviado a: {$client->email} - Vuelo: {$recommendedFlight->code}");

                // Pequeña pausa para evitar saturar el servidor de correo
                usleep(100000); // 0.1 segundos

            } catch (\Exception $e) {
                $errorCount++;
                Log::error("❌ Error al enviar correo a {$client->email}: " . $e->getMessage());
            }
        }

        Log::info("✨ Proceso completado - Enviados: {$sentCount}, Errores: {$errorCount}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ Job de recomendaciones diarias falló: ' . $exception->getMessage());
    }
}

