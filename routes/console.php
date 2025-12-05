<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Actualizar estados de vuelos automáticamente cada hora
Schedule::command('flights:update-statuses')->hourly();

// Expirar reservas vencidas cada 30 minutos
Schedule::command('reservations:expire')->everyThirtyMinutes();

// Enviar recordatorios de reservas cada hora
Schedule::command('reservations:send-reminders')->hourly();

// Enviar recomendaciones diarias de vuelos a clientes suscritos (cada 24 horas a las 9 AM)
Schedule::command('flights:send-recommendations')->everyTenMinutes();
