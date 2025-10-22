<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Thread;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        // Crear foro público si no existe
        Thread::firstOrCreate(
            ['type' => Thread::TYPE_PUBLIC],
            [
                'title' => 'Foro Público',
                'status' => Thread::STATUS_OPEN,
                'user_id' => null
            ]
        );

        $this->command->info('✓ Foro público inicializado');
    }
}
