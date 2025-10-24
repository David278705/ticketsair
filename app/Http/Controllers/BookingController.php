<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingStoreRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;




// app/Http/Controllers/BookingController.php
class BookingController extends Controller
{

    public function myBookings(Request $r) {
  return \App\Models\Booking::with(['flight.origin','flight.destination','tickets','passengers.seat'])
    ->where('user_id',$r->user()->id)
    ->orderByDesc('created_at')
    ->paginate(10);
}


  public function cancel(\Illuminate\Http\Request $request, \App\Models\Booking $booking)
{
    // $this->authorize('cancel', $booking);

    $flight = $booking->flight;
    $now = now();

    if ($booking->type === 'purchase') {
        // Se puede cancelar hasta 1 hora antes del vuelo -> refund
        if ($flight->departure_at->diffInMinutes($now, false) >= -60) {
            return response()->json(['error'=>'too_late_to_cancel_purchase'], 422);
        }
        // Refund simulado
        $payment = $booking->payments()->latest()->first();
        if ($payment && $payment->status === 'paid') {
            $payment->update(['status'=>'refunded']);
            $booking->update(['status'=>'cancelled']);
        }
    } else {
        // Reserva: cancelar mientras esté vigente (antes de expirar)
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            return response()->json(['error'=>'reservation_already_expired'], 422);
        }
        $booking->update(['status'=>'cancelled']);
    }

    // Liberar asientos
    foreach ($booking->passengers as $bp) {
        if ($bp->seat) {
            $bp->seat->update(['status'=>'available']);
            $bp->update(['seat_id'=>null]);
        }
    }

    return response()->json(['ok'=>true]);
}


  public function store(BookingStoreRequest $request)
{
    $data = $request->validated();
    $flight = \App\Models\Flight::available()->findOrFail($data['flight_id']); // Solo vuelos disponibles

    if ($flight->status !== 'scheduled' || $flight->departure_at->isPast()) {
        return response()->json(['error'=>'flight_unavailable'],422);
    }

    // Pasajeros: menores deben ir con adulto
    $passengers = collect($data['passengers']);
    $hasAdult = $passengers->contains(fn($p)=>\Carbon\Carbon::parse($p['birth_date'])->age >= 18);
    $hasMinor = $passengers->contains(fn($p)=>\Carbon\Carbon::parse($p['birth_date'])->age < 18);
    if ($hasMinor && !$hasAdult) {
        return response()->json(['error'=>'minors_cannot_travel_alone'],422);
    }

    // Máx. 5 por vuelo/cliente (Request ya limita el array a 5) + check duplicidad viajero en mismo vuelo
    $dniList = $passengers->pluck('dni');
    $dup = \App\Models\BookingPassenger::whereHas('booking', function($q) use ($request, $flight){
        $q->where('user_id', $request->user()->id)->where('flight_id', $flight->id);
    })->whereIn('dni', $dniList)->exists();
    if ($dup) return response()->json(['error'=>'passenger_already_in_flight'],422);

    // Tomar asientos disponibles por clase
    $needed = $passengers->count();
    $seats = \App\Models\Seat::where('flight_id',$flight->id)
        ->where('class',$data['class'])
        ->where('status','available')
        ->inRandomOrder()
        ->lockForUpdate()
        ->limit($needed)->get();

    if ($seats->count() < $needed) {
        return response()->json(['error'=>'not_enough_seats'],422);
    }

    $booking = DB::transaction(function () use ($request, $flight, $data, $seats, $passengers) {
        $expires = $data['type'] === 'reservation' ? now()->addDay() : null; // 24h
        $total = $passengers->count() * $flight->price_per_seat;

        // GENERAR CÓDIGO ÚNICO PARA AMBOS TIPOS (purchase Y reservation)
        $reservationCode = Str::upper(Str::random(8));

        $booking = \App\Models\Booking::create([
            'user_id'=>$request->user()->id,
            'flight_id'=>$flight->id,
            'type'   =>$data['type'],
            'status' =>$data['type']==='purchase' ? 'confirmed' : 'pending',
            'reservation_code'=> $reservationCode, // Código para ambos tipos
            'travel_type'=>$data['travel_type'] ?? 'one_way',
            'expires_at'=>$expires,
            'seats_count'=>$passengers->count(),
            'total_amount'=>$total,
        ]);

        foreach ($passengers->values() as $i => $p) {
            $seat = $seats[$i];
            $seat->update(['status' => $data['type']==='purchase' ? 'assigned' : 'reserved']);

            $bp = \App\Models\BookingPassenger::create([
                'booking_id'=>$booking->id,
                'dni'=>$p['dni'],
                'first_name'=>$p['first_name'],
                'last_name'=>$p['last_name'],
                'birth_date'=>$p['birth_date'],
                'gender'=>$p['gender'],
                'phone'=>$p['phone'] ?? null,
                'email'=>$p['email'] ?? null,
                'emergency_contact_name'=>$p['emergency_contact_name'] ?? null,
                'emergency_contact_phone'=>$p['emergency_contact_phone'] ?? null,
                'seat_id'=>$seat->id,
                'class'=>$data['class'],
            ]);

            // Tickets SOLO en compra (el PDF usa código de reserva tras compra para check-in rápido)
            if ($data['type']==='purchase') {
                \App\Models\Ticket::create([
                    'booking_id'=>$booking->id,
                    'booking_passenger_id'=>$bp->id,
                    'ticket_code'=>Str::upper(Str::random(10)),
                    'status'=>'issued',
                ]);
            }
        }

        // Simular pago inmediato en compra
        if ($data['type']==='purchase') {
            $paymentMeta = ['method'=>'wallet/demo'];
            
            // Si hay datos de pago (tarjeta), guardarlos
            if (isset($data['payment'])) {
                $paymentData = $data['payment'];
                $paymentMeta = [
                    'method' => 'credit_card',
                    'card_type' => $paymentData['card_type'] ?? 'unknown',
                    'last_four' => $paymentData['last_four'] ?? 'XXXX',
                    'transaction_id' => $paymentData['transaction_id'] ?? Str::random(16),
                    'card_holder' => $paymentData['card_holder'] ?? 'N/A',
                ];
                
                // Si el usuario quiere guardar la tarjeta
                if (isset($paymentData['save_card']) && $paymentData['save_card']) {
                    $expiryParts = explode('/', $paymentData['expiry_date'] ?? '');
                    \App\Models\Card::create([
                        'user_id' => $request->user()->id,
                        'brand' => $paymentData['card_type'] ?? 'unknown',
                        'holder_name' => $paymentData['card_holder'] ?? '',
                        'last4' => $paymentData['last_four'] ?? 'XXXX',
                        'exp_month' => $expiryParts[0] ?? '01',
                        'exp_year' => isset($expiryParts[1]) ? '20' . $expiryParts[1] : date('Y'),
                        'token' => null,
                    ]);
                }
            }
            
            \App\Models\Payment::create([
                'payable_type'=>\App\Models\Booking::class,
                'payable_id'=>$booking->id,
                'user_id'=>$request->user()->id,
                'status'=>'paid',
                'amount'=>$total,
                'meta'=>$paymentMeta,
            ]);

            // Cargar relaciones una sola vez
            $bookingWithData = $booking->load('flight.origin', 'flight.destination', 'passengers.seat');
            
            // Enviar correo de compra a cada pasajero con email
            foreach ($bookingWithData->passengers as $p) {
                if ($p->email) {
                    try {
                        Mail::to($p->email)->send(new \App\Mail\PurchaseMail($bookingWithData));
                        Log::info("Email de compra enviado a: {$p->email}");
                    } catch (\Exception $e) {
                        Log::error("Error enviando email de compra a {$p->email}: " . $e->getMessage());
                    }
                }
            }
        } else {
            // RESERVA: Enviar correo de confirmación de reserva
            $bookingWithData = $booking->load('flight.origin', 'flight.destination', 'passengers.seat');
            
            foreach ($bookingWithData->passengers as $p) {
                if ($p->email) {
                    try {
                        Mail::to($p->email)->send(new \App\Mail\ReservationConfirmationMail($bookingWithData));
                        Log::info("Email de reserva enviado a: {$p->email}");
                    } catch (\Exception $e) {
                        Log::error("Error enviando email de reserva a {$p->email}: " . $e->getMessage());
                    }
                }
            }
            
            // También al usuario propietario
            if ($request->user()->email) {
                try {
                    Mail::to($request->user()->email)->send(new \App\Mail\ReservationConfirmationMail($bookingWithData));
                    Log::info("Email de reserva enviado al usuario: {$request->user()->email}");
                } catch (\Exception $e) {
                    Log::error("Error enviando email al usuario: " . $e->getMessage());
                }
            }
        }

        return $booking->load('passengers.seat','tickets');
    });

    return response()->json($booking, 201);
}


  public function convertToPurchase(Request $request, \App\Models\Booking $booking)
{
    // Validar que el usuario sea el dueño de la reserva
    if ($booking->user_id !== $request->user()->id) {
        return response()->json(['error'=>'unauthorized'], 403);
    }

    // Solo reservas pendientes pueden convertirse
    if ($booking->type !== 'reservation' || $booking->status !== 'pending') {
        return response()->json(['error'=>'cannot_convert_booking'], 422);
    }

    // Verificar que no haya expirado
    if ($booking->expires_at && $booking->expires_at->isPast()) {
        return response()->json(['error'=>'reservation_expired'], 422);
    }

    // Verificar que el vuelo siga disponible
    if ($booking->flight->status !== 'scheduled' || $booking->flight->isPast()) {
        return response()->json(['error'=>'flight_unavailable'], 422);
    }

    return DB::transaction(function() use ($booking, $request) {
        // Cambiar tipo y estado
        $booking->update([
            'type' => 'purchase',
            'status' => 'confirmed',
            'expires_at' => null, // Ya no expira
        ]);

        // Cambiar estado de asientos de 'reserved' a 'assigned'
        foreach ($booking->passengers as $passenger) {
            if ($passenger->seat && $passenger->seat->status === 'reserved') {
                $passenger->seat->update(['status' => 'assigned']);
            }

            // Crear ticket para cada pasajero
            if (!$passenger->ticket) {
                \App\Models\Ticket::create([
                    'booking_id' => $booking->id,
                    'booking_passenger_id' => $passenger->id,
                    'ticket_code' => Str::upper(Str::random(10)),
                    'status' => 'issued',
                ]);
            }
        }

        // Crear pago
        \App\Models\Payment::create([
            'payable_type' => \App\Models\Booking::class,
            'payable_id' => $booking->id,
            'user_id' => $request->user()->id,
            'status' => 'paid',
            'amount' => $booking->total_amount,
            'meta' => ['method' => 'wallet/demo', 'converted_from_reservation' => true],
        ]);

        // Enviar correo de confirmación de compra
        foreach ($booking->passengers as $p) {
            if ($p->email) {
                Mail::to($p->email)->queue(new \App\Mail\PurchaseMail($booking->load('flight.origin','flight.destination','passengers')));
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Reserva convertida a compra exitosamente',
            'booking' => $booking->fresh()->load('passengers.seat', 'tickets', 'payments')
        ]);
    });
}

}
