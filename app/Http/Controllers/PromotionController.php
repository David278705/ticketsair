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

    // noticia espejo
    \App\Models\News::create([
      'title'        => $promo->title,
      'body'         => $promo->description,
      'flight_id'    => $flight->id,
      'is_promotion' => true,
    ]);

    return response()->json($promo, 201);
  }
}
