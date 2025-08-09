<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('users', function (Blueprint $table) {
      $table->string('dni')->nullable()->index();
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->date('birth_date')->nullable();
      $table->string('birth_place')->nullable();
      $table->string('billing_address')->nullable();
      $table->enum('gender', ['M','F','X'])->nullable();
      $table->string('username')->nullable()->unique();
      $table->string('avatar_path')->nullable();
      $table->boolean('news_opt_in')->default(false);
      $table->decimal('wallet_balance', 12,2)->default(0); // saldo simple del módulo financiero
      $table->foreignId('role_id')->nullable()->change();
    });
  }
  public function down(): void {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn([
        'dni','first_name','last_name','birth_date','birth_place',
        'billing_address','gender','username','avatar_path','news_opt_in','wallet_balance'
      ]);
    });
  }
};