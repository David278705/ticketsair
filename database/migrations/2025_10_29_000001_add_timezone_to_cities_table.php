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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('timezone', 50)->default('America/Bogota')->after('scope');
            $table->string('country', 100)->nullable()->after('name');
        });

        // Actualizar ciudades existentes con zonas horarias comunes de Colombia
        DB::table('cities')->update([
            'timezone' => 'America/Bogota',
            'country' => 'Colombia'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'country']);
        });
    }
};
