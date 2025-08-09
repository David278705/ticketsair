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
        Schema::create('booking_passengers', function (Blueprint $t){
            $t->id();
            $t->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // si el viajero es usuario registrado
            // datos requeridos
            $t->string('dni');
            $t->string('first_name');
            $t->string('last_name');
            $t->date('birth_date');
            $t->enum('gender',['M','F','X']);
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('emergency_contact_name')->nullable();
            $t->string('emergency_contact_phone')->nullable();
            // asiento
            $t->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('class',['first','economy']);
            $t->boolean('seat_changed_once')->default(false);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_passengers');
    }
};
