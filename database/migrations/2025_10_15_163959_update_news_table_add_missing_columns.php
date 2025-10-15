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
        Schema::table('news', function (Blueprint $table) {
            // Renombrar description a body
            $table->renameColumn('description', 'body');
            
            // Modificar título para que tenga máximo 180 caracteres
            $table->string('title', 180)->change();
            
            // Agregar columnas faltantes
            $table->string('image_path')->nullable()->after('body');
            $table->foreignId('flight_id')->nullable()->constrained()->nullOnDelete()->after('image_path');
            $table->boolean('is_promotion')->default(false)->after('flight_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Revertir los cambios
            $table->renameColumn('body', 'description');
            $table->string('title', 255)->change();
            $table->dropForeign(['flight_id']);
            $table->dropColumn(['image_path', 'flight_id', 'is_promotion']);
        });
    }
};
