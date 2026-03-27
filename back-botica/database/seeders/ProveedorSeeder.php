<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            ['razon_social' => 'Proveedor 1'],
            ['razon_social' => 'Proveedor 2'],
            ['razon_social' => 'Proveedor 3'],
            ['razon_social' => 'Proveedor 4'],
            ['razon_social' => 'Proveedor 5'],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
