<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

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
      'name'           => $data['first_name'] . ' ' . $data['last_name'],
      // Campos de ubicación
      'country_code'   => $data['country'] ?? null,
      'country_name'   => $data['country_name'] ?? null,
      'state_code'     => $data['state'] ?? null,
      'state_name'     => $data['state_name'] ?? null,
      'city_id'        => $data['city'] ?? null,
      'city_name'      => $data['city_name'] ?? null,
    ]);

    // Si quieres forzar verificación de email (opcional):
    // $user->sendEmailVerificationNotification();

    return response()->json($user->only(['id','email','first_name','last_name']), 201);
  }

  public function login(Request $r){
    $r->validate(['email'=>'required|email','password'=>'required']);
    if(!Auth::attempt($r->only('email','password')))
      return response()->json(['error'=>'invalid_credentials'],422);
    
    $user = $r->user()->load('role');
    
    // Verificar si es un admin con registro incompleto
    if ($user->role->name === 'admin' && !$user->registration_completed) {
      $token = $user->createToken('web')->plainTextToken;
      return response()->json([
        'token' => $token,
        'user' => $user,
        'requires_completion' => true
      ]);
    }
    
    $token = $user->createToken('web')->plainTextToken;
    return response()->json(['token'=>$token,'user'=>$user]);
  }

  public function me(Request $r){ 
    return $r->user()->load('role'); 
  }

  public function logout(Request $r){
    $r->user()->currentAccessToken()->delete();
    return ['ok'=>true];
  }

  public function forgotPassword(Request $request)
  {
    $request->validate([
      'email' => 'required|email|exists:users,email'
    ], [
      'email.required' => 'El email es requerido.',
      'email.email' => 'Ingresa un email válido.',
      'email.exists' => 'No encontramos una cuenta con este email.'
    ]);

    // Generar token para recuperación
    $token = Str::random(64);
    
    // Buscar usuario
    $user = User::with('role')->where('email', $request->email)->first();
    
    // Guardar token en la tabla password_reset_tokens
    DB::table('password_reset_tokens')->updateOrInsert(
      ['email' => $request->email],
      [
        'token' => Hash::make($token),
        'created_at' => now()
      ]
    );

    // Enviar email de recuperación
    try {
      Mail::to($user->email)->send(new PasswordResetMail($user, $token));
      
      return response()->json([
        'status' => 'success',
        'message' => 'Enlace de recuperación enviado a tu email.',
        'user_info' => [
          'id' => $user->id,
          'email' => $user->email,
          'name' => $user->first_name . ' ' . $user->last_name,
          'role' => $user->role->name ?? 'client'
        ]
      ]);
    } catch (\Exception $e) {
      // Si falla el envío de email, eliminar el token
      DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();
        
      return response()->json([
        'status' => 'error',
        'message' => 'Error al enviar el email. Por favor, inténtalo de nuevo.'
      ], 500);
    }
  }

  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email|exists:users,email',
      'password' => 'required|min:8|confirmed',
    ], [
      'email.exists' => 'No encontramos una cuenta con este email.',
      'password.confirmed' => 'Las contraseñas no coinciden.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.'
    ]);

    // Verificar token
    $record = DB::table('password_reset_tokens')
      ->where('email', $request->email)
      ->first();

    if (!$record || !Hash::check($request->token, $record->token)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Token inválido o expirado.'
      ], 422);
    }

    // Verificar si el token no ha expirado (24 horas)
    if (now()->diffInHours($record->created_at) > 24) {
      return response()->json([
        'status' => 'error',
        'message' => 'El token ha expirado. Solicita uno nuevo.'
      ], 422);
    }

    // Actualizar contraseña
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // Eliminar el token usado
    DB::table('password_reset_tokens')
      ->where('email', $request->email)
      ->delete();

    // Revocar todos los tokens existentes del usuario
    $user->tokens()->delete();

    return response()->json([
      'status' => 'success',
      'message' => 'Contraseña restablecida correctamente. Por favor, inicia sesión.'
    ]);
  }

  public function checkResetToken(Request $request)
  {
    $request->validate([
      'token' => 'required|string',
      'email' => 'required|email'
    ]);

    // Verificar si existe un registro con este email
    $record = DB::table('password_reset_tokens')
      ->where('email', $request->email)
      ->first();

    if (!$record || !Hash::check($request->token, $record->token)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Token inválido o expirado.'
      ], 422);
    }

    // Verificar si el token no ha expirado (24 horas)
    if (now()->diffInHours($record->created_at) > 24) {
      return response()->json([
        'status' => 'error',
        'message' => 'El token ha expirado. Solicita uno nuevo.'
      ], 422);
    }

    // Retornar información básica del usuario
    $user = User::with('role')->where('email', $request->email)->first();
    
    return response()->json([
      'status' => 'success',
      'user' => [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role->name ?? 'client'
      ]
    ]);
  }
}
