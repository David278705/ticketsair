<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model {
  protected $fillable=['user_id','brand','holder_name','last4','exp_month','exp_year','token'];
  public function user(){ return $this->belongsTo(User::class); }
}