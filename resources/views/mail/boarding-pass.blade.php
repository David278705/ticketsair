@component('mail::message')
# ✅ Check-in Exitoso

Hola **{{ $ticket->passenger->first_name }}**,

Tu check-in ha sido completado exitosamente. Adjunto encontrarás tu pasabordo electrónico.

---

**Vuelo:** {{ $flight->code }}  
**Ruta:** {{ $flight->origin->name }} → {{ $flight->destination->name }}  
**Fecha:** {{ $flight->departure_at->format('d/m/Y H:i') }}  
**Duración:** @php
    $duration = $flight->duration_minutes;
    $hours = floor($duration / 60);
    $minutes = $duration % 60;
    echo $hours > 0 ? ($hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '')) : $minutes . 'min';
@endphp

**Pasajero:** {{ $ticket->passenger->first_name }} {{ $ticket->passenger->last_name }}  
**Documento:** {{ $ticket->passenger->dni }}  
**Asiento:** {{ $ticket->passenger->seat ? $ticket->passenger->seat->number : 'Por asignar' }}  
**Clase:** {{ ucfirst($ticket->passenger->class) }}

---

**Código de Tiquete:** {{ $ticket->ticket_code }}  
**Código de Reserva:** {{ $booking->reservation_code }}

---

### Instrucciones Importantes:

1. **Imprime o descarga** tu pasabordo adjunto
2. **Llega al aeropuerto** con 2 horas de antelación
3. **Presenta** tu documento de identidad y pasabordo
4. **Dirígete** a la puerta de embarque indicada

@component('mail::button', ['url' => config('app.url') . '/mis-viajes'])
Ver Mis Viajes
@endcomponent

¡Buen viaje!

Saludos,<br>
{{ config('app.name') }}
@endcomponent
