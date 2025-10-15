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
        Schema::table('flights', function (Blueprint $table) {
            if (!Schema::hasColumn('flights', 'duration_minutes')) {
                $table->integer('duration_minutes')->after('departure_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
