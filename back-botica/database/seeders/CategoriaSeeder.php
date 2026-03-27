<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Medicamentos'],
            ['nombre' => 'Suplementos'],
            ['nombre' => 'Cuidado Personal'],
            ['nombre' => 'Equipos Médicos'],
            ['nombre' => 'Higiene'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
