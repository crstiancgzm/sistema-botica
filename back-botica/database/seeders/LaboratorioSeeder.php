<?php

namespace Database\Seeders;

use App\Models\Laboratorio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laboratorios = [
            ['nombre' => 'Laboratorio 1'],
            ['nombre' => 'Laboratorio 2'],
            ['nombre' => 'Laboratorio 3'],
            ['nombre' => 'Laboratorio 4'],
            ['nombre' => 'Laboratorio 5'],
        ];

        foreach ($laboratorios as $laboratorio) {
            Laboratorio::create($laboratorio);
        }

    }
}
