<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model {
  protected $fillable=['user_id','criteria'];
  protected $casts=['criteria'=>'array'];
  public function user(){ return $this->belongsTo(User::class); }
}
