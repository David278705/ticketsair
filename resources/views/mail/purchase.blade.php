@component('mail::message')
# ✅ ¡Compra Confirmada!

Hola {{ $passenger->first_name }},

Tu compra ha sido procesada exitosamente. A continuación los detalles de tu vuelo:

---

## Tu Información Personal

**Pasajero:** {{ $passenger->first_name }} {{ $passenger->last_name }}  
**DNI:** {{ $passenger->dni }}  
**Asiento:** {{ $passenger->seat ? $passenger->seat->number : 'Por asignar' }}  
**Clase:** {{ ucfirst($booking->class) }}

---

## Información de Pago

**Estado:** ✅ Pagado  
**Código de Reserva:** {{ $booking->reservation_code }}

---

## Detalles del Vuelo

**Vuelo:** {{ $booking->flight->code }}  
**Ruta:** {{ $booking->flight->origin->name }} ({{ $booking->flight->origin->code }}) → {{ $booking->flight->destination->name }} ({{ $booking->flight->destination->code }})

**Fecha de Salida:** {{ $booking->flight->departure_at->format('d/m/Y H:i') }}  
**Fecha de Llegada:** {{ $booking->flight->arrival_at ? $booking->flight->arrival_at->format('d/m/Y H:i') : 'Por confirmar' }}  
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

## Próximos Pasos

1. **Check-in:** Podrás hacer check-in {{ $booking->flight->is_international ? '48 horas' : '24 horas' }} antes de tu vuelo
2. **Pasabordo:** Recibirás tu pasabordo por email después de completar el check-in
3. **Llegada:** Te recomendamos llegar al aeropuerto {{ $booking->flight->is_international ? '3 horas' : '2 horas' }} antes de tu vuelo

@component('mail::button', ['url' => config('app.url') . '/mis-viajes'])
Ver Mis Viajes
@endcomponent

Gracias por volar con TicketsAir. ¡Buen viaje! ✈️

Saludos,<br>
{{ config('app.name') }}
@endcomponent
