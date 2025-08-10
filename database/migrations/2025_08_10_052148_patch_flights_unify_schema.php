<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Agregar columnas nuevas que usa el admin/frontend
        Schema::table('flights', function (Blueprint $t) {
            // scope reemplaza is_international
            if (!Schema::hasColumn('flights','scope')) {
                $t->string('scope', 20)->default('national')->after('arrival_at'); // 'national'|'international'
            }
            if (!Schema::hasColumn('flights','capacity_first')) {
                $t->unsignedSmallInteger('capacity_first')->default(0)->after('scope');
            }
            if (!Schema::hasColumn('flights','capacity_economy')) {
                $t->unsignedSmallInteger('capacity_economy')->default(0)->after('capacity_first');
            }
        });

        // 2) Migrar datos existentes (si los hay)
        // - scope: a partir de is_international
        // - repartir capacity en first + economy (p. ej., 15%/85% con mínimo 8 en first)
        $rows = DB::table('flights')->select('id','is_international','capacity')->get();
        foreach ($rows as $r) {
            $scope = ($r->is_international ?? false) ? 'international' : 'national';

            $cap = (int)($r->capacity ?? 0);
            if ($cap < 1) { $first = 0; $eco = 0; }
            else {
                $first = max(8, (int)round($cap * 0.15)); // heurística por defecto
                if ($first > 60) $first = 60;             // límite razonable
                if ($first > $cap) $first = (int)floor($cap/5); // fallback
                $eco = max(0, $cap - $first);
            }

            DB::table('flights')->where('id',$r->id)->update([
                'scope'            => $scope,
                'capacity_first'   => $first,
                'capacity_economy' => $eco,
            ]);
        }

        // 3) Eliminar columnas antiguas que ya no usaremos
        Schema::table('flights', function (Blueprint $t) {
            if (Schema::hasColumn('flights','capacity'))        $t->dropColumn('capacity');
            if (Schema::hasColumn('flights','is_international')) $t->dropColumn('is_international');
        });
    }

    public function down(): void
    {
        // Revertir: volver a tener is_international y capacity (estimando desde scope y sumas)
        Schema::table('flights', function (Blueprint $t) {
            if (!Schema::hasColumn('flights','is_international')) $t->boolean('is_international')->default(false)->after('arrival_at');
            if (!Schema::hasColumn('flights','capacity'))         $t->unsignedSmallInteger('capacity')->default(0)->after('is_international');
        });

        $rows = DB::table('flights')->select('id','scope','capacity_first','capacity_economy')->get();
        foreach ($rows as $r) {
            $isIntl = ($r->scope ?? 'national') === 'international';
            $cap = (int)($r->capacity_first ?? 0) + (int)($r->capacity_economy ?? 0);
            DB::table('flights')->where('id',$r->id)->update([
                'is_international' => $isIntl,
                'capacity'         => $cap,
            ]);
        }

        Schema::table('flights', function (Blueprint $t) {
            if (Schema::hasColumn('flights','capacity_first'))   $t->dropColumn('capacity_first');
            if (Schema::hasColumn('flights','capacity_economy')) $t->dropColumn('capacity_economy');
            if (Schema::hasColumn('flights','scope'))            $t->dropColumn('scope');
        });
    }
};
