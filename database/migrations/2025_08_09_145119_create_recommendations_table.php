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
        Schema::create('recommendations', function (Blueprint $t){
        $t->id();
        $t->foreignId('user_id')->constrained();
        $t->foreignId('flight_id')->constrained();
        $t->decimal('score',5,2)->default(0);
        $t->timestamps();
        $t->unique(['user_id','flight_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
