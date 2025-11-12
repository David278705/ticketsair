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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('card')->after('amount')->comment('wallet, saved_card, new_card, card');
            $table->unsignedBigInteger('wallet_transaction_id')->nullable()->after('payment_method');
            $table->foreign('wallet_transaction_id')->references('id')->on('wallet_transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['wallet_transaction_id']);
            $table->dropColumn(['payment_method', 'wallet_transaction_id']);
        });
    }
};
