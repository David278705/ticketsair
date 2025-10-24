@component('mail::message')
# ✅ ¡Compra Confirmada!

Hola {{ $booking->passengers->first()->first_name }},

Tu compra ha sido procesada exitosamente. A continuación los detalles de tu vuelo:

---

## Información de Pago

**Estado:** ✅ Pagado  
**Monto Total:** ${{ number_format($booking->total_amount, 0, ',', '.') }} COP  
**Código de Reserva:** {{ $booking->reservation_code }}

---

## Detalles del Vuelo

**Vuelo:** {{ $booking->flight->code }}  
**Ruta:** {{ $booking->flight->origin->name }} ({{ $booking->flight->origin->code }}) → {{ $booking->flight->destination->name }} ({{ $booking->flight->destination->code }})

**Fecha de Salida:** {{ $booking->flight->departure_at->format('d/m/Y H:i') }}  
**Fecha de Llegada:** {{ $booking->flight->arrival_at ? $booking->flight->arrival_at->format('d/m/Y H:i') : 'Por confirmar' }}  
**Duración:** {{ $booking->flight->duration_minutes }} minutos  
**Clase:** {{ ucfirst($booking->class) }}

---

## Pasajeros

@foreach($booking->passengers as $p)
**{{ $loop->iteration }}.** {{ $p->first_name }} {{ $p->last_name }}  
- DNI: {{ $p->dni }}  
- Asiento: {{ $p->seat ? $p->seat->number : 'Por asignar' }}  
@if($p->luggage_count > 0)
- Equipaje: {{ $p->luggage_count }} pieza(s)
@endif

@endforeach

---

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
