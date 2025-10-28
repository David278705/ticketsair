@component('mail::message')
# ¡Reserva Confirmada!

Hola {{ $passenger->first_name }},

Tu reserva ha sido confirmada exitosamente. A continuación los detalles:

---

## Tu Información Personal

**Pasajero:** {{ $passenger->first_name }} {{ $passenger->last_name }}  
**DNI:** {{ $passenger->dni }}  
**Asiento:** {{ $passenger->seat ? $passenger->seat->number : 'Por asignar' }}  
**Clase:** {{ ucfirst($booking->class) }}

---

**Código de Reserva:** {{ $booking->reservation_code }}

**Vuelo:** {{ $booking->flight->code }}  
**Ruta:** {{ $booking->flight->origin->name }} → {{ $booking->flight->destination->name }}  
**Fecha de Salida:** {{ $booking->flight->departure_at->format('d/m/Y H:i') }}  
**Duración:** {{ $booking->flight->duration_minutes }} minutos

---

@if($booking->passengers->count() > 1)
## Otros Pasajeros en esta Reserva

@foreach($booking->passengers as $p)
@if($p->id !== $passenger->id)
**•** {{ $p->first_name }} {{ $p->last_name }} ({{ $p->dni }}) - Asiento: {{ $p->seat ? $p->seat->number : 'Por asignar' }}  
@endif
@endforeach

---
@endif

⚠️ **IMPORTANTE:** Esta reserva expira el **{{ $booking->expires_at ? $booking->expires_at->format('d/m/Y H:i') : 'Próximamente' }}** (24 horas desde ahora).

Para confirmar tu viaje, debes completar el pago antes de la fecha de expiración. De lo contrario, tu reserva será cancelada automáticamente y los asientos serán liberados.

@component('mail::button', ['url' => config('app.url') . '/mis-viajes'])
Ver Mis Viajes
@endcomponent

Gracias por confiar en TicketsAir.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
