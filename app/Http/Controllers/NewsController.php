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
                ->with(['activePromotion', 'origin', 'destination'])
                ->select('id','code','origin_id','destination_id','price_per_seat','first_class_price','departure_at','duration_minutes','capacity_first','capacity_economy','scope');
        },
        'flight.origin:id,name,timezone,country',
        'flight.destination:id,name,timezone,country',
        'promotion' => function($query) {
          // Solo promociones activas (YA INICIADAS)
          $query->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
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
      // Si es promoción, solo mostrar si la promoción YA inició
      ->where(function($query) {
        $query->where('is_promotion', false)
              ->orWhereHas('promotion', function($q) {
                $q->where('is_active', true)
                  ->where('starts_at', '<=', now())
                  ->where('ends_at', '>=', now());
              });
      })
      ->when($r->filled('is_promotion'), fn($q)=>$q->where('is_promotion', (bool)$r->is_promotion))
      ->orderByDesc('created_at');
    
    $news = $q->paginate(12);
    
    // Agregar información de hora de llegada con zona horaria para cada vuelo
    $news->getCollection()->transform(function ($newsItem) {
        if ($newsItem->flight) {
            $newsItem->flight->arrival_info = $newsItem->flight->getFormattedArrivalTime();
        }
        return $newsItem;
    });
    
    return $news;
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
