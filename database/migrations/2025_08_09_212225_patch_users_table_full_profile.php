<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','role_id'))         $t->foreignId('role_id')->nullable()->constrained('roles');
            if (!Schema::hasColumn('users','dni'))             $t->string('dni', 40)->nullable()->unique();
            if (!Schema::hasColumn('users','first_name'))      $t->string('first_name', 100)->nullable();
            if (!Schema::hasColumn('users','last_name'))       $t->string('last_name', 100)->nullable();
            if (!Schema::hasColumn('users','birth_date'))      $t->date('birth_date')->nullable();
            if (!Schema::hasColumn('users','birth_place'))     $t->string('birth_place', 120)->nullable();
            if (!Schema::hasColumn('users','billing_address')) $t->string('billing_address', 200)->nullable();
            if (!Schema::hasColumn('users','gender'))          $t->string('gender', 10)->nullable(); // 'M','F','X'
            if (!Schema::hasColumn('users','username'))        $t->string('username', 60)->nullable()->unique();
            if (!Schema::hasColumn('users','avatar_path'))     $t->string('avatar_path', 255)->nullable();
            if (!Schema::hasColumn('users','news_opt_in'))     $t->boolean('news_opt_in')->default(false);
            if (!Schema::hasColumn('users','wallet_balance'))  $t->decimal('wallet_balance', 12, 2)->default(0);
            if (!Schema::hasColumn('users','email_verified_at')) $t->timestamp('email_verified_at')->nullable();
        });

        // Asegurar índices típicos
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','name') && Schema::hasColumn('users','first_name') && Schema::hasColumn('users','last_name')) {
                // opcional: podrías poblarla por trigger o accessor; no crear columna si no existe
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            // no elimines campos en down si ya están en uso
        });
    }
};

