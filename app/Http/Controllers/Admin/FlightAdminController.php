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
    $q = Flight::query()->with(['origin','destination'])
      ->when($r->filled('code'), fn($q)=>$q->where('code','like','%'.$r->code.'%'))
      ->when($r->filled('status'), fn($q)=>$q->where('status',$r->status))
      ->when($r->filled('origin_id'), fn($q)=>$q->where('origin_id',$r->origin_id))
      ->when($r->filled('destination_id'), fn($q)=>$q->where('destination_id',$r->destination_id))
      ->orderByDesc('departure_at');
    return $q->paginate(12);
  }

  // POST /admin/flights
  public function store(FlightStoreRequest $r) {
    return DB::transaction(function() use ($r) {
      $data = $r->validated();
      
      // Generar código único para el vuelo
      $code = $this->generateFlightCode($data['scope']);
      
      $flight = Flight::create([
        'code'             => $code,
        'origin_id'        => $data['origin_id'],
        'destination_id'   => $data['destination_id'],
        'scope'            => $data['scope'],
        'departure_at'     => $data['departure_at'],
        'arrival_at'       => now()->parse($data['departure_at'])->addMinutes($data['duration_minutes']), // puedes ajustar TZs luego
        'duration_minutes' => $data['duration_minutes'],
        'status'           => 'scheduled',
        'price_per_seat'   => $data['price_per_seat'],
        'capacity_first'   => $data['capacity_first'],
        'capacity_economy' => $data['capacity_economy'],
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

      // (Opcional) crear noticia automática
      \App\Models\News::create([
        'title' => "Nuevo vuelo {$flight->code}: ".$flight->origin->name.' → '.$flight->destination->name,
        'body'  => '¡Ya disponible para reservas y compras!',
        'flight_id' => $flight->id,
      ]);

      return $flight->load(['origin','destination']);
    });
  }

  // PUT /admin/flights/{flight}
  public function update(FlightUpdateRequest $r, Flight $flight) {
    // Regla: si el vuelo no está scheduled o ya tiene ventas, no permitimos cambios críticos
    $hasSales = $flight->tickets()->exists() || $flight->bookings()->where('type','purchase')->exists();
    if ($flight->status !== 'scheduled' || $hasSales) {
      // permitimos cambiar solo precio_per_seat y departure_at (siempre que sea futuro)
      $data = $r->safe()->only(['price_per_seat','departure_at']);
      if (isset($data['departure_at'])) {
        abort_if(now()->parse($data['departure_at'])->isPast(), 422, 'No puedes fijar una salida en el pasado.');
        $data['arrival_at'] = now()->parse($data['departure_at'])->addMinutes($flight->duration_minutes);
      }
      $flight->update($data);
      return $flight->fresh()->load(['origin','destination']);
    }

    // Caso editable completo
    $data = $r->validated();
    if (isset($data['departure_at'])) {
      $data['arrival_at'] = now()->parse($data['departure_at'])->addMinutes($data['duration_minutes'] ?? $flight->duration_minutes);
    }
    $flight->update($data);
    return $flight->fresh()->load(['origin','destination']);
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
