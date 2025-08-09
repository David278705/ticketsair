<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RootUserSeeder extends Seeder {
  public function run(): void {
    $root = Role::where('name','root')->first();
    User::firstOrCreate(
      ['email'=>'root@local.test'],
      [
        'role_id'=>$root->id,
        'name'=>'Root',
        'password'=>bcrypt('root1234'), // cámbialo
      ]
    );
  }
}