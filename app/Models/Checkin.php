<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkin extends Model {
  protected $fillable=['ticket_id','checked_in_at','boarding_pass_pdf_path'];
  protected $casts=['checked_in_at'=>'datetime'];
  public function ticket(){ return $this->belongsTo(Ticket::class); }
}
