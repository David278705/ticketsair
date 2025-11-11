<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para MySQL, necesitamos modificar el ENUM directamente
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('recharge', 'payment', 'purchase', 'refund', 'bonus', 'adjustment') DEFAULT 'recharge'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('recharge', 'purchase', 'refund', 'adjustment') DEFAULT 'recharge'");
    }
};
