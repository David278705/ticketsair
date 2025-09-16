<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $rootUser;
    protected $adminUser;
    protected $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles
        $rootRole = Role::create(['name' => 'root', 'description' => 'Root user']);
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $clientRole = Role::create(['name' => 'client', 'description' => 'Client user']);

        // Crear usuarios de prueba
        $this->rootUser = User::create([
            'email' => 'root@ticketsair.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Root',
            'last_name' => 'User',
            'dni' => '12345678',
            'birth_date' => '1980-01-01',
            'role_id' => $rootRole->id,
            'is_active' => true,
            'name' => 'Root User',
            'email_verified_at' => now()
        ]);

        $this->adminUser = User::create([
            'email' => 'admin@ticketsair.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'dni' => '87654321',
            'birth_date' => '1985-01-01',
            'role_id' => $adminRole->id,
            'is_active' => true,
            'name' => 'Admin User',
            'email_verified_at' => now()
        ]);

        $this->clientUser = User::create([
            'email' => 'client@ticketsair.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Client',
            'last_name' => 'User',
            'dni' => '11223344',
            'birth_date' => '1990-01-01',
            'role_id' => $clientRole->id,
            'is_active' => true,
            'name' => 'Client User',
            'email_verified_at' => now()
        ]);
    }

    /** @test */
    public function root_can_list_all_users()
    {
        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'email', 'first_name', 'last_name', 'role']
                    ]
                ]
            ]);
    }

    /** @test */
    public function root_can_create_admin()
    {
        $adminData = [
            'email' => 'newadmin@ticketsair.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'New',
            'last_name' => 'Admin',
            'dni' => '99887766',
            'birth_date' => '1988-05-15'
        ];

        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->postJson('/api/admin/users/create-admin', $adminData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'email', 'role']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@ticketsair.com',
            'first_name' => 'New',
            'last_name' => 'Admin'
        ]);
    }

    /** @test */
    public function non_root_cannot_create_admin()
    {
        $adminData = [
            'email' => 'unauthorized@ticketsair.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Unauthorized',
            'last_name' => 'User',
            'dni' => '55443322',
            'birth_date' => '1990-03-10'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/admin/users/create-admin', $adminData);

        $response->assertStatus(403);
    }

    /** @test */
    public function root_can_reset_user_password()
    {
        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->postJson("/api/admin/users/{$this->clientUser->id}/reset-password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Contraseña restablecida exitosamente'
            ]);
    }

    /** @test */
    public function root_can_update_user_credentials()
    {
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@ticketsair.com'
        ];

        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->putJson("/api/admin/users/{$this->clientUser->id}/credentials", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Credenciales actualizadas exitosamente'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->clientUser->id,
            'email' => 'updated@ticketsair.com',
            'first_name' => 'Updated'
        ]);
    }

    /** @test */
    public function root_can_toggle_user_status()
    {
        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->patchJson("/api/admin/users/{$this->clientUser->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function root_can_get_roles()
    {
        $response = $this->actingAs($this->rootUser, 'sanctum')
            ->getJson('/api/admin/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'description']
                ]
            ]);
    }
}
