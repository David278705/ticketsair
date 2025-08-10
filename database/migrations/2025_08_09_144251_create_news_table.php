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
        Schema::create('news', function (Blueprint $t){
            $t->id();
            $t->string('title', 180);
            $t->text('body')->nullable();
            $t->string('image_path')->nullable(); // storage/public/news/...
            $t->foreignId('flight_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_promotion')->default(false);
            $t->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
