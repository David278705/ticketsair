<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\City;
use App\Models\News;

use App\Http\Requests\FlightStoreRequest;
use App\Http\Requests\FlightUpdateRequest;
use Illuminate\Support\Facades\DB;

// app/Http/Controllers/FlightController.php
class FlightController extends Controller
{

  public function index(Request $r)
{
  $q = \App\Models\Flight::query()->with(['origin','destination'])
        ->where('status','scheduled');

  if ($r->filled('origin_id'))      $q->where('origin_id',$r->origin_id);
  if ($r->filled('destination_id')) $q->where('destination_id',$r->destination_id);
  if ($r->filled('date'))           $q->whereDate('departure_at',$r->date);
  if ($r->filled('class'))          $q->whereHas('seats', fn($s)=>$s->where('class',$r->class)->where('status','available'));
  if ($r->filled('max_price'))      $q->where('price_per_seat','<=',$r->max_price);

  // log de búsqueda (para recomendaciones)
  \App\Models\SearchLog::create([
    'user_id'=>optional($r->user())->id,
    'criteria'=>$r->all()
  ]);

  return $q->orderBy('departure_at')->paginate(12);
}


  public function store(FlightStoreRequest $request)
{
    $data = $request->validated();

    // Validar restricciones internacionales (origen/destino)
    // (Puedes usar un servicio o Rule personalizada; aquí simple)
    if ($data['is_international']) {
        $allowedOrigins = ['Pereira','Bogotá','Medellín','Cali','Cartagena'];
        $allowedDestins = ['Madrid','Londres','New York','Buenos Aires','Miami']; // PDF
        if (!in_array($request->origin->name, $allowedOrigins, true) ||
            !in_array($request->destination->name, $allowedDestins, true)) {
            return response()->json(['error' => 'invalid_international_route'], 422);
        }
    }

    $arrival = \Carbon\Carbon::parse($data['departure_at'])
               ->addMinutes($data['duration_minutes']); // ajustar zona si aplicara
    $capacity = $data['is_international'] ? 250 : 150; // PDF
    $firstLimit = $data['is_international'] ? 50 : 25;

    $flight = DB::transaction(function () use ($data, $arrival, $capacity, $firstLimit) {
        $code = 'FL-'.str_pad((\App\Models\Flight::max('id')+1), 6, '0', STR_PAD_LEFT);
        $f = \App\Models\Flight::create(array_merge($data, [
            'code'=>$code, 'arrival_at'=>$arrival, 'capacity'=>$capacity
        ]));

        // seats por PDF
        $seats = [];
        for ($i=1; $i <= $capacity; $i++) {
            $seats[] = [
                'flight_id'=>$f->id,
                'number'=>$i,
                'class'=> $i <= $firstLimit ? 'first' : 'economy',
                'status'=>'available',
                'created_at'=>now(),'updated_at'=>now(),
            ];
        }
        \App\Models\Seat::insert($seats);

        // noticia automática
        \App\Models\News::create([
            'title'=>"Nuevo vuelo {$f->code}",
            'body'=>"Se programó el vuelo {$f->code}",
            'flight_id'=>$f->id,
            'is_promotion'=>false,
        ]);

        return $f->load('origin','destination');
    });

    return response()->json($flight, 201);
}

  public function update(Request $r, Flight $flight)
  {
    if ($flight->status !== 'scheduled' || $flight->bookings()->exists()) {
      return response()->json(['error'=>'flight_not_editable'],422);
    }
    // Validaciones similares a store...
    // Actualiza y recalcula arrival si cambia duración/fecha.
  }

  public function destroy(Flight $flight)
  {
    // cancelar vuelo -> reembolso o reubicación (se implementa en servicio)
    $flight->update(['status'=>'cancelled']);
    return response()->json(['status'=>'cancelled']);
  }
}

