<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Requests\ProfilePhotoRequest;

class ProfileController extends Controller
{
    /**
     * Actualizar información personal del perfil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Debug: Log datos recibidos
        \Log::info('Profile update data:', $request->all());

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
            'location.state' => 'nullable|string|max:10',
            'location.state_name' => 'nullable|string|max:100',
            'location.city' => 'nullable|string',
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

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil actualizado exitosamente.',
            'data' => $user->load('role')->makeHidden(['password'])->append('profile_photo_url')
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
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'La contraseña actual es requerida.',
            'new_password.required' => 'La nueva contraseña es requerida.',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
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
     * Subir o actualizar foto de perfil
     */
    public function uploadProfilePhoto(ProfilePhotoRequest $request)
    {
        $user = $request->user();

        try {
            $photo = $request->file('profile_photo');
            
            // Eliminar foto anterior si existe
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Generar nombre único para el archivo
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $path = 'profile-photos/' . $filename;

            // Guardar la imagen original
            $photo->storeAs('profile-photos', $filename, 'public');

            // Opcional: redimensionar la imagen si es muy grande
            $fullPath = storage_path('app/public/' . $path);
            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);
            
            // Redimensionar manteniendo aspecto si es mayor a 500px
            if ($image->width() > 500 || $image->height() > 500) {
                $image->scale(width: 500, height: 500);
                $image->save($fullPath);
            }

            // Actualizar el usuario con la nueva ruta
            $user->update(['avatar_path' => $path]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto de perfil actualizada exitosamente.',
                'data' => [
                    'avatar_path' => $user->avatar_path,
                    'profile_photo_url' => $user->profile_photo_url
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading profile photo: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al subir la foto de perfil. Inténtalo de nuevo.'
            ], 500);
        }
    }

    /**
     * Eliminar foto de perfil
     */
    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();

        if (!$user->avatar_path) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay foto de perfil para eliminar.'
            ], 404);
        }

        try {
            // Eliminar archivo del storage
            Storage::disk('public')->delete($user->avatar_path);

            // Limpiar el campo en la base de datos
            $user->update(['avatar_path' => null]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto de perfil eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting profile photo: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la foto de perfil. Inténtalo de nuevo.'
            ], 500);
        }
    }
}