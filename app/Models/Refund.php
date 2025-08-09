<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model {
  protected $fillable=['payment_id','amount','refunded_at','meta'];
  protected $casts=['refunded_at'=>'datetime','meta'=>'array'];
  public function payment(){ return $this->belongsTo(Payment::class); }
}