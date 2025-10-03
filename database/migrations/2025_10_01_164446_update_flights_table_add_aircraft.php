<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            // Solo agregar aircraft_id si no existe
            if (!Schema::hasColumn('flights', 'aircraft_id')) {
                $table->string('aircraft_id', 10)->nullable()->after('destination_id');
            }
            
            // Verificar y eliminar campos que ya no se necesitan
            if (Schema::hasColumn('flights', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
            
            if (Schema::hasColumn('flights', 'capacity')) {
                $table->dropColumn('capacity');
            }

            // Agregar campo scope si no existe
            if (!Schema::hasColumn('flights', 'scope')) {
                if (Schema::hasColumn('flights', 'is_international')) {
                    // Primero cambiar el tipo a string si es boolean
                    $table->string('scope')->default('national')->after('destination_id');
                    // Luego actualizar datos basado en is_international
                    \DB::statement("UPDATE flights SET scope = CASE WHEN is_international = 1 THEN 'international' ELSE 'national' END");
                    // Finalmente eliminar is_international
                    $table->dropColumn('is_international');
                } else {
                    $table->string('scope')->default('national')->after('destination_id');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->integer('duration_minutes')->default(0);
            $table->unsignedSmallInteger('capacity')->default(150);
            
            // Eliminar campo del avión
            if (Schema::hasColumn('flights', 'aircraft_id')) {
                $table->dropColumn('aircraft_id');
            }
            
            // Restaurar is_international
            if (Schema::hasColumn('flights', 'scope')) {
                $table->boolean('is_international')->default(false);
                \DB::statement("UPDATE flights SET is_international = CASE WHEN scope = 'international' THEN 1 ELSE 0 END");
                $table->dropColumn('scope');
            }
        });
    }
};
