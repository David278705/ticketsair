<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Role;
use App\Models\Booking;
use App\Models\Card;
use App\Models\Message;

class User extends Authenticatable {
  use HasApiTokens, Notifiable, HasFactory;

  protected $fillable = [
    'role_id','name','email','password','dni','first_name','last_name','birth_date',
    'birth_place','billing_address','gender','username','avatar_path','news_opt_in','wallet_balance',
    'temp_password_token','temp_password_expires_at','registration_completed',
    'country_code','country_name','state_code','state_name','city_id','city_name'
  ];

  protected $hidden = ['password','remember_token'];

  protected $casts = [
    'birth_date'       => 'date',
    'news_opt_in'      => 'bool',
    'birth_place'    => 'json',
    'email_verified_at'=> 'datetime',
    'wallet_balance'   => 'decimal:2',
    'temp_password_expires_at' => 'datetime',
    'registration_completed' => 'boolean',
  ];

  // Relaciones (ya ok)
  public function role(){ return $this->belongsTo(Role::class); }
  public function bookings(){ return $this->hasMany(Booking::class); }
  public function cards(){ return $this->hasMany(Card::class); }
  public function messagesSent(){ return $this->hasMany(Message::class,'from_user_id'); }
  public function messagesReceived(){ return $this->hasMany(Message::class,'to_user_id'); }

  // Accesor opcional para nombre completo (útil en front)
  public function getFullNameAttribute(){ return trim(($this->first_name ?? '').' '.($this->last_name ?? '')); }
}
