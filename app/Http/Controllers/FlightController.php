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
  
  // Filtro de clase (opcional - si no se especifica, muestra todas las clases)
  if ($r->filled('class')) {
    $q->whereHas('seats', fn($s) => $s->where('class', $r->class)->where('status', 'available'));
  }
  
  // Filtros de precio (considerando promociones y ambas clases)
  if ($r->has('min_price') && $r->min_price !== null && $r->min_price !== '') {
    $minPrice = floatval($r->min_price);
    
    // Filtrar en PHP después de cargar los vuelos con promociones
    // Para evitar consultas SQL complejas
    $q->where(function($query) use ($minPrice, $r) {
      // Filtro básico para reducir resultados iniciales
      if ($r->filled('class') && $r->class === 'economy') {
        $query->where('price_per_seat', '>=', $minPrice * 0.1); // Margen para promociones
      } else if ($r->filled('class') && $r->class === 'first') {
        $query->where('first_class_price', '>=', $minPrice * 0.1);
      } else {
        // Sin clase: al menos uno de los precios debe estar cerca
        $query->where(function($q) use ($minPrice) {
          $q->where('price_per_seat', '>=', $minPrice * 0.1)
            ->orWhere('first_class_price', '>=', $minPrice * 0.1);
        });
      }
    });
  }
  
  if ($r->has('max_price') && $r->max_price !== null && $r->max_price !== '') {
    $maxPrice = floatval($r->max_price);
    
    $q->where(function($query) use ($maxPrice, $r) {
      if ($r->filled('class') && $r->class === 'economy') {
        $query->where('price_per_seat', '<=', $maxPrice * 2); // Margen para filtrar después
      } else if ($r->filled('class') && $r->class === 'first') {
        $query->where('first_class_price', '<=', $maxPrice * 2);
      } else {
        $query->where(function($q) use ($maxPrice) {
          $q->where('price_per_seat', '<=', $maxPrice * 2)
            ->orWhere('first_class_price', '<=', $maxPrice * 2);
        });
      }
    });
  }

  // log de búsqueda (para recomendaciones)
  \App\Models\SearchLog::create([
    'user_id'=>optional($r->user())->id,
    'criteria'=>$r->all()
  ]);

  $flights = $q->orderBy('departure_at')->paginate(12);
  
  // Filtrar por precio exacto considerando promociones
  if (($r->has('min_price') && $r->min_price !== null && $r->min_price !== '') || 
      ($r->has('max_price') && $r->max_price !== null && $r->max_price !== '')) {
    
    $minPrice = $r->has('min_price') && $r->min_price !== null && $r->min_price !== '' ? floatval($r->min_price) : 0;
    $maxPrice = $r->has('max_price') && $r->max_price !== null && $r->max_price !== '' ? floatval($r->max_price) : PHP_FLOAT_MAX;
    $filterClass = $r->filled('class') ? $r->class : null;
    
    $flights->getCollection()->transform(function ($flight) use ($minPrice, $maxPrice, $filterClass) {
      // Calcular precios reales con promoción
      $economyPrice = floatval($flight->price_per_seat);
      $firstPrice = floatval($flight->first_class_price);
      
      if ($flight->activePromotion) {
        $discount = floatval($flight->activePromotion->discount_percent) / 100;
        $economyPrice = $economyPrice * (1 - $discount);
        $firstPrice = $firstPrice * (1 - $discount);
      }
      
      // Verificar si cumple con el rango de precio
      $matchesPrice = false;
      
      if ($filterClass === 'economy') {
        $matchesPrice = $economyPrice >= $minPrice && $economyPrice <= $maxPrice;
      } else if ($filterClass === 'first') {
        $matchesPrice = $firstPrice >= $minPrice && $firstPrice <= $maxPrice;
      } else {
        // Sin clase específica: al menos una clase debe cumplir
        $matchesPrice = ($economyPrice >= $minPrice && $economyPrice <= $maxPrice) ||
                       ($firstPrice >= $minPrice && $firstPrice <= $maxPrice);
      }
      
      $flight->_matchesPrice = $matchesPrice;
      return $flight;
    });
    
    // Filtrar vuelos que no cumplen
    $filtered = $flights->getCollection()->filter(fn($f) => $f->_matchesPrice);
    $flights->setCollection($filtered->values());
  }
  
  // Agregar información de hora de llegada con zona horaria
  $flights->getCollection()->transform(function ($flight) {
      $flight->arrival_info = $flight->getFormattedArrivalTime();
      unset($flight->_matchesPrice); // Limpiar propiedad temporal
      return $flight;
  });

  return $flights;
}

/**
 * Obtener un vuelo específico con sus relaciones
 */
public function show(Flight $flight)
{
    // Cargar relaciones necesarias
    $flight->load([
        'origin',
        'destination',
        'aircraft',
        'activePromotion'
    ]);
    
    // Agregar información de hora de llegada
    $flight->arrival_info = $flight->getFormattedArrivalTime();
    
    return response()->json($flight);
}

}
