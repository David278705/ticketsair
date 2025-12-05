<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingStoreRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;




// app/Http/Controllers/BookingController.php
class BookingController extends Controller
{

    public function myBookings(Request $r) {
  return \App\Models\Booking::with(['flight.origin','flight.destination','originalFlight.origin','originalFlight.destination','tickets','passengers.seat'])
    ->where('user_id',$r->user()->id)
    ->orderByDesc('created_at')
    ->paginate(10);
}


  public function cancel(\Illuminate\Http\Request $request, \App\Models\Booking $booking)
{
    // $this->authorize('cancel', $booking);

    $flight = $booking->flight;
    $now = now();
    
    // Caso especial: Si es un vuelo reubicado, permitir cancelación con reembolso completo
    if ($booking->relocated_from_flight_id) {
        // Reembolsar el monto total a la billetera
        \App\Models\WalletTransaction::createTransaction(
            $request->user()->id,
            'refund',
            $booking->total_amount,
            "Reembolso completo por cancelación de vuelo reubicado - Reserva {$booking->reservation_code}",
            $booking,
            [
                'booking_id' => $booking->id,
                'relocated_from_flight_id' => $booking->relocated_from_flight_id,
                'original_flight_id' => $booking->relocated_from_flight_id,
                'new_flight_id' => $booking->flight_id,
                'reason' => 'relocated_flight_cancelled_by_user'
            ],
            'COP'
        );
        
        // Marcar pagos como reembolsados
        foreach ($booking->payments as $payment) {
            if ($payment->status === 'completed' || $payment->status === 'paid') {
                $payment->update(['status' => 'refunded']);
            }
        }
        
        $booking->update(['status'=>'cancelled']);
        
        // Liberar asientos
        foreach ($booking->passengers as $bp) {
            if ($bp->seat) {
                $bp->seat->update(['status'=>'available']);
                $bp->update(['seat_id'=>null]);
            }
        }
        
        return response()->json(['ok'=>true, 'refunded' => true, 'amount' => $booking->total_amount]);
    }

    // Lógica normal de cancelación para vuelos no reubicados
    if ($booking->type === 'purchase') {
        // Se puede cancelar hasta 1 hora antes del vuelo -> refund
        if ($flight->departure_at->diffInMinutes($now, false) >= -60) {
            return response()->json(['error'=>'too_late_to_cancel_purchase'], 422);
        }
        
        // Procesar reembolso
        $payment = $booking->payments()->latest()->first();
        if ($payment && $payment->status === 'paid') {
            $payment->update(['status'=>'refunded']);
            
            // Si el pago fue con wallet, hacer reembolso a la billetera
            if ($payment->payment_method === 'wallet' && $payment->wallet_transaction_id) {
                \App\Models\WalletTransaction::createTransaction(
                    $request->user()->id,
                    'refund',
                    $payment->amount,
                    "Reembolso por cancelación de reserva #{$booking->id}",
                    $booking,
                    [
                        'booking_id' => $booking->id,
                        'original_payment_id' => $payment->id,
                        'original_transaction_id' => $payment->wallet_transaction_id,
                        'flight_code' => $flight->flight_code
                    ],
                    'COP'
                );
            }
            
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

    // Verificar duplicados de DNI dentro de la misma reserva (ya validado en Request, pero doble check)
    $dniList = $passengers->pluck('dni')->map(fn($dni) => strtolower(trim($dni)));
    if ($dniList->count() !== $dniList->unique()->count()) {
        return response()->json([
            'error' => 'duplicate_dni_in_booking',
            'message' => 'Hay cédulas duplicadas en la lista de pasajeros.'
        ], 422);
    }

    // Verificar duplicados de email dentro de la misma reserva
    $emailList = $passengers->pluck('email')->map(fn($email) => strtolower(trim($email)));
    if ($emailList->count() !== $emailList->unique()->count()) {
        return response()->json([
            'error' => 'duplicate_email_in_booking',
            'message' => 'Hay emails duplicados en la lista de pasajeros.'
        ], 422);
    }

    // Verificar si algún pasajero ya está registrado en otra reserva del mismo usuario para el mismo vuelo
    $existingPassengers = \App\Models\BookingPassenger::whereHas('booking', function($q) use ($request, $flight){
        $q->where('user_id', $request->user()->id)
          ->where('flight_id', $flight->id)
          ->whereIn('status', ['pending', 'confirmed']); // Solo reservas activas
    })->whereIn('dni', $dniList->toArray())->get();

    if ($existingPassengers->isNotEmpty()) {
        $duplicatedDnis = $existingPassengers->pluck('dni')->unique()->values();
        return response()->json([
            'error' => 'passenger_already_in_flight',
            'message' => 'Los siguientes pasajeros ya están registrados en este vuelo: ' . $duplicatedDnis->join(', '),
            'duplicated_dnis' => $duplicatedDnis
        ], 422);
    }

    // Verificar disponibilidad de asientos por clase
    $firstClassCount = $passengers->where('class', 'first')->count();
    $economyCount = $passengers->where('class', 'economy')->count();

    $availableFirst = \App\Models\Seat::where('flight_id',$flight->id)
        ->where('class','first')
        ->where('status','available')
        ->count();

    $availableEconomy = \App\Models\Seat::where('flight_id',$flight->id)
        ->where('class','economy')
        ->where('status','available')
        ->count();

    if ($firstClassCount > $availableFirst || $economyCount > $availableEconomy) {
        return response()->json(['error'=>'not_enough_seats'],422);
    }

    // VALIDACIONES DE PAGO ANTES DE LA TRANSACCIÓN
    // Calcular total ANTES de entrar a la transacción para validar saldo
    $totalBeforeDiscount = $passengers->reduce(function($sum, $p) use ($flight) {
        $price = $p['class'] === 'first' 
            ? ($flight->first_class_price ?? $flight->price_per_seat * 2)
            : $flight->price_per_seat;
        return $sum + $price;
    }, 0);

    $total = $totalBeforeDiscount;
    if ($flight->hasActivePromotion()) {
        $total = $flight->getDiscountedPrice($totalBeforeDiscount);
    }

    // Si es compra, validar método de pago ANTES de la transacción
    if ($data['type'] === 'purchase') {
        if (!isset($data['payment']) || !isset($data['payment']['method'])) {
            return response()->json([
                'error' => 'payment_required',
                'message' => 'Debe seleccionar un método de pago'
            ], 422);
        }

        $paymentMethod = $data['payment']['method'];
        
        // Validar saldo si es wallet
        if ($paymentMethod === 'wallet') {
            if ($request->user()->wallet_balance < $total) {
                return response()->json([
                    'error' => 'insufficient_balance',
                    'message' => 'Saldo insuficiente en la billetera',
                    'current_balance' => $request->user()->wallet_balance,
                    'required_amount' => $total
                ], 422);
            }
        }
        // Validar datos de tarjeta si es card
        elseif (in_array($paymentMethod, ['saved_card', 'new_card', 'card'])) {
            if (empty($data['payment']['card_type']) && empty($data['payment']['card_id'])) {
                return response()->json([
                    'error' => 'payment_data_missing',
                    'message' => 'Faltan datos de pago con tarjeta'
                ], 422);
            }
        }
    }

    // AHORA SÍ: TRANSACCIÓN PRINCIPAL
    $booking = DB::transaction(function () use ($request, $flight, $data, $passengers, $total, $totalBeforeDiscount) {
        $expires = $data['type'] === 'reservation' ? now()->addDay() : null;
        
        // Aplicar descuento si existe promoción activa
        $discountApplied = $totalBeforeDiscount - $total;
        $promotionId = null;
        
        if ($flight->hasActivePromotion()) {
            $promotionId = $flight->activePromotion->id;
        }

        $reservationCode = Str::upper(Str::random(8));

        $booking = \App\Models\Booking::create([
            'user_id'=>$request->user()->id,
            'flight_id'=>$flight->id,
            'promotion_id'=>$promotionId,
            'type'   =>$data['type'],
            'status' =>$data['type']==='purchase' ? 'confirmed' : 'pending',
            'reservation_code'=> $reservationCode,
            'travel_type'=>$data['travel_type'] ?? 'one_way',
            'expires_at'=>$expires,
            'seats_count'=>$passengers->count(),
            'total_amount'=>$total,
            'original_amount'=>$totalBeforeDiscount,
            'discount_amount'=>$discountApplied,
        ]);

        foreach ($passengers->values() as $i => $p) {
            // Obtener asiento disponible de la clase del pasajero
            $seat = \App\Models\Seat::where('flight_id',$flight->id)
                ->where('class', $p['class'])
                ->where('status','available')
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            if (!$seat) {
                throw new \Exception("No seat available for class {$p['class']}");
            }

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
                'class'=>$p['class'],
            ]);

            // Tickets SOLO en compra
            if ($data['type']==='purchase') {
                \App\Models\Ticket::create([
                    'booking_id'=>$booking->id,
                    'booking_passenger_id'=>$bp->id,
                    'ticket_code'=>Str::upper(Str::random(10)),
                    'status'=>'issued',
                ]);
            }
        }

        // Procesar pago si es compra
        if ($data['type']==='purchase') {
            $paymentMethod = $data['payment']['method'];
            $paymentMeta = ['method' => $paymentMethod];
            $walletTransactionId = null;
            
            // Si el pago es con wallet
            if ($paymentMethod === 'wallet') {
                // Crear transacción de wallet (descuento automático)
                $walletTransaction = \App\Models\WalletTransaction::createTransaction(
                    $request->user()->id,
                    'purchase',
                    $total,
                    "Compra de vuelo {$flight->flight_code}",
                    $booking,
                    [
                        'booking_id' => $booking->id,
                        'flight_code' => $flight->flight_code,
                        'passengers_count' => count($data['passengers'])
                    ],
                    'COP'
                );
                
                $walletTransactionId = $walletTransaction->id;
                $paymentMeta = [
                    'method' => 'wallet',
                    'wallet_transaction_id' => $walletTransactionId,
                    'balance_before' => $walletTransaction->balance_before,
                    'balance_after' => $walletTransaction->balance_after
                ];
            }
            // Si el pago es con tarjeta (guardada o nueva)
            elseif (in_array($paymentMethod, ['saved_card', 'new_card', 'card'])) {
                $paymentData = $data['payment'];
                $paymentMeta = [
                    'method' => $paymentMethod,
                    'card_type' => $paymentData['card_type'] ?? 'unknown',
                    'last_four' => $paymentData['last_four'] ?? 'XXXX',
                    'transaction_id' => $paymentData['transaction_id'] ?? Str::random(16),
                    'card_holder' => $paymentData['card_holder'] ?? 'N/A',
                ];
                
                // Si es nueva tarjeta y quiere guardarla
                if ($paymentMethod === 'new_card' && isset($paymentData['save_card']) && $paymentData['save_card']) {
                    $expiryParts = explode('/', $paymentData['expiry_date'] ?? '');
                    $expYear = $expiryParts[1] ?? date('Y');
                    
                    // Si el año viene en formato de 2 dígitos (28), añadir "20"
                    // Si ya viene en formato completo (2028), dejarlo así
                    if (strlen($expYear) == 2) {
                        $expYear = '20' . $expYear;
                    }
                    
                    \App\Models\Card::create([
                        'user_id' => $request->user()->id,
                        'brand' => $paymentData['card_type'] ?? 'unknown',
                        'holder_name' => $paymentData['card_holder'] ?? '',
                        'last4' => $paymentData['last_four'] ?? 'XXXX',
                        'exp_month' => $expiryParts[0] ?? '01',
                        'exp_year' => $expYear,
                        'token' => null,
                    ]);
                }
            }
            
            // Crear registro de pago
            \App\Models\Payment::create([
                'payable_type'=>\App\Models\Booking::class,
                'payable_id'=>$booking->id,
                'user_id'=>$request->user()->id,
                'status'=>'paid',
                'amount'=>$total,
                'payment_method'=>$paymentMethod,
                'wallet_transaction_id'=>$walletTransactionId,
                'meta'=>$paymentMeta,
            ]);

            // Cargar relaciones una sola vez
            $bookingWithData = $booking->load('flight.origin', 'flight.destination', 'passengers.seat');
            
            // Enviar correo de compra a cada pasajero INDIVIDUALMENTE
            foreach ($bookingWithData->passengers as $p) {
                try {
                    Mail::to($p->email)->send(new \App\Mail\PurchaseMail($bookingWithData, $p));
                    Log::info("Email de compra enviado a: {$p->email} ({$p->first_name} {$p->last_name})");
                } catch (\Exception $e) {
                    Log::error("Error enviando email de compra a {$p->email}: " . $e->getMessage());
                }
            }
        } else {
            // RESERVA: Enviar correo de confirmación de reserva a cada pasajero INDIVIDUALMENTE
            $bookingWithData = $booking->load('flight.origin', 'flight.destination', 'passengers.seat');
            
            foreach ($bookingWithData->passengers as $p) {
                try {
                    Mail::to($p->email)->send(new \App\Mail\ReservationConfirmationMail($bookingWithData, $p));
                    Log::info("Email de reserva enviado a: {$p->email} ({$p->first_name} {$p->last_name})");
                } catch (\Exception $e) {
                    Log::error("Error enviando email de reserva a {$p->email}: " . $e->getMessage());
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

        // Procesar pago según el método seleccionado
        $paymentData = $request->input('payment', []);
        $paymentMethod = $paymentData['method'] ?? 'card';
        $paymentMeta = ['method' => $paymentMethod, 'converted_from_reservation' => true];
        $walletTransactionId = null;
        
        // Si el pago es con wallet
        if ($paymentMethod === 'wallet') {
            // Verificar saldo suficiente
            if ($request->user()->wallet_balance < $booking->total_amount) {
                throw new \Exception('Saldo insuficiente en la billetera');
            }
            
            // Crear transacción de wallet (descuento automático)
            $walletTransaction = \App\Models\WalletTransaction::createTransaction(
                $request->user()->id,
                'purchase',
                $booking->total_amount,
                "Conversión a compra de reserva #{$booking->id}",
                $booking,
                [
                    'booking_id' => $booking->id,
                    'flight_code' => $booking->flight->flight_code,
                    'converted_from_reservation' => true
                ],
                'COP'
            );
            
            $walletTransactionId = $walletTransaction->id;
            $paymentMeta = array_merge($paymentMeta, [
                'wallet_transaction_id' => $walletTransactionId,
                'balance_before' => $walletTransaction->balance_before,
                'balance_after' => $walletTransaction->balance_after
            ]);
        }
        // Si el pago es con tarjeta
        elseif (in_array($paymentMethod, ['saved_card', 'new_card', 'card'])) {
            $paymentMeta = array_merge($paymentMeta, [
                'card_type' => $paymentData['card_type'] ?? 'unknown',
                'last_four' => $paymentData['last_four'] ?? 'XXXX',
                'transaction_id' => $paymentData['transaction_id'] ?? Str::random(16),
                'card_holder' => $paymentData['card_holder'] ?? 'N/A',
            ]);
            
            // Si es nueva tarjeta y quiere guardarla
            if ($paymentMethod === 'new_card' && isset($paymentData['save_card']) && $paymentData['save_card']) {
                $expiryParts = explode('/', $paymentData['expiry_date'] ?? '');
                $expYear = $expiryParts[1] ?? date('Y');
                
                // Si el año viene en formato de 2 dígitos (28), añadir "20"
                // Si ya viene en formato completo (2028), dejarlo así
                if (strlen($expYear) == 2) {
                    $expYear = '20' . $expYear;
                }
                
                \App\Models\Card::create([
                    'user_id' => $request->user()->id,
                    'brand' => $paymentData['card_type'] ?? 'unknown',
                    'holder_name' => $paymentData['card_holder'] ?? '',
                    'last4' => $paymentData['last_four'] ?? 'XXXX',
                    'exp_month' => $expiryParts[0] ?? '01',
                    'exp_year' => $expYear,
                    'token' => null,
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
            'payment_method' => $paymentMethod,
            'wallet_transaction_id' => $walletTransactionId,
            'meta' => $paymentMeta,
        ]);

        // Enviar correo de confirmación de compra a cada pasajero
        $bookingWithData = $booking->load('flight.origin','flight.destination','passengers.seat');
        foreach ($bookingWithData->passengers as $p) {
            if ($p->email) {
                try {
                    Mail::to($p->email)->send(new \App\Mail\PurchaseMail($bookingWithData, $p));
                    Log::info("Email de conversión a compra enviado a: {$p->email}");
                } catch (\Exception $e) {
                    Log::error("Error enviando email de conversión a {$p->email}: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Reserva convertida a compra exitosamente',
            'booking' => $booking->fresh()->load('passengers.seat', 'tickets', 'payments')
        ]);
    });
}

/**
 * Asignar asiento a un pasajero durante check-in
 */
public function assignSeat(Request $request, $bookingId, $passengerId)
{
    $validator = Validator::make($request->all(), [
        'seat_id' => 'required|exists:seats,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'ok' => false,
            'message' => 'Datos inválidos',
            'errors' => $validator->errors()
        ], 422);
    }

    // Verificar que la reserva pertenezca al usuario
    $booking = \App\Models\Booking::where('id', $bookingId)
        ->where('user_id', auth()->id())
        ->with('passengers', 'flight')
        ->first();

    if (!$booking) {
        return response()->json([
            'ok' => false,
            'message' => 'Reserva no encontrada'
        ], 404);
    }

    // Verificar que el pasajero pertenezca a esta reserva
    $passenger = $booking->passengers()->where('id', $passengerId)->first();
    
    if (!$passenger) {
        return response()->json([
            'ok' => false,
            'message' => 'Pasajero no encontrado en esta reserva'
        ], 404);
    }

    // Verificar que el asiento esté disponible o sea el asiento actual del pasajero
    $seat = \App\Models\Seat::where('id', $request->seat_id)
        ->where('flight_id', $booking->flight_id)
        ->where('class', $passenger->class)
        ->where(function($query) use ($passenger) {
            $query->where('status', 'available')
                  ->orWhere('id', $passenger->seat_id); // Permitir el asiento actual
        })
        ->first();

    if (!$seat) {
        return response()->json([
            'ok' => false,
            'message' => 'El asiento no está disponible'
        ], 422);
    }

    // Si el pasajero ya tiene un asiento asignado y está cambiando a otro, liberarlo
    if ($passenger->seat_id && $passenger->seat_id != $seat->id) {
        $oldSeat = \App\Models\Seat::find($passenger->seat_id);
        if ($oldSeat) {
            $oldSeat->update(['status' => 'available']);
        }
    }

    // Asignar asiento (actualizar solo si es diferente)
    if ($passenger->seat_id != $seat->id) {
        $passenger->update(['seat_id' => $seat->id]);
        $seat->update(['status' => 'assigned']);
    }

    return response()->json([
        'ok' => true,
        'message' => 'Asiento asignado exitosamente',
        'passenger' => $passenger->fresh('seat')
    ]);
}

}
