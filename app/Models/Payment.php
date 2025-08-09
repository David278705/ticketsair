<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
  protected $fillable=['payable_id','payable_type','user_id','card_id','status','amount','meta'];
  protected $casts=['meta'=>'array'];
  public function payable(){ return $this->morphTo(); }
  public function user(){ return $this->belongsTo(User::class); }
  public function card(){ return $this->belongsTo(Card::class); }
  public function refund(){ return $this->hasOne(Refund::class); }
}