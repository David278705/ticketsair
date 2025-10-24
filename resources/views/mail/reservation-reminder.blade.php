@component('mail::message')
# ⚠️ Tu Reserva Expira Pronto

Hola {{ $booking->passengers->first()->first_name }},

Te recordamos que tu reserva está por expirar y queremos asegurarnos de que no pierdas tu vuelo.

**Código de Reserva:** {{ $booking->reservation_code }}

**Vuelo:** {{ $booking->flight->code }}  
**Ruta:** {{ $booking->flight->origin->name }} → {{ $booking->flight->destination->name }}  
**Fecha de Salida:** {{ $booking->flight->departure_at->format('d/m/Y H:i') }}

**⏰ Tu reserva expira:** {{ $booking->expires_at->format('d/m/Y H:i') }}

---

**¡Solo quedan pocas horas!**

Si deseas asegurar tu viaje, completa el pago antes de la fecha de expiración. De lo contrario, tu reserva será cancelada automáticamente y los asientos serán liberados para otros pasajeros.

@component('mail::button', ['url' => config('app.url') . '/mis-viajes'])
Completar mi Compra
@endcomponent

¿Necesitas ayuda? Contáctanos.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
