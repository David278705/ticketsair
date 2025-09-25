<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminRegistrationController extends Controller
{
    /**
     * Verificar token de registro temporal
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $admin = User::where('email', $request->email)
            ->where('temp_password_token', $request->token)
            ->where('temp_password_expires_at', '>', now())
            ->where('registration_completed', false)
            ->first();

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido o expirado'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token válido',
            'data' => [
                'email' => $admin->email,
                'name' => $admin->name,
                'token_expires_at' => $admin->temp_password_expires_at,
            ]
        ]);
    }

    /**
     * Completar registro de administrador
     */
    public function completeRegistration(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'dni' => 'required|string|unique:users,dni',
            'birth_date' => 'required|date|before:today|after_or_equal:' . now()->subYears(80)->format('Y-m-d') . '|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'nullable|in:M,F,X',
            'billing_address' => 'nullable|string|max:500',
            'username' => 'nullable|string|unique:users,username',
            'new_password' => ['required', 'confirmed', Password::min(8)],
            
            // Campos de ubicación
            'location.country' => 'required|string|max:3',
            'location.country_name' => 'required|string|max:100',
            'location.state' => 'nullable|string|max:10',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string|max:20',
            'location.city_name' => 'nullable|string|max:100',
        ], [
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años para completar el registro.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años para completar el registro.',
        ]);

        $admin = User::where('email', $request->email)
            ->where('temp_password_token', $request->token)
            ->where('temp_password_expires_at', '>', now())
            ->where('registration_completed', false)
            ->first();

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido o expirado'
            ], 400);
        }

        // Completar el registro
        $admin->update([
            'dni' => $request->dni,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'billing_address' => $request->billing_address,
            'username' => $request->username,
            'password' => Hash::make($request->new_password),
            'temp_password_token' => null,
            'temp_password_expires_at' => null,
            'registration_completed' => true,
            
            // Campos de ubicación
            'country_code' => $request->input('location.country'),
            'country_name' => $request->input('location.country_name'),
            'state_code' => $request->input('location.state'),
            'state_name' => $request->input('location.state_name'),
            'city_id' => $request->input('location.city'),
            'city_name' => $request->input('location.city_name'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro completado exitosamente. Ya puedes iniciar sesión con tus credenciales.',
            'data' => $admin->load('role')
        ]);
    }

    /**
     * Completar registro para admin ya autenticado
     */
    public function completeAuthenticatedRegistration(Request $request)
    {
        $user = $request->user();

        // Verificar que es un admin con registro incompleto
        if ($user->role->name !== 'admin' || $user->registration_completed) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users,dni,' . $user->id,
            'birth_date' => 'required|date|before:today|after_or_equal:' . now()->subYears(80)->format('Y-m-d') . '|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'nullable|in:M,F,X',
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'password' => ['required', 'confirmed', Password::min(8)],
            
            // Campos de ubicación
            'location.country' => 'required|string|max:3',
            'location.country_name' => 'required|string|max:100',
            'location.state' => 'nullable|string|max:10',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string|max:20',
            'location.city_name' => 'nullable|string|max:100',
        ], [
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años para completar el registro.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años para completar el registro.',
        ]);

        // Actualizar datos del usuario
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'dni' => $request->dni,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'temp_password_token' => null,
            'temp_password_expires_at' => null,
            'registration_completed' => true,
            
            // Campos de ubicación
            'country_code' => $request->input('location.country'),
            'country_name' => $request->input('location.country_name'),
            'state_code' => $request->input('location.state'),
            'state_name' => $request->input('location.state_name'),
            'city_id' => $request->input('location.city'),
            'city_name' => $request->input('location.city_name'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro completado exitosamente.',
            'data' => $user->load('role')
        ]);
    }
}
