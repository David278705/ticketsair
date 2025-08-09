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
        Schema::create('payments', function (Blueprint $t){
            $t->id();
            $t->morphs('payable'); // payable_id, payable_type (Booking)
            $t->foreignId('user_id')->constrained(); // quien paga
            $t->foreignId('card_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('status',['pending','paid','refunded','failed'])->default('pending');
            $t->decimal('amount',12,2);
            $t->json('meta')->nullable(); // info de pasarela simulado
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
