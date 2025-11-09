<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder {
  public function run(): void {
    // Ciudades nacionales (Colombia - America/Bogota)
    $nacionales = [
      ['name' => 'Bogotá', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Medellín', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Cali', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Barranquilla', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Cartagena', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Bucaramanga', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Pereira', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Manizales', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Cúcuta', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
      ['name' => 'Santa Marta', 'timezone' => 'America/Bogota', 'country' => 'Colombia'],
    ];
    
    foreach ($nacionales as $city) {
      \App\Models\City::updateOrCreate(
        ['name' => $city['name']], 
        [
          'scope' => 'national',
          'timezone' => $city['timezone'],
          'country' => $city['country']
        ]
      );
    }
    
    // Ciudades internacionales con sus zonas horarias correctas
    $internacionales = [
      ['name' => 'Madrid', 'timezone' => 'Europe/Madrid', 'country' => 'España'],
      ['name' => 'Londres', 'timezone' => 'Europe/London', 'country' => 'Reino Unido'],
      ['name' => 'New York', 'timezone' => 'America/New_York', 'country' => 'Estados Unidos'],
      ['name' => 'Buenos Aires', 'timezone' => 'America/Argentina/Buenos_Aires', 'country' => 'Argentina'],
      ['name' => 'Miami', 'timezone' => 'America/New_York', 'country' => 'Estados Unidos'],
    ];
    
    foreach ($internacionales as $city) {
      \App\Models\City::updateOrCreate(
        ['name' => $city['name']], 
        [
          'scope' => 'international',
          'timezone' => $city['timezone'],
          'country' => $city['country']
        ]
      );
    }
  }
}