<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CityDistancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Distancias aproximadas en kilómetros entre ciudades
     */
    public function run(): void
    {
        // Primero obtenemos todas las ciudades
        $cities = \App\Models\City::all()->keyBy('name');
        
        // Matriz de distancias (en km) - solo ciudades colombianas entre sí
        $nationalDistances = [
            'Bogotá' => [
                'Medellín' => 415,
                'Cali' => 460,
                'Barranquilla' => 990,
                'Cartagena' => 1060,
                'Bucaramanga' => 395,
                'Pereira' => 320,
                'Manizales' => 285,
                'Cúcuta' => 550,
                'Santa Marta' => 985
            ],
            'Medellín' => [
                'Bogotá' => 415,
                'Cali' => 410,
                'Barranquilla' => 635,
                'Cartagena' => 640,
                'Bucaramanga' => 390,
                'Pereira' => 200,
                'Manizales' => 205,
                'Cúcuta' => 535,
                'Santa Marta' => 730
            ],
            'Cali' => [
                'Bogotá' => 460,
                'Medellín' => 410,
                'Barranquilla' => 1015,
                'Cartagena' => 950,
                'Bucaramanga' => 685,
                'Pereira' => 230,
                'Manizales' => 305,
                'Cúcuta' => 830,
                'Santa Marta' => 1020
            ],
            'Barranquilla' => [
                'Bogotá' => 990,
                'Medellín' => 635,
                'Cali' => 1015,
                'Cartagena' => 115,
                'Bucaramanga' => 595,
                'Pereira' => 770,
                'Manizales' => 805,
                'Cúcuta' => 580,
                'Santa Marta' => 95
            ],
            'Cartagena' => [
                'Bogotá' => 1060,
                'Medellín' => 640,
                'Cali' => 950,
                'Barranquilla' => 115,
                'Bucaramanga' => 615,
                'Pereira' => 675,
                'Manizales' => 710,
                'Cúcuta' => 670,
                'Santa Marta' => 205
            ],
            'Bucaramanga' => [
                'Bogotá' => 395,
                'Medellín' => 390,
                'Cali' => 685,
                'Barranquilla' => 595,
                'Cartagena' => 615,
                'Pereira' => 475,
                'Manizales' => 460,
                'Cúcuta' => 195,
                'Santa Marta' => 560
            ],
            'Pereira' => [
                'Bogotá' => 320,
                'Medellín' => 200,
                'Cali' => 230,
                'Barranquilla' => 770,
                'Cartagena' => 675,
                'Bucaramanga' => 475,
                'Manizales' => 55,
                'Cúcuta' => 610,
                'Santa Marta' => 865
            ],
            'Manizales' => [
                'Bogotá' => 285,
                'Medellín' => 205,
                'Cali' => 305,
                'Barranquilla' => 805,
                'Cartagena' => 710,
                'Bucaramanga' => 460,
                'Pereira' => 55,
                'Cúcuta' => 585,
                'Santa Marta' => 895
            ],
            'Cúcuta' => [
                'Bogotá' => 550,
                'Medellín' => 535,
                'Cali' => 830,
                'Barranquilla' => 580,
                'Cartagena' => 670,
                'Bucaramanga' => 195,
                'Pereira' => 610,
                'Manizales' => 585,
                'Santa Marta' => 545
            ],
            'Santa Marta' => [
                'Bogotá' => 985,
                'Medellín' => 730,
                'Cali' => 1020,
                'Barranquilla' => 95,
                'Cartagena' => 205,
                'Bucaramanga' => 560,
                'Pereira' => 865,
                'Manizales' => 895,
                'Cúcuta' => 545
            ]
        ];

        // Distancias desde ciudades colombianas a destinos internacionales (en km)
        $internationalDistances = [
            'Bogotá' => [
                'Madrid' => 8015,
                'Londres' => 8484,
                'New York' => 4016,
                'Buenos Aires' => 4690,
                'Miami' => 2494
            ],
            'Medellín' => [
                'Madrid' => 8235,
                'Londres' => 8700,
                'New York' => 4176,
                'Buenos Aires' => 4880,
                'Miami' => 2304
            ],
            'Cali' => [
                'Madrid' => 8450,
                'Londres' => 8915,
                'New York' => 4540,
                'Buenos Aires' => 4865,
                'Miami' => 2858
            ],
            'Cartagena' => [
                'Madrid' => 7880,
                'Londres' => 8344,
                'New York' => 2498,
                'Buenos Aires' => 5135,
                'Miami' => 1692
            ],
            'Pereira' => [
                'Madrid' => 8195,
                'Londres' => 8660,
                'New York' => 4340,
                'Buenos Aires' => 4790,
                'Miami' => 2630
            ]
        ];

        // Actualizar ciudades con sus distancias
        foreach ($nationalDistances as $cityName => $distances) {
            if (isset($cities[$cityName])) {
                $city = $cities[$cityName];
                $distanceMap = [];
                
                foreach ($distances as $destName => $distance) {
                    if (isset($cities[$destName])) {
                        $distanceMap[$cities[$destName]->id] = $distance;
                    }
                }
                
                // Agregar distancias internacionales si existen
                if (isset($internationalDistances[$cityName])) {
                    foreach ($internationalDistances[$cityName] as $destName => $distance) {
                        if (isset($cities[$destName])) {
                            $distanceMap[$cities[$destName]->id] = $distance;
                        }
                    }
                }
                
                $city->update(['distances' => $distanceMap]);
            }
        }
    }
}
