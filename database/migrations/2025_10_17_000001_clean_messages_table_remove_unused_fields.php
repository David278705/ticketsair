<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Eliminar campo 'subject' - no se usa en el sistema de foro
            if (Schema::hasColumn('messages', 'subject')) {
                $table->dropColumn('subject');
            }
            
            // Eliminar campo 'sent_at' - redundante con 'created_at'
            if (Schema::hasColumn('messages', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
            
            // Hacer 'to_user_id' nullable - necesario para mensajes del foro público
            $table->foreignId('to_user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->string('subject')->nullable()->after('to_user_id');
            $table->timestamp('sent_at')->nullable()->after('is_read');
            
            // Revertir to_user_id a NOT NULL
            $table->foreignId('to_user_id')->nullable(false)->change();
        });
    }
};
