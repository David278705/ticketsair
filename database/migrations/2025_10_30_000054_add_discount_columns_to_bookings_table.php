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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('flight_id')->constrained()->nullOnDelete();
            $table->decimal('original_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'original_amount', 'discount_amount']);
        });
    }
};
