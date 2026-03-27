<?php

namespace Database\Seeders;

use App\Models\Subcategoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $malestares = [
            ['nombre' => 'Dolor de cabeza'],
            ['nombre' => 'Fiebre'],
            ['nombre' => 'Resfriado'],
            ['nombre' => 'Dolor muscular'],
            ['nombre' => 'Alergias'],
            ['nombre' => 'Problemas digestivos'],
            ['nombre' => 'Dolor de garganta'],
            ['nombre' => 'Tos'],
            ['nombre' => 'Dolor de estómago'],
            ['nombre' => 'Dolor de espalda'],
        ];

        foreach ($malestares as $malestar) {
            Subcategoria::create($malestar);
        }

    }
}
