<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromotionStoreRequest;
use App\Models\Flight;
use App\Models\Promotion;

class PromotionController extends Controller
{
  // POST /flights/{flight}/promotions
  public function store(PromotionStoreRequest $r, Flight $flight) {
    $promo = Promotion::create([
      'flight_id'        => $flight->id,
      'title'            => $r->title,
      'description'      => $r->description,
      'discount_percent' => $r->discount_percent,
      'starts_at'        => $r->starts_at,
      'ends_at'          => $r->ends_at,
      'is_active'        => $r->boolean('is_active', true),
    ]);

    // Crear noticia de la promoción con la imagen del vuelo
    \App\Models\News::create([
      'title'        => $promo->title,
      'body'         => $promo->description . ' - ¡' . $promo->discount_percent . '% de descuento! Vuelo ' . $flight->code . ': ' . $flight->origin->name . ' → ' . $flight->destination->name,
      'flight_id'    => $flight->id,
      'promotion_id' => $promo->id,
      'image_path'   => $flight->image_path,
      'is_promotion' => true,
    ]);

    return response()->json($promo, 201);
  }
}
