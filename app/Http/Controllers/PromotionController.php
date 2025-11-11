<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromotionStoreRequest;
use App\Models\Flight;
use App\Models\Promotion;
use App\Models\News;

class PromotionController extends Controller
{
  // POST /flights/{flight}/promotions
  public function store(PromotionStoreRequest $r, Flight $flight) {
    // Verificar si ya existe una promoción válida (activa o futura) para este vuelo
    $existingPromo = $flight->promotions()
        ->where('is_active', true)
        ->where('ends_at', '>=', now())
        ->first();
    
    if ($existingPromo) {
      // Actualizar la promoción existente en lugar de crear una nueva
      $existingPromo->update([
        'title'            => $r->title,
        'description'      => $r->description,
        'discount_percent' => $r->discount_percent,
        'starts_at'        => $r->starts_at,
        'ends_at'          => $r->ends_at,
        'is_active'        => $r->boolean('is_active', true),
      ]);
      
      // Actualizar o eliminar la noticia asociada según el estado
      $news = News::where('promotion_id', $existingPromo->id)->first();
      
      if ($news) {
        if ($r->boolean('is_active', true)) {
          // Actualizar la noticia si la promoción está activa
          $news->update([
            'title' => $existingPromo->title,
            'body'  => $existingPromo->description . ' - ¡' . $existingPromo->discount_percent . '% de descuento! Vuelo ' . $flight->code . ': ' . $flight->origin->name . ' → ' . $flight->destination->name,
            'image_path' => $flight->image_path,
          ]);
        } else {
          // Eliminar la noticia si la promoción se desactiva
          $news->delete();
        }
      } elseif ($r->boolean('is_active', true)) {
        // Crear noticia si no existe y la promoción está activa
        News::create([
          'title'        => $existingPromo->title,
          'body'         => $existingPromo->description . ' - ¡' . $existingPromo->discount_percent . '% de descuento! Vuelo ' . $flight->code . ': ' . $flight->origin->name . ' → ' . $flight->destination->name,
          'flight_id'    => $flight->id,
          'promotion_id' => $existingPromo->id,
          'image_path'   => $flight->image_path,
          'is_promotion' => true,
        ]);
      }
      
      return response()->json([
        'message' => 'Promoción actualizada exitosamente',
        'promotion' => $existingPromo->fresh(),
        'updated' => true
      ], 200);
    }
    
    // Crear nueva promoción solo si no existe una activa
    $promo = Promotion::create([
      'flight_id'        => $flight->id,
      'title'            => $r->title,
      'description'      => $r->description,
      'discount_percent' => $r->discount_percent,
      'starts_at'        => $r->starts_at,
      'ends_at'          => $r->ends_at,
      'is_active'        => $r->boolean('is_active', true),
    ]);

    // Crear noticia de la promoción SOLO si está activa
    if ($r->boolean('is_active', true)) {
      News::create([
        'title'        => $promo->title,
        'body'         => $promo->description . ' - ¡' . $promo->discount_percent . '% de descuento! Vuelo ' . $flight->code . ': ' . $flight->origin->name . ' → ' . $flight->destination->name,
        'flight_id'    => $flight->id,
        'promotion_id' => $promo->id,
        'image_path'   => $flight->image_path,
        'is_promotion' => true,
      ]);
    }

    return response()->json([
      'message' => 'Promoción creada exitosamente',
      'promotion' => $promo,
      'updated' => false
    ], 201);
  }
  
  // GET /flights/{flight}/promotions - Obtener promociones del vuelo
  public function index(Flight $flight) {
    $promotions = $flight->promotions()
        ->orderByDesc('created_at')
        ->get();
    
    // Promoción válida (incluyendo futuras, SOLO si está activa)
    $validPromo = $flight->promotions()
        ->where('is_active', true)
        ->where('ends_at', '>=', now())
        ->first();
    
    // Promoción activa (solo si ya empezó y está activa)
    $activePromo = $flight->promotions()
        ->where('is_active', true)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>=', now())
        ->first();
    
    return response()->json([
      'promotions' => $promotions,
      'valid_promotion' => $validPromo,
      'active_promotion' => $activePromo,
      'has_valid' => (bool) $validPromo,
      'has_active' => (bool) $activePromo
    ]);
  }
  
  // DELETE /promotions/{promotion} - Eliminar promoción
  public function destroy(Promotion $promotion) {
    // Eliminar la noticia asociada
    News::where('promotion_id', $promotion->id)->delete();
    
    // Eliminar la promoción
    $promotion->delete();
    
    return response()->json([
      'message' => 'Promoción eliminada exitosamente'
    ]);
  }
}
