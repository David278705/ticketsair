<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['public', 'private'])->default('private');
            $table->string('title')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('user_id');
        });

        // Agregar thread_id a messages
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('thread_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index('thread_id');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['thread_id']);
            $table->dropColumn('thread_id');
        });
        
        Schema::dropIfExists('threads');
    }
};
