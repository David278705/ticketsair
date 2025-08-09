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
        Schema::create('bookings', function (Blueprint $t){
            $t->id();
            $t->foreignId('user_id')->constrained();  // cliente
            $t->foreignId('flight_id')->constrained();
            $t->enum('type',['reservation','purchase']); // reserva o compra
            $t->enum('status',['pending','confirmed','cancelled','expired'])->default('pending');
            $t->string('reservation_code')->unique(); // enviado por correo
            $t->enum('travel_type',['one_way','round_trip'])->default('one_way'); // trayecto único o completo
            $t->dateTime('expires_at')->nullable(); // reservas: +24h
            $t->unsignedTinyInteger('seats_count'); // 1..5
            $t->decimal('total_amount',12,2)->default(0);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
