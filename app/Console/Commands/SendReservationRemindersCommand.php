<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationReminderMail;

class SendReservationRemindersCommand extends Command
{
    protected $signature = 'reservations:send-reminders';
    protected $description = 'Envía recordatorios por correo a reservas que expiran en 2-3 horas';

    public function handle(): int
    {
        $now = Carbon::now();
        $reminderWindow = $now->copy()->addHours(3); // Reservas que expiran en las próximas 3 horas

        $reservations = Booking::query()
            ->where('type', 'reservation')
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now) // Aún no han expirado
            ->where('expires_at', '<=', $reminderWindow) // Pero expiran en <= 3 horas
            ->whereNull('notified_at') // No han sido notificadas
            ->with(['passengers', 'flight.origin', 'flight.destination'])
            ->get();

        $count = 0;
        foreach ($reservations as $booking) {
            // Enviar correo al primer pasajero o usuario principal
            $passenger = $booking->passengers->first();
            if ($passenger && $passenger->email) {
                Mail::to($passenger->email)->queue(new ReservationReminderMail($booking));
            }

            // También al usuario propietario de la reserva
            if ($booking->user && $booking->user->email) {
                Mail::to($booking->user->email)->queue(new ReservationReminderMail($booking));
            }

            // Marcar como notificada
            $booking->update(['notified_at' => $now]);
            $count++;
            $this->info("Recordatorio enviado para la reserva {$booking->reservation_code}");
        }

        $this->info("Total de recordatorios enviados: {$count}");
        return self::SUCCESS;
    }
}
