<?php

namespace Database\Seeders;

use App\Models\Presentacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PresentacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presentaciones = [
            ['nombre' => 'Caja'],
            ['nombre' => 'Frasco'],
            ['nombre' => 'Blister'],
            ['nombre' => 'Ampolla'],
            ['nombre' => 'Sachet'],
            ['nombre' => 'Tubo'],
            ['nombre' => 'Vial'],
            ['nombre' => 'Sobre'],
            ['nombre' => 'Bolsa'],
            ['nombre' => 'Píldora'],
        ];

        foreach ($presentaciones as $presentacion) {
            Presentacion::create($presentacion);
        }
    }
}
