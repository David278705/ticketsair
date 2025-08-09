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
        Schema::create('seats', function (Blueprint $t){
            $t->id();
            $t->foreignId('flight_id')->constrained();
            $t->unsignedSmallInteger('number'); // 1..N
            $t->enum('class',['first','economy']);
            $t->enum('status',['available','reserved','assigned','checked_in'])->default('available');
            $t->unique(['flight_id','number']);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
