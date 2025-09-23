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
            'birth_date' => 'required|date|before:today',
            'gender' => 'nullable|in:M,F,X',
            'billing_address' => 'nullable|string|max:500',
            'username' => 'nullable|string|unique:users,username',
            'new_password' => ['required', 'confirmed', Password::min(8)],
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
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro completado exitosamente. Ya puedes iniciar sesión con tus credenciales.',
            'data' => $admin->load('role')
        ]);
    }
}
