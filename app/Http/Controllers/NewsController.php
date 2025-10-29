<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsStoreRequest;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
  // GET /news (público)
  public function index(Request $r){
    $q = News::query()
      ->with([
        'flight' => function($query) {
          // Solo vuelos disponibles (scheduled y futuros)
          $query->where('status', 'scheduled')
                ->where('departure_at', '>', now())
                ->select('id','origin_id','destination_id','price_per_seat','first_class_price','departure_at','duration_minutes','capacity_first','capacity_economy');
        },
        'flight.origin:id,name',
        'flight.destination:id,name',
        'promotion' => function($query) {
          // Solo promociones activas
          $query->where('is_active', true)
                ->where(function($q) {
                  $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function($q) {
                  $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->select('id','flight_id','title','discount_percent','starts_at','ends_at','is_active');
        }
      ])
      // Filtrar solo noticias con vuelos válidos o sin vuelo asociado
      ->where(function($query) {
        $query->whereHas('flight', function($q) {
          $q->where('status', 'scheduled')
            ->where('departure_at', '>', now());
        })->orWhereNull('flight_id');
      })
      ->when($r->filled('is_promotion'), fn($q)=>$q->where('is_promotion', (bool)$r->is_promotion))
      ->orderByDesc('created_at');
    
    return $q->paginate(12);
  }

  // POST /news (admin/root)
  public function store(NewsStoreRequest $r){
    $data = $r->validated();
    $imgPath = null;
    if ($r->hasFile('image')) {
      try {
        $imgPath = $r->file('image')->store('news','public');
        if (!$imgPath) {
          throw new \Exception('No se pudo guardar la imagen.');
        }
      } catch (\Exception $e) {
        return response()->json([
          'message' => 'Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.',
          'errors' => ['image' => ['Error al cargar la imagen. Por favor, intenta de nuevo con una imagen más pequeña.']]
        ], 422);
      }
    }
    $news = News::create([
      'title' => $data['title'],
      'body'  => $data['body'] ?? null,
      'flight_id' => $data['flight_id'] ?? null,
      'is_promotion' => (bool)($data['is_promotion'] ?? false),
      'image_path' => $imgPath,
    ]);
    return response()->json($news, 201);
  }

  public function latestNews()
  {
    $news = News::latest()->take(5)->get();
    return response()->json($news);
  }
}
