<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder {
  public function run(): void {
    $nacionales = ['Bogotá','Medellín','Cali','Barranquilla','Cartagena','Bucaramanga','Pereira','Manizales','Cúcuta','Santa Marta'];
    foreach ($nacionales as $c) \App\Models\City::firstOrCreate(['name'=>$c],['scope'=>'national']);
    $internacionales = ['Madrid','Londres','New York','Buenos Aires','Miami'];
    foreach ($internacionales as $c) \App\Models\City::firstOrCreate(['name'=>$c],['scope'=>'international']);
  }
}