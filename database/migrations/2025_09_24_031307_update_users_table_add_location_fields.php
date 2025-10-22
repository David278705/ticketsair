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
        Schema::table('users', function (Blueprint $table) {
            // Agregar nuevos campos de ubicación después de wallet_balance
            $table->string('country_code', 3)->nullable()->after('wallet_balance');
            $table->string('country_name', 100)->nullable()->after('country_code');
            $table->string('state_code', 10)->nullable()->after('country_name');
            $table->string('state_name', 100)->nullable()->after('state_code');
            $table->string('city_id', 100)->nullable()->after('state_name');
            $table->string('city_name', 100)->nullable()->after('city_id');
            
            // Agregar índices para búsquedas
            $table->index(['country_code']);
            $table->index(['state_code']);
            $table->index(['city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex(['country_code']);
            $table->dropIndex(['state_code']);
            $table->dropIndex(['city_id']);
            
            // Remover campos de ubicación
            $table->dropColumn([
                'country_code',
                'country_name', 
                'state_code',
                'state_name',
                'city_id',
                'city_name'
            ]);
        });
    }
};
