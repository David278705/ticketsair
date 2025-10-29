<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'new_password' => ['required', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            
            // Campos de ubicación
            'location.country' => 'required|string|max:3',
            'location.country_name' => 'required|string|max:100',
            'location.state' => 'nullable|string|max:10',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string|max:20',
            'location.city_name' => 'nullable|string|max:100',
        ], [
            // Token y Email
            'token.required' => 'El token es requerido.',
            'token.string' => 'El token debe ser texto.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            
            // DNI
            'dni.required' => 'El documento de identidad es requerido.',
            'dni.string' => 'El documento de identidad debe ser texto.',
            'dni.unique' => 'Este documento de identidad ya está registrado.',
            
            // Birth Date
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años para completar el registro.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años para completar el registro.',
            
            // Gender
            'gender.in' => 'El género seleccionado no es válido.',
            
            // Billing Address
            'billing_address.string' => 'La dirección de facturación debe ser texto.',
            'billing_address.max' => 'La dirección de facturación no puede tener más de 500 caracteres.',
            
            // Username
            'username.string' => 'El nombre de usuario debe ser texto.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            
            // Password
            'new_password.required' => 'La contraseña es requerida.',
            'new_password.confirmed' => 'Las contraseñas no coinciden.',
            'new_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'new_password.regex' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula y un número.',
            
            // Location
            'location.country.required' => 'El país es requerido.',
            'location.country.string' => 'El país debe ser texto.',
            'location.country.max' => 'El código de país no puede tener más de 3 caracteres.',
            'location.country_name.required' => 'El nombre del país es requerido.',
            'location.country_name.string' => 'El nombre del país debe ser texto.',
            'location.country_name.max' => 'El nombre del país no puede tener más de 100 caracteres.',
            'location.state.string' => 'El estado debe ser texto.',
            'location.state.max' => 'El código de estado no puede tener más de 10 caracteres.',
            'location.state_name.string' => 'El nombre del estado debe ser texto.',
            'location.state_name.max' => 'El nombre del estado no puede tener más de 100 caracteres.',
            'location.city.string' => 'La ciudad debe ser texto.',
            'location.city.max' => 'El código de ciudad no puede tener más de 20 caracteres.',
            'location.city_name.string' => 'El nombre de la ciudad debe ser texto.',
            'location.city_name.max' => 'El nombre de la ciudad no puede tener más de 100 caracteres.',
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
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            
            // Campos de ubicación
            'location.country' => 'required|string|max:3',
            'location.country_name' => 'required|string|max:100',
            'location.state' => 'nullable|string|max:10',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string|max:20',
            'location.city_name' => 'nullable|string|max:100',
        ], [
            // First Name
            'first_name.required' => 'El nombre es requerido.',
            'first_name.string' => 'El nombre debe ser texto.',
            'first_name.max' => 'El nombre no puede tener más de 255 caracteres.',
            
            // Last Name
            'last_name.required' => 'El apellido es requerido.',
            'last_name.string' => 'El apellido debe ser texto.',
            'last_name.max' => 'El apellido no puede tener más de 255 caracteres.',
            
            // DNI
            'dni.required' => 'El documento de identidad es requerido.',
            'dni.string' => 'El documento de identidad debe ser texto.',
            'dni.unique' => 'Este documento de identidad ya está registrado.',
            
            // Birth Date
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años para completar el registro.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años para completar el registro.',
            
            // Gender
            'gender.in' => 'El género seleccionado no es válido.',
            
            // Username
            'username.string' => 'El nombre de usuario debe ser texto.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            
            // Password
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula y un número.',
            
            // Location
            'location.country.required' => 'El país es requerido.',
            'location.country.string' => 'El país debe ser texto.',
            'location.country.max' => 'El código de país no puede tener más de 3 caracteres.',
            'location.country_name.required' => 'El nombre del país es requerido.',
            'location.country_name.string' => 'El nombre del país debe ser texto.',
            'location.country_name.max' => 'El nombre del país no puede tener más de 100 caracteres.',
            'location.state.string' => 'El estado debe ser texto.',
            'location.state.max' => 'El código de estado no puede tener más de 10 caracteres.',
            'location.state_name.string' => 'El nombre del estado debe ser texto.',
            'location.state_name.max' => 'El nombre del estado no puede tener más de 100 caracteres.',
            'location.city.string' => 'La ciudad debe ser texto.',
            'location.city.max' => 'El código de ciudad no puede tener más de 20 caracteres.',
            'location.city_name.string' => 'El nombre de la ciudad debe ser texto.',
            'location.city_name.max' => 'El nombre de la ciudad no puede tener más de 100 caracteres.',
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
