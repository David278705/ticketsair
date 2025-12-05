<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Actualizar información personal del perfil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();


        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users,dni,' . $user->id,
            'birth_date' => 'required|date|before:today|after_or_equal:' . now()->subYears(80)->format('Y-m-d') . '|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'nullable|in:M,F,X',
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'billing_address' => 'nullable|string|max:500',
            'news_opt_in' => 'boolean',
            
            // Campos de ubicación
            'location.country' => 'required|string|max:3',
            'location.country_name' => 'required|string|max:100',
            'location.state' => 'nullable|string|max:50',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string|max:100',
            'location.city_name' => 'nullable|string|max:100',
        ], [
            'first_name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'dni.required' => 'El documento de identidad es requerido.',
            'dni.unique' => 'Este documento ya está en uso por otro usuario.',
            'birth_date.required' => 'La fecha de nacimiento es requerida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after_or_equal' => 'Debes tener máximo 80 años.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 años.',
            'gender.in' => 'El género seleccionado no es válido.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            
            // Mensajes de ubicación
            'location.country.required' => 'El país es requerido.',
            'location.country_name.required' => 'El nombre del país es requerido.',
        ]);

        // Actualizar datos del usuario (excepto email que no se puede cambiar)
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'dni' => $request->dni,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'username' => $request->username,
            'billing_address' => $request->billing_address,
            'news_opt_in' => $request->boolean('news_opt_in'),
            
            // Campos de ubicación
            'country_code' => $request->input('location.country'),
            'country_name' => $request->input('location.country_name'),
            'state_code' => $request->input('location.state'),
            'state_name' => $request->input('location.state_name'),
            'city_id' => $request->input('location.city'),
            'city_name' => $request->input('location.city_name'),
        ]);

        // Debug: verificar que se guardó
        $user->refresh();


        return response()->json([
            'status' => 'success',
            'message' => 'Perfil actualizado exitosamente.',
            'data' => $user->fresh()->load('role')
        ]);
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ], [
            'current_password.required' => 'La contraseña actual es requerida.',
            'new_password.required' => 'La nueva contraseña es requerida.',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.regex' => 'La nueva contraseña debe contener al menos una letra minúscula, una mayúscula y un número.',
        ]);

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La contraseña actual es incorrecta.',
                'errors' => ['current_password' => ['La contraseña actual es incorrecta.']]
            ], 422);
        }

        // Verificar que la nueva contraseña no sea igual a la actual
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La nueva contraseña debe ser diferente a la actual.',
                'errors' => ['new_password' => ['La nueva contraseña debe ser diferente a la actual.']]
            ], 422);
        }

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Opcional: Revocar todos los tokens existentes excepto el actual
        // $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada exitosamente.'
        ]);
    }

    /**
     * Subir foto de perfil del usuario
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048', // Max 2MB
        ], [
            'avatar.required' => 'Debes seleccionar una imagen.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png o gif.',
            'avatar.max' => 'La imagen no debe superar los 2MB.',
        ]);

        try {
            // Eliminar foto anterior si existe
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Guardar nueva foto
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $user->id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');

            // Actualizar ruta en base de datos
            $user->update([
                'avatar_path' => $path
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto de perfil actualizada exitosamente.',
                'data' => [
                    'avatar_url' => '/storage/' . $path
                ]
            ]);
        } catch (\Exception $e) {
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al subir la foto de perfil. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }

    /**
     * Eliminar foto de perfil del usuario
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        try {
            // Eliminar foto si existe
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Actualizar base de datos
            $user->update([
                'avatar_path' => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto de perfil eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {

            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la foto de perfil. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }
}