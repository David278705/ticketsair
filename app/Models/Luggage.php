<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Luggage extends Model {
  protected $fillable=['ticket_id','type','pieces','extra_fee'];
  public function ticket(){ return $this->belongsTo(Ticket::class); }
}