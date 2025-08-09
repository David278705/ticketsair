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
        Schema::create('seat_changes', function (Blueprint $t){
            $t->id();
            $t->foreignId('booking_passenger_id')->constrained()->cascadeOnDelete();
            $t->foreignId('from_seat_id')->nullable()->constrained('seats')->nullOnDelete();
            $t->foreignId('to_seat_id')->constrained('seats');
            $t->dateTime('changed_at');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_changes');
    }
};
