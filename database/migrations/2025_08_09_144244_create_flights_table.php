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
        Schema::create('flights', function (Blueprint $t){
            $t->id();
            $t->string('code')->unique(); // consecutivo autogenerado p.ej. FL-000123
            $t->foreignId('origin_id')->constrained('cities');
            $t->foreignId('destination_id')->constrained('cities');
            $t->dateTime('departure_at');
            $t->integer('duration_minutes');
            $t->dateTime('arrival_at'); // calculado/ajustado a zona destino si internacional
            $t->boolean('is_international')->default(false);
            $t->unsignedSmallInteger('capacity'); // 150 nacional, 250 internacional
            $t->decimal('price_per_seat',12,2);
            $t->enum('status',['scheduled','cancelled','completed'])->default('scheduled');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
