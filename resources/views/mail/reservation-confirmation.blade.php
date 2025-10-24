@component('mail::message')
# ¡Reserva Confirmada!

Hola {{ $booking->passengers->first()->first_name }},

Tu reserva ha sido confirmada exitosamente. A continuación los detalles:

**Código de Reserva:** {{ $booking->reservation_code }}

**Vuelo:** {{ $booking->flight->code }}  
**Ruta:** {{ $booking->flight->origin->name }} → {{ $booking->flight->destination->name }}  
**Fecha de Salida:** {{ $booking->flight->departure_at->format('d/m/Y H:i') }}  
**Duración:** {{ $booking->flight->duration_minutes }} minutos

**Pasajeros:**
@foreach($booking->passengers as $p)
- {{ $p->first_name }} {{ $p->last_name }} ({{ $p->dni }}) - Asiento: {{ $p->seat ? $p->seat->number : 'Por asignar' }}
@endforeach

**Total:** ${{ number_format($booking->total_amount, 0, ',', '.') }}

---

 **IMPORTANTE:** Esta reserva expira el **{{ $booking->expires_at ? $booking->expires_at->format('d/m/Y H:i') : 'Próximamente' }}** (24 horas desde ahora).

Para confirmar tu viaje, debes completar el pago antes de la fecha de expiración. De lo contrario, tu reserva será cancelada automáticamente y los asientos serán liberados.

@component('mail::button', ['url' => config('app.url') . '/mis-viajes'])
Ver Mis Viajes
@endcomponent

Gracias por confiar en TicketsAir.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
