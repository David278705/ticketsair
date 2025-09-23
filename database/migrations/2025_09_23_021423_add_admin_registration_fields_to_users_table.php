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
        Schema::table('users', function (Blueprint $table) {
            $table->string('temp_password_token')->nullable()->after('remember_token');
            $table->timestamp('temp_password_expires_at')->nullable()->after('temp_password_token');
            $table->boolean('registration_completed')->default(true)->after('temp_password_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['temp_password_token', 'temp_password_expires_at', 'registration_completed']);
        });
    }
};
