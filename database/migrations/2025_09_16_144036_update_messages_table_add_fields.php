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
        Schema::table('messages', function (Blueprint $table) {
            // Renombrar content a body si existe
            if (Schema::hasColumn('messages', 'content')) {
                $table->renameColumn('content', 'body');
            }
            
            // Agregar nuevos campos
            if (!Schema::hasColumn('messages', 'subject')) {
                $table->string('subject')->after('to_user_id');
            }
            
            if (!Schema::hasColumn('messages', 'body') && !Schema::hasColumn('messages', 'content')) {
                $table->text('body')->after('subject');
            }
            
            if (!Schema::hasColumn('messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('body');
            }
            
            if (!Schema::hasColumn('messages', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['subject', 'is_read', 'sent_at']);
            
            // Revertir body a content si es necesario
            if (Schema::hasColumn('messages', 'body')) {
                $table->renameColumn('body', 'content');
            }
        });
    }
};
