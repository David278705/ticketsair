<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aircraft = [
            [
                'name' => 'Boeing 737-800',
                'brand' => 'Boeing',
                'capacity_first' => 12,
                'capacity_economy' => 150,
                'speed_kmh' => 850,
                'is_active' => true
            ],
            [
                'name' => 'Airbus A320',
                'brand' => 'Airbus',
                'capacity_first' => 16,
                'capacity_economy' => 134,
                'speed_kmh' => 840,
                'is_active' => true
            ],
            [
                'name' => 'Boeing 787 Dreamliner',
                'brand' => 'Boeing',
                'capacity_first' => 28,
                'capacity_economy' => 222,
                'speed_kmh' => 900,
                'is_active' => true
            ],
            [
                'name' => 'Airbus A330-200',
                'brand' => 'Airbus',
                'capacity_first' => 24,
                'capacity_economy' => 246,
                'speed_kmh' => 880,
                'is_active' => true
            ],
            [
                'name' => 'Embraer E190',
                'brand' => 'Embraer',
                'capacity_first' => 8,
                'capacity_economy' => 90,
                'speed_kmh' => 830,
                'is_active' => true
            ],
            [
                'name' => 'Boeing 777-300ER',
                'brand' => 'Boeing',
                'capacity_first' => 35,
                'capacity_economy' => 331,
                'speed_kmh' => 905,
                'is_active' => true
            ],
            [
                'name' => 'Airbus A350-900',
                'brand' => 'Airbus',
                'capacity_first' => 32,
                'capacity_economy' => 283,
                'speed_kmh' => 910,
                'is_active' => true
            ],
            [
                'name' => 'ATR 72-600',
                'brand' => 'ATR',
                'capacity_first' => 0,
                'capacity_economy' => 68,
                'speed_kmh' => 510,
                'is_active' => true
            ]
        ];

        foreach ($aircraft as $plane) {
            \App\Models\Aircraft::create($plane);
        }
    }
}
