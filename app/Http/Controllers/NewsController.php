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
    $q = News::query()->with('flight:id,code,origin_id,destination_id')
      ->when($r->filled('is_promotion'), fn($q)=>$q->where('is_promotion', (bool)$r->is_promotion))
      ->orderByDesc('created_at');
    return $q->paginate(12);
  }

  // POST /news (admin/root)
  public function store(NewsStoreRequest $r){
    $data = $r->validated();
    $imgPath = null;
    if ($r->hasFile('image')) {
      $imgPath = $r->file('image')->store('news','public');
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
}
