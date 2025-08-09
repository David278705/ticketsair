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
        Schema::create('promotions', function (Blueprint $t){
            $t->id();
            $t->foreignId('flight_id')->constrained();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedTinyInteger('discount_percent'); // 5..90
            $t->unsignedInteger('max_seats')->nullable(); // opcional
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
