<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
  public function register(RegisterRequest $r){
    $data = $r->validated();

    $clientRoleId = Role::where('name','client')->value('id');
    $user = User::create([
      'role_id'        => $clientRoleId,
      'email'          => $data['email'],
      'password'       => Hash::make($data['password']),
      'first_name'     => $data['first_name'],
      'last_name'      => $data['last_name'],
      'dni'            => $data['dni'],
      'birth_date'     => $data['birth_date'],
      'gender'         => $data['gender'] ?? null,
      'username'       => $data['username'] ?? null,
      'billing_address'=> $data['billing_address'] ?? null,
      'news_opt_in'    => (bool)($data['news_opt_in'] ?? false),
      'wallet_balance' => 0,
       'name'            => $data['first_name'] . ' ' . $data['last_name'],
    ]);

    // Si quieres forzar verificación de email (opcional):
    // $user->sendEmailVerificationNotification();

    return response()->json($user->only(['id','email','first_name','last_name']), 201);
  }

  public function login(Request $r){
    $r->validate(['email'=>'required|email','password'=>'required']);
    if(!Auth::attempt($r->only('email','password')))
      return response()->json(['error'=>'invalid_credentials'],422);
    $token = $r->user()->createToken('web')->plainTextToken;
    return response()->json(['token'=>$token,'user'=>$r->user()]);
  }

  public function me(Request $r){ return $r->user()->load('role'); }

  public function logout(Request $r){
    $r->user()->currentAccessToken()->delete();
    return ['ok'=>true];
  }
}
