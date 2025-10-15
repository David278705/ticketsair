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
            if (!Schema::hasColumn('flights', 'aircraft_id')) {
                $table->foreignId('aircraft_id')->nullable()->after('code')->constrained('aircraft')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'aircraft_id')) {
                $table->dropForeign(['aircraft_id']);
                $table->dropColumn('aircraft_id');
            }
        });
    }
};
