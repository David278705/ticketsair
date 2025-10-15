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
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Boeing 737-800
            $table->string('brand'); // Ej: Boeing
            $table->unsignedSmallInteger('capacity_first')->default(0); // Capacidad primera clase
            $table->unsignedSmallInteger('capacity_economy'); // Capacidad clase económica
            $table->unsignedSmallInteger('speed_kmh'); // Velocidad en km/h
            $table->boolean('is_active')->default(true); // Si está disponible para uso
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};
