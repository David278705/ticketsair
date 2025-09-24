<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAdminRequest;
use App\Http\Requests\UpdateCredentialsRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\Role;
use App\Mail\AdminInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserAdminController extends Controller
{
    /**
     * Display a listing of all users (only for root)
     */
    public function index(Request $request)
    {
        // Solo el root puede ver todos los usuarios
        if (!$request->user() || $request->user()->role->name !== 'root') {
            return response()->json(['error' => 'Unauthorized. Root access required.'], 403);
        }

        $users = User::with('role')
            ->when($request->role, function ($query, $role) {
                return $query->whereHas('role', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('dni', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, User $user)
    {
        // Solo el root puede ver detalles de usuarios
        if (!$request->user() || $request->user()->role->name !== 'root') {
            return response()->json(['error' => 'Unauthorized. Root access required.'], 403);
        }

        $user->load(['role', 'bookings.flight', 'cards', 'messagesSent', 'messagesReceived']);
        
        return response()->json($user);
    }

    /**
     * Create a new administrator (only for root)
     */
    public function createAdmin(CreateAdminRequest $request)
    {
        try {
            DB::beginTransaction();

            $adminRole = Role::where('name', 'admin')->first();
            if (!$adminRole) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rol de administrador no encontrado'
                ], 500);
            }

            // Generar contraseña temporal y token
            $temporaryPassword = Str::random(12);
            $token = Str::random(60);
            
            // Separar el nombre completo
            $nameParts = explode(' ', trim($request->full_name), 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            $admin = User::create([
                'email' => $request->email,
                'password' => Hash::make($temporaryPassword),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $request->full_name,
                'role_id' => $adminRole->id,
                'temp_password_token' => $token,
                'temp_password_expires_at' => now()->addHours(24),
                'registration_completed' => false,
                'email_verified_at' => now(),
                'news_opt_in' => false,
                'wallet_balance' => 0,
            ]);

            // Enviar email con credenciales temporales
            Mail::to($admin->email)->send(new AdminInvitationMail($admin, $temporaryPassword));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Invitación de administrador enviada exitosamente. El administrador recibirá un correo con las instrucciones para completar su registro.',
                'data' => $admin->load('role')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear administrador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user credentials (restricted)
     */
    public function updateCredentials(UpdateCredentialsRequest $request, $id)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Funcionalidad no disponible para usuario root'
        ], 403);
    }

    /**
     * Reset user password (restricted)
     */
    public function resetPassword(ResetPasswordRequest $request, $id)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Funcionalidad no disponible para usuario root'
        ], 403);
    }

    /**
     * Activate/Deactivate user account (only for root)
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // No permitir desactivar usuarios root
            if ($user->role->name === 'root') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede desactivar un usuario root'
                ], 403);
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            $status = $user->is_active ? 'activado' : 'desactivado';

            return response()->json([
                'status' => 'success',
                'message' => "Usuario {$status} exitosamente",
                'data' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar estado del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system roles for admin creation
     */
    public function getRoles()
    {
        try {
            $roles = Role::all();

            return response()->json([
                'status' => 'success',
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update root's own password (only root can change their own password)
     */
    public function updateOwnPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $request->user();

            // Verificar que realmente es root
            if ($user->role->name !== 'root') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solo el usuario root puede usar esta funcionalidad'
                ], 403);
            }

            // Verificar la contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar contraseña: ' . $e->getMessage()
            ], 500);
        }
    }
}
