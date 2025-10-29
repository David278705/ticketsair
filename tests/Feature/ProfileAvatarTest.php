<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        Role::factory()->create(['name' => 'client']);
        Role::factory()->create(['name' => 'root']);
    }

    /** @test */
    public function user_can_upload_avatar()
    {
        Storage::fake('public');
        
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'client')->first()->id
        ]);
        
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(1024); // 1MB

        $response = $this->actingAs($user)
            ->postJson('/api/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Foto de perfil actualizada exitosamente.'
            ]);

        // Verificar que el archivo se guardó
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
        
        // Verificar que el usuario tiene la ruta del avatar
        $this->assertNotNull($user->fresh()->avatar_path);
    }

    /** @test */
    public function avatar_must_be_an_image()
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'client')->first()->id
        ]);
        
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($user)
            ->postJson('/api/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function avatar_must_not_exceed_2mb()
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'client')->first()->id
        ]);
        
        $file = UploadedFile::fake()->image('avatar.jpg')->size(3000); // 3MB

        $response = $this->actingAs($user)
            ->postJson('/api/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function user_can_delete_avatar()
    {
        Storage::fake('public');
        
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'client')->first()->id,
            'avatar_path' => 'avatars/test_avatar.jpg'
        ]);
        
        // Crear archivo fake
        Storage::disk('public')->put('avatars/test_avatar.jpg', 'fake content');

        $response = $this->actingAs($user)
            ->deleteJson('/api/profile/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Foto de perfil eliminada exitosamente.'
            ]);

        // Verificar que el archivo se eliminó
        Storage::disk('public')->assertMissing('avatars/test_avatar.jpg');
        
        // Verificar que el usuario no tiene ruta de avatar
        $this->assertNull($user->fresh()->avatar_path);
    }

    /** @test */
    public function uploading_new_avatar_deletes_old_one()
    {
        Storage::fake('public');
        
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'client')->first()->id,
            'avatar_path' => 'avatars/old_avatar.jpg'
        ]);
        
        // Crear archivo fake antiguo
        Storage::disk('public')->put('avatars/old_avatar.jpg', 'old content');
        
        $newFile = UploadedFile::fake()->image('new_avatar.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/profile/avatar', [
                'avatar' => $newFile,
            ]);

        $response->assertStatus(200);

        // Verificar que el archivo antiguo se eliminó
        Storage::disk('public')->assertMissing('avatars/old_avatar.jpg');
        
        // Verificar que el nuevo archivo existe
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
    }

    /** @test */
    public function guest_cannot_upload_avatar()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/profile/avatar', [
            'avatar' => $file,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function guest_cannot_delete_avatar()
    {
        $response = $this->deleteJson('/api/profile/avatar');

        $response->assertStatus(401);
    }
}
