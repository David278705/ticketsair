<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Cambiar tipo a VARCHAR(10)
        DB::statement('ALTER TABLE `seats` MODIFY `number` VARCHAR(10) NOT NULL');

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

        // Volver a INT (⚠️ sólo seguro si todos los valores son numéricos)
        DB::statement('ALTER TABLE `seats` MODIFY `number` INT NOT NULL');
    }
};
