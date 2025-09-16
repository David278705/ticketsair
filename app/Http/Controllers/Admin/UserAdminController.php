<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAdminRequest;
use App\Http\Requests\UpdateCredentialsRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

            $admin = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'username' => $request->username,
                'billing_address' => $request->billing_address,
                'role_id' => $adminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'name' => $request->first_name . ' ' . $request->last_name,
                'news_opt_in' => false,
                'wallet_balance' => 0
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Administrador creado exitosamente',
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
     * Update user credentials (only for root)
     */
    public function updateCredentials(UpdateCredentialsRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // No permitir editar usuarios root si no es root
            if ($user->role->name === 'root' && $request->user()->role->name !== 'root') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permisos para editar este usuario'
                ], 403);
            }

            $updateData = $request->validated();

            // Actualizar el nombre completo si se actualizan nombres
            if (isset($updateData['first_name']) || isset($updateData['last_name'])) {
                $firstName = $updateData['first_name'] ?? $user->first_name;
                $lastName = $updateData['last_name'] ?? $user->last_name;
                $updateData['name'] = $firstName . ' ' . $lastName;
            }

            $user->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Credenciales actualizadas exitosamente',
                'data' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar credenciales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password (only for root)
     */
    public function resetPassword(ResetPasswordRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // No permitir cambiar contraseña a usuarios root si no es root
            if ($user->role->name === 'root' && $request->user()->role->name !== 'root') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permisos para cambiar la contraseña de este usuario'
                ], 403);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña restablecida exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restablecer contraseña: ' . $e->getMessage()
            ], 500);
        }
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
}
