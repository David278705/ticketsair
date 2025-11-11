<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Cambiar tipo a VARCHAR(10) - Compatible con MySQL y PostgreSQL
        Schema::table('seats', function (Blueprint $table) {
            $table->string('number', 10)->change();
        });

        // Asegurar columnas class/status si no existen (por si tu seats original era minimal)
        Schema::table('seats', function (Blueprint $t) {
            if (!Schema::hasColumn('seats','class')) {
                $t->string('class', 20)->default('economy')->after('number'); // 'first'|'economy'
            }
            if (!Schema::hasColumn('seats','status')) {
                $t->string('status', 20)->default('available')->after('class'); // 'available'|...
            }
        });

        // Índice único por (flight_id, number)
        Schema::table('seats', function (Blueprint $t) {
            $t->unique(['flight_id','number'], 'seats_flight_number_unique');
        });
    }

    public function down(): void
    {
        // Quitar unique
        Schema::table('seats', function (Blueprint $t) {
            $t->dropUnique('seats_flight_number_unique');
        });

        // Volver a INT con conversión explícita para PostgreSQL
        // Primero limpiar datos no numéricos si los hay
        DB::statement("UPDATE seats SET number = regexp_replace(number, '[^0-9]', '', 'g') WHERE number ~ '[^0-9]'");
        
        // Ahora convertir usando USING para PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE seats ALTER COLUMN number TYPE INTEGER USING number::integer');
        } else {
            Schema::table('seats', function (Blueprint $table) {
                $table->integer('number')->change();
            });
        }
    }
};
