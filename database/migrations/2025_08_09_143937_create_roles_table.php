<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('roles', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique(); // root, admin, client, visitor
      $table->timestamps();
    });
    Schema::table('users', function (Blueprint $table) {
      $table->foreignId('role_id')->after('id')->default(0);
    });
  }
  public function down(): void {
    Schema::table('users', fn(Blueprint $t)=>$t->dropColumn('role_id'));
    Schema::dropIfExists('roles');
  }
};
