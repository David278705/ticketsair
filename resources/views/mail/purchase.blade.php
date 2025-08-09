@component('mail::message')
# ¡Gracias por tu compra!

Código de reserva: **{{ $booking->reservation_code }}**

Vuelo: {{ $booking->flight->code }} ({{ $booking->flight->origin->name }} → {{ $booking->flight->destination->name }})
Fecha: {{ $booking->flight->departure_at->format('Y-m-d H:i') }}

@endcomponent
