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
        Schema::create('luggage', function (Blueprint $t){
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->enum('type',['cabin','hold'])->default('hold'); // aquí registramos la de bodega
            $t->unsignedTinyInteger('pieces')->default(1);
            $t->decimal('extra_fee',10,2)->default(0);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('luggage');
    }
};
