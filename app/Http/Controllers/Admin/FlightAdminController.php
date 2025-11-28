<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlightStoreRequest;
use App\Http\Requests\Admin\FlightUpdateRequest;
use App\Models\Flight;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class FlightAdminController extends Controller
{
  // GET /admin/flights
  public function index(Request $r) {
    $q = Flight::query()->with(['origin','destination','anyValidPromotion'])
      ->when($r->filled('code'), fn($q)=>$q->where('code','like','%'.$r->code.'%'))
      ->when($r->filled('status'), fn($q)=>$q->where('status',$r->status))
      ->when($r->filled('origin_id'), fn($q)=>$q->where('origin_id',$r->origin_id))
      ->when($r->filled('destination_id'), fn($q)=>$q->where('destination_id',$r->destination_id))
      ->orderByDesc('departure_at');

    $flights = $q->paginate(12);

    // Agregar información sobre si tiene ventas y hora de llegada con zona horaria
    $flights->getCollection()->transform(function($flight) {
      $flight->has_sales = $flight->tickets()->exists() || $flight->bookings()->where('type','purchase')->exists();
      $flight->arrival_info = $flight->getFormattedArrivalTime();
      return $flight;
    });

    return $flights;
  }

  // POST /admin/flights
  public function store(FlightStoreRequest $r) {
    try {
      return DB::transaction(function() use ($r) {
        $data = $r->validated();
        
        // Generar código único para el vuelo
        $code = $this->generateFlightCode($data['scope']);
        
        // Obtener capacidades según el scope
        $capacityFirst = Flight::getDefaultFirstClassCapacity($data['scope']);
        $capacityEconomy = Flight::getDefaultEconomyCapacity($data['scope']);
        
        // Manejar imagen - usar por defecto si no se proporciona
        $imagePath = 'flights/default-flight.svg'; // Imagen por defecto
        if ($r->hasFile('image')) {
          try {
            $uploadedPath = $r->file('image')->store('flights', 'public');
            if ($uploadedPath) {
              $imagePath = $uploadedPath;
            }
          } catch (\Exception $e) {
            // Si falla la carga, usar la imagen por defecto
            \Log::warning('Error al cargar imagen de vuelo: ' . $e->getMessage());
          }
        }
      
      $flight = Flight::create([
        'code'             => $code,
        'aircraft_id'      => null,
        'origin_id'        => $data['origin_id'],
        'destination_id'   => $data['destination_id'],
        'scope'            => $data['scope'],
        'departure_at'     => $data['departure_at'],
        'arrival_at'       => now()->parse($data['departure_at'])->addMinutes((int)$data['duration_minutes']),
        'duration_minutes' => (int)$data['duration_minutes'],
        'status'           => 'scheduled',
        'price_per_seat'   => $data['price_per_seat'],
        'first_class_price'=> $data['first_class_price'] ?? ($data['price_per_seat'] * 2),
        'capacity_first'   => $capacityFirst,
        'capacity_economy' => $capacityEconomy,
        'image_path'       => $imagePath,
      ]);

      // Generar asientos con numeración secuencial
      $seats = [];
      
      // Asientos de primera clase (1-25 para nacional, 1-50 para internacional)
      for ($i=1; $i <= $capacityFirst; $i++) {
          $seats[] = [
              'flight_id' => $flight->id,
              'number'    => (string)$i,
              'class'     => 'first',
              'status'    => 'available',
              'created_at'=> now(),
              'updated_at'=> now(),
          ];
      }
      
      // Asientos de clase económica (26-150 para nacional, 51-250 para internacional)
      for ($i=1; $i <= $capacityEconomy; $i++) {
          $seatNumber = $capacityFirst + $i;
          $seats[] = [
              'flight_id' => $flight->id,
              'number'    => (string)$seatNumber,
              'class'     => 'economy',
              'status'    => 'available',
              'created_at'=> now(),
              'updated_at'=> now(),
          ];
      }
      
      \App\Models\Seat::insert($seats);

      // Crear noticia automática con la imagen del vuelo
      \App\Models\News::create([
        'title' => "Nuevo vuelo {$flight->code}: ".$flight->origin->name.' → '.$flight->destination->name,
        'body'  => '¡Ya disponible para reservas y compras!',
        'flight_id' => $flight->id,
        'image_path' => $imagePath,
        'is_promotion' => false,
      ]);

      return $flight->load(['origin','destination']);
      });
    } catch (\Exception $e) {
      return response()->json([
        'message' => $e->getMessage(),
        'errors' => ['image' => [$e->getMessage()]]
      ], 422);
    }
  }

  // PUT /admin/flights/{flight}
  public function update(FlightUpdateRequest $r, Flight $flight) {
    // Verificar si el vuelo ya pasó
    if ($flight->departure_at->isPast()) {
      return response()->json([
        'message' => 'No se puede editar un vuelo cuya fecha de salida ya pasó.'
      ], 422);
    }

    // Verificar si hay ventas (tickets emitidos o reservas confirmadas)
    $hasSales = $flight->tickets()->exists() || $flight->bookings()->whereIn('status', ['confirmed', 'pending'])->exists();
    
    if ($flight->status !== 'scheduled' || $hasSales) {
      // Si hay ventas, verificar si intentan cambiar campos restringidos
      $restrictedFields = ['origin_id', 'destination_id', 'scope', 'duration_minutes'];
      $attemptedChanges = [];
      
      foreach ($restrictedFields as $field) {
        if ($r->has($field) && $r->input($field) != $flight->$field) {
          $fieldNames = [
            'origin_id' => 'origen',
            'destination_id' => 'destino',
            'scope' => 'tipo de vuelo',
            'duration_minutes' => 'duración'
          ];
          $attemptedChanges[] = $fieldNames[$field] ?? $field;
        }
      }
      
      if (!empty($attemptedChanges)) {
        return response()->json([
          'message' => 'Este vuelo ya tiene reservas o compras. Solo puedes cambiar los precios',
        ], 422);
      }
      
      // Si hay ventas, solo permitimos cambiar precios y fecha de salida (si es futura)
      $data = $r->safe()->only(['price_per_seat','first_class_price','departure_at']);
      
      if (isset($data['departure_at'])) {
        abort_if(now()->parse($data['departure_at'])->isPast(), 422, 'No puedes fijar una salida en el pasado.');
        $data['arrival_at'] = now()->parse($data['departure_at'])->addMinutes((int)$flight->duration_minutes);
      }
      
      // Manejar imagen si existe
      if ($r->hasFile('image')) {
        try {
          $imagePath = $r->file('image')->store('flights', 'public');
          if (!$imagePath) {
            throw new \Exception('No se pudo guardar la imagen.');
          }
          $data['image_path'] = $imagePath;
        } catch (\Exception $e) {
          return response()->json([
            'message' => 'Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.',
            'errors' => ['image' => ['Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.']]
          ], 422);
        }
      }
      
      $flight->update($data);

      // Actualizar la noticia existente del vuelo (si existe)
      $news = \App\Models\News::where('flight_id', $flight->id)
        ->where('is_promotion', false)
        ->first();

      if ($news) {
        $news->update([
          'title' => "Nuevo vuelo {$flight->code}: ".$flight->origin->name.' → '.$flight->destination->name,
          'body'  => '¡Ya disponible para reservas y compras!',
          'image_path' => $data['image_path'] ?? $flight->image_path,
        ]);
      }

      return $flight->fresh()->load(['origin','destination']);
    }

    // Caso editable completo
    $data = $r->validated();
    if (isset($data['departure_at'])) {
      $data['arrival_at'] = now()->parse($data['departure_at'])->addMinutes((int)($data['duration_minutes'] ?? $flight->duration_minutes));
    }
    if (isset($data['duration_minutes'])) {
      $data['duration_minutes'] = (int)$data['duration_minutes'];
    }
    
    // Manejar imagen si existe
    if ($r->hasFile('image')) {
      try {
        $imagePath = $r->file('image')->store('flights', 'public');
        if (!$imagePath) {
          throw new \Exception('No se pudo guardar la imagen.');
        }
        $data['image_path'] = $imagePath;
      } catch (\Exception $e) {
        return response()->json([
          'message' => 'Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.',
          'errors' => ['image' => ['Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.']]
        ], 422);
      }
    }
    $flight->update($data);

    // Actualizar la noticia existente del vuelo (si existe)
    $updatedFlight = $flight->fresh()->load(['origin','destination']);
    $news = \App\Models\News::where('flight_id', $updatedFlight->id)
      ->where('is_promotion', false)
      ->first();

    if ($news) {
      $news->update([
        'title' => "Nuevo vuelo {$updatedFlight->code}: ".$updatedFlight->origin->name.' → '.$updatedFlight->destination->name,
        'body'  => '¡Ya disponible para reservas y compras!',
        'image_path' => $data['image_path'] ?? $updatedFlight->image_path,
      ]);
    }

    return $updatedFlight;
  }

  // DELETE /admin/flights/{flight}
  public function destroy(Flight $flight) {
    // Verificar si el vuelo tiene tiquetes vendidos
    $hasSales = $flight->tickets()->exists() || $flight->bookings()->where('type','purchase')->exists();

    if ($hasSales) {
      return response()->json([
        'error' => 'No se puede eliminar el vuelo porque ya tiene tiquetes vendidos.'
      ], 422);
    }

    return DB::transaction(function() use ($flight) {
      // Eliminar asientos asociados
      $flight->seats()->delete();

      // Eliminar reservas (si no son compras)
      $flight->bookings()->where('type', 'reservation')->delete();

      // Eliminar noticias asociadas al vuelo
      \App\Models\News::where('flight_id', $flight->id)->delete();

      // Eliminar promociones asociadas
      \App\Models\Promotion::where('flight_id', $flight->id)->delete();

      // Finalmente eliminar el vuelo
      $flight->delete();

      return ['ok' => true, 'message' => 'Vuelo eliminado correctamente'];
    });
  }

  // GET /admin/flights/{flight}/affected-passengers
  public function affectedPassengers(Flight $flight) {
    $count = \App\Models\Booking::where('flight_id', $flight->id)
      ->whereIn('status', ['confirmed', 'pending'])
      ->withCount('passengers')
      ->get()
      ->sum('passengers_count');
    
    return response()->json(['count' => $count]);
  }

  // GET /admin/flights/{flight}/alternative-flights
  public function alternativeFlights(Flight $flight) {
    // Obtener número total de pasajeros que necesitan reubicación
    $totalPassengers = \App\Models\Booking::where('flight_id', $flight->id)
      ->whereIn('status', ['confirmed', 'pending'])
      ->withCount('passengers')
      ->get()
      ->sum('passengers_count');
    
    // Contar pasajeros por clase
    $passengersByClass = \App\Models\BookingPassenger::whereHas('booking', function($q) use ($flight) {
      $q->where('flight_id', $flight->id)
        ->whereIn('status', ['confirmed', 'pending']);
    })->select('class', DB::raw('count(*) as total'))
      ->groupBy('class')
      ->pluck('total', 'class')
      ->toArray();
    
    $economyNeeded = $passengersByClass['economy'] ?? 0;
    $firstClassNeeded = $passengersByClass['first'] ?? 0;
    
    // Buscar vuelos alternativos
    // Criterios: misma ruta, fecha cercana (+/- 3 días), estado programado
    $departureDate = \Carbon\Carbon::parse($flight->departure_at);
    $minDate = $departureDate->copy()->subDays(3);
    $maxDate = $departureDate->copy()->addDays(3);
    
    $alternatives = Flight::where('id', '!=', $flight->id)
      ->where('origin_id', $flight->origin_id)
      ->where('destination_id', $flight->destination_id)
      ->where('status', 'scheduled')
      ->whereBetween('departure_at', [$minDate, $maxDate])
      ->with(['origin', 'destination', 'aircraft'])
      ->get()
      ->map(function($alt) use ($economyNeeded, $firstClassNeeded) {
        // Contar asientos disponibles por clase
        $availableEconomy = \App\Models\Seat::where('flight_id', $alt->id)
          ->where('class', 'economy')
          ->where('status', 'available')
          ->count();
        
        $availableFirst = \App\Models\Seat::where('flight_id', $alt->id)
          ->where('class', 'first')
          ->where('status', 'available')
          ->count();
        
        return [
          'id' => $alt->id,
          'code' => $alt->code,
          'flight_number' => $alt->flight_number,
          'departure_at' => $alt->departure_at,
          'origin' => $alt->origin,
          'destination' => $alt->destination,
          'available_economy' => $availableEconomy,
          'available_first_class' => $availableFirst,
          'can_accommodate' => $availableEconomy >= $economyNeeded && $availableFirst >= $firstClassNeeded
        ];
      })
      ->filter(function($alt) {
        return $alt['can_accommodate']; // Solo vuelos con suficiente capacidad
      })
      ->values();
    
    return response()->json([
      'flights' => $alternatives,
      'passengers_needed' => [
        'economy' => $economyNeeded,
        'first_class' => $firstClassNeeded,
        'total' => $totalPassengers
      ]
    ]);
  }

  // POST /admin/flights/{flight}/cancel
  public function cancel(Request $r, Flight $flight) {
    if (in_array($flight->status, ['cancelled','completed'])) {
      return response()->json(['error'=>'flight_not_cancellable'], 422);
    }

    $r->validate([
      'cancel_option' => 'required|in:relocate,refund',
      'alternative_flight_id' => 'nullable|required_if:cancel_option,relocate|exists:flights,id'
    ]);

    return DB::transaction(function() use ($flight, $r) {
      $flight->update(['status'=>'cancelled']);
      
      // Cargar relaciones necesarias del vuelo
      $flight->load(['origin', 'destination']);

      // Obtener todas las reservas afectadas
      $bookings = $flight->bookings()
        ->whereIn('status', ['confirmed', 'pending'])
        ->with(['tickets', 'payments', 'passengers.seat', 'user'])
        ->get();

      if ($r->cancel_option === 'relocate') {
        // Opción 1: Reubicar pasajeros
        $alternativeFlight = Flight::with(['origin', 'destination'])->findOrFail($r->alternative_flight_id);
        
        foreach ($bookings as $booking) {
          // Liberar asientos del vuelo cancelado
          foreach ($booking->passengers as $passenger) {
            if ($passenger->seat_id) {
              \App\Models\Seat::where('id', $passenger->seat_id)
                ->update(['status' => 'available']);
            }
          }
          
          // Asignar asientos en el vuelo alternativo
          foreach ($booking->passengers as $passenger) {
            $newSeat = \App\Models\Seat::where('flight_id', $alternativeFlight->id)
              ->where('class', $passenger->class)
              ->where('status', 'available')
              ->first();
            
            if ($newSeat) {
              $passenger->update(['seat_id' => $newSeat->id]);
              $newSeat->update(['status' => 'assigned']);
            }
          }
          
          // Actualizar la reserva al nuevo vuelo y marcar la relocalización
          $booking->update([
            'relocated_from_flight_id' => $flight->id, // Registrar el vuelo original
            'flight_id' => $alternativeFlight->id,
            'status' => 'confirmed'
          ]);
          
          // Actualizar tickets - mantener como 'issued'
          foreach ($booking->tickets as $ticket) {
            $ticket->update(['status' => 'issued']);
          }
          
          // Recargar booking con nuevas relaciones para el correo
          $booking->load(['flight', 'passengers.seat']);
          
          // Enviar correo de reubicación
          try {
            Mail::to($booking->user->email)->send(
              new \App\Mail\FlightRelocatedMail($booking, $flight, $alternativeFlight)
            );
            Log::info("Correo de reubicación enviado a: {$booking->user->email}, Reserva: {$booking->booking_code}");
          } catch (\Exception $e) {
            Log::error('Error sending relocation email: ' . $e->getMessage());
          }
        }
        
        // Noticia automática
        \App\Models\News::create([
          'title' => "Vuelo {$flight->code} reubicado",
          'body' => "El vuelo {$flight->code} ha sido cancelado. Los pasajeros han sido reubicados al vuelo {$alternativeFlight->code}. Si deseas cancelar tu reserva, puedes hacerlo desde Mi Viajes para recibir un reembolso completo.",
          'flight_id' => $flight->id,
        ]);
        
      } else {
        // Opción 2: Reembolsar a todos
        foreach ($bookings as $booking) {
          // Calcular monto total a reembolsar
          $refundAmount = 0;
          
          if ($booking->type === 'purchase') {
            // Para compras: reembolsar pagos con tarjeta/PSE
            foreach ($booking->payments as $payment) {
              if ($payment->status === 'completed') {
                $payment->update(['status' => 'refunded']);
                $refundAmount += $payment->amount;
              }
            }
          }
          
          // Devolver dinero a la billetera del usuario
          // Esto incluye tanto pagos con tarjeta como pagos directos con wallet
          $totalToRefund = $booking->total_amount > 0 ? $booking->total_amount : $refundAmount;
          
          if ($totalToRefund > 0) {
            $transaction = \App\Models\WalletTransaction::createTransaction(
              $booking->user_id,
              'refund',
              $totalToRefund,
              "Reembolso por cancelación del vuelo {$flight->code} - Reserva {$booking->booking_code}",
              null,
              [
                'flight_id' => $flight->id, 
                'booking_id' => $booking->id,
                'flight_code' => $flight->code,
                'reason' => 'flight_cancelled_by_admin'
              ],
              'COP'
            );
            
            Log::info("Reembolso procesado: Usuario {$booking->user_id}, Monto: {$totalToRefund}, Reserva: {$booking->booking_code}, Nuevo balance: {$transaction->balance_after}");
          }
          
          // Liberar asientos y anular tickets
          foreach ($booking->tickets as $ticket) {
            $ticket->update(['status' => 'cancelled']);
            if ($ticket->seat_id) {
              \App\Models\Seat::where('id', $ticket->seat_id)
                ->update(['status' => 'available']);
            }
          }
          
          $booking->update(['status' => 'cancelled']);
          
          // Enviar correo de cancelación y reembolso
          try {
            Mail::to($booking->user->email)->send(
              new \App\Mail\FlightCancelledRefundMail($booking, $flight)
            );
            Log::info("Correo de reembolso enviado a: {$booking->user->email}, Reserva: {$booking->booking_code}, Monto: {$totalToRefund}");
          } catch (\Exception $e) {
            Log::error('Error sending cancellation email: ' . $e->getMessage());
          }
        }
        
        // Noticia automática
        \App\Models\News::create([
          'title' => "Vuelo {$flight->code} cancelado",
          'body' => 'Lamentamos los inconvenientes. El vuelo ha sido cancelado y se ha procesado el reembolso automático a tu billetera.',
          'flight_id' => $flight->id,
        ]);
      }

      return ['ok' => true, 'option' => $r->cancel_option];
    });
  }

  // POST /admin/flights/update-statuses
  public function updateStatuses(Request $r) {
    try {
      $now = \Carbon\Carbon::now();
      $completedFlights = [];
      $updatedCount = 0;

      // Obtener todos los vuelos programados
      $flights = Flight::where('status', 'scheduled')
        ->with(['origin', 'destination'])
        ->get();

      foreach ($flights as $flight) {
        $departureTime = \Carbon\Carbon::parse($flight->departure_at);
        $arrivalTime = $departureTime->copy()->addMinutes($flight->duration_minutes);

        // Si ya llegó el vuelo (hora de salida + duración < ahora)
        if ($arrivalTime->lessThan($now)) {
          $flight->update(['status' => 'completed']);
          
          $completedFlights[] = [
            'code' => $flight->code,
            'route' => $flight->origin->name . ' → ' . $flight->destination->name,
            'departure' => $departureTime->format('d/m/Y H:i'),
            'arrival' => $arrivalTime->format('d/m/Y H:i'),
          ];
          
          $updatedCount++;
        }
      }

      return response()->json([
        'success' => true,
        'updated_count' => $updatedCount,
        'flights' => $completedFlights,
        'message' => $updatedCount > 0 
          ? "Se actualizaron {$updatedCount} vuelo(s) a estado 'completado'"
          : "No hay vuelos pendientes de actualizar"
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar estados: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Genera un código único para el vuelo
   */
  private function generateFlightCode($scope)
  {
    $prefix = $scope === 'international' ? 'INT' : 'NAC';
    
    do {
      $number = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
      $code = $prefix . '-' . $number;
      
      // Verificar que no exista
      $exists = Flight::where('code', $code)->exists();
    } while ($exists);
    
    return $code;
  }
}
