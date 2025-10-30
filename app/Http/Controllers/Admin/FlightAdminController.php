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

    // Agregar información sobre si tiene ventas
    $flights->getCollection()->transform(function($flight) {
      $flight->has_sales = $flight->tickets()->exists() || $flight->bookings()->where('type','purchase')->exists();
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
        
        // Manejar imagen si existe
        $imagePath = null;
        if ($r->hasFile('image')) {
          try {
            $imagePath = $r->file('image')->store('flights', 'public');
            if (!$imagePath) {
              throw new \Exception('No se pudo guardar la imagen.');
            }
          } catch (\Exception $e) {
            throw new \Exception('Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.');
          }
        }
      
      $flight = Flight::create([
        'code'             => $code,
        'aircraft_id'      => $data['aircraft_id'] ?? null,
        'origin_id'        => $data['origin_id'],
        'destination_id'   => $data['destination_id'],
        'scope'            => $data['scope'],
        'departure_at'     => $data['departure_at'],
        'arrival_at'       => now()->parse($data['departure_at'])->addMinutes((int)$data['duration_minutes']), // puedes ajustar TZs luego
        'duration_minutes' => (int)$data['duration_minutes'],
        'status'           => 'scheduled',
        'price_per_seat'   => $data['price_per_seat'],
        'first_class_price'=> $data['first_class_price'] ?? ($data['price_per_seat'] * 2),
        'capacity_first'   => (int)$data['capacity_first'],
        'capacity_economy' => (int)$data['capacity_economy'],
        'image_path'       => $imagePath,
      ]);

      // Generar asientos
      $seats = [];
      for ($i=1; $i <= (int)$data['capacity_first']; $i++) {
          $seats[] = [
              'flight_id' => $flight->id,
              'number'    => "F{$i}",         // <-- STRING
              'class'     => 'first',
              'status'    => 'available',
              'created_at'=> now(),
              'updated_at'=> now(),
          ];
      }
      for ($i=1; $i <= (int)$data['capacity_economy']; $i++) {
          $seats[] = [
              'flight_id' => $flight->id,
              'number'    => "E{$i}",         // <-- STRING
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
      $restrictedFields = ['origin_id', 'destination_id', 'aircraft_id', 'scope', 'capacity_first', 'capacity_economy', 'duration_minutes'];
      $attemptedChanges = [];
      
      foreach ($restrictedFields as $field) {
        if ($r->has($field) && $r->input($field) != $flight->$field) {
          $fieldNames = [
            'origin_id' => 'origen',
            'destination_id' => 'destino',
            'aircraft_id' => 'avión',
            'scope' => 'tipo de vuelo',
            'capacity_first' => 'capacidad de primera clase',
            'capacity_economy' => 'capacidad económica',
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
    if (isset($data['capacity_first'])) {
      $data['capacity_first'] = (int)$data['capacity_first'];
    }
    if (isset($data['capacity_economy'])) {
      $data['capacity_economy'] = (int)$data['capacity_economy'];
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

  // POST /admin/flights/{flight}/cancel
  public function cancel(Request $r, Flight $flight) {
    if (in_array($flight->status, ['cancelled','completed'])) {
      return response()->json(['error'=>'flight_not_cancellable'], 422);
    }

    return DB::transaction(function() use ($flight) {
      $flight->update(['status'=>'cancelled']);

      // Cancelar & reembolsar bookings
      $bookings = $flight->bookings()->with(['tickets','payments','passengers'])->get();
      foreach ($bookings as $b) {
        if ($b->type === 'purchase') {
          // reembolsar pagos
          foreach ($b->payments as $p) { $p->update(['status'=>'refunded']); }
        }
        // liberar asientos / anular tickets
        foreach ($b->tickets as $t) {
          $t->update(['status'=>'cancelled']);
          if ($t->seat_id) { \App\Models\Seat::where('id',$t->seat_id)->update(['status'=>'available']); }
        }
        $b->update(['status'=>'cancelled']);
      }

      // Noticia automática
      \App\Models\News::create([
        'title'        => "Vuelo {$flight->code} cancelado",
        'body'         => 'Lamentamos los inconvenientes. Si tenías compra, tu reembolso se está procesando automáticamente.',
        'flight_id'    => $flight->id,
      ]);

      return ['ok'=>true];
    });
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
