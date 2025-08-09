<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model {
  protected $fillable=['cart_id','flight_id','class','qty','price'];
  public function cart(){ return $this->belongsTo(Cart::class); }
  public function flight(){ return $this->belongsTo(Flight::class); }
}