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
  // Usar el scope available para solo mostrar vuelos programados y no finalizados
  $q = \App\Models\Flight::query()
        ->with(['origin','destination','activePromotion'])
        ->available(); // Solo vuelos disponibles (no completados ni pasados)

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

  $flights = $q->orderBy('departure_at')->paginate(12);
  
  // Agregar información de hora de llegada con zona horaria
  $flights->getCollection()->transform(function ($flight) {
      $flight->arrival_info = $flight->getFormattedArrivalTime();
      return $flight;
  });

  return $flights;
}

}

