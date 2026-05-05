<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermisosFuncionalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = Role::first();

        $permisos = [
            //categoria
            ['name' => 'admin-categoria-crear', 'description' => 'Crear Categoria'],
            ['name' => 'admin-categoria-editar', 'description' => 'Editar Categoria'],
            ['name' => 'admin-categoria-eliminar', 'description' => 'Eliminar Categoria'],
            //categoria
            ['name' => 'admin-precentacion-editar', 'description' => 'editar precentaciones'],

            //categoria malestar
            ['name' => 'admin-categoria-malestar-crear', 'description' => 'Crear Categoria malestar'],
            ['name' => 'admin-categoria-malestar-editar', 'description' => 'Editar Categoria malestar'],
            ['name' => 'admin-categoria-malestar-eliminar', 'description' => 'Eliminar Categoria malestar'],
            //categoria laboratorios
            ['name' => 'admin-laboratorios-crear', 'description' => 'Crear laboratorios'],
            ['name' => 'admin-laboratorios-editar', 'description' => 'Editar laboratorios'],
            ['name' => 'admin-laboratorios-eliminar', 'description' => 'Eliminar laboratorios'],
            //categoria proveedores
            ['name' => 'admin-proveedores-crear', 'description' => 'Crear proveedores'],
            ['name' => 'admin-proveedores-editar', 'description' => 'Editar proveedores'],
            ['name' => 'admin-proveedores-eliminar', 'description' => 'Eliminar proveedores'],
            //categoria inventariado
            ['name' => 'admin-inventario-crear', 'description' => 'Crear inventariado'],
            ['name' => 'admin-inventario-editar', 'description' => 'Editar inventariado'],
            ['name' => 'admin-inventario-eliminar', 'description' => 'Eliminar inventariado'],
            ['name' => 'admin-inventario-generador-codigo-barras', 'description' => 'generador codigo barras'],
            ['name' => 'admin-inventario-exportar', 'description' => 'exportar'],
            //categoria ventas
            
            //categoria de cajas
            ['name' => 'admin-caja-ver', 'description' => 'ver caja'],
            ['name' => 'admin-caja-abrir', 'description' => 'abrir caja'],
            ['name' => 'admin-caja-cerrar', 'description' => 'cerrar caja'],
            ['name' => 'admin-caja-reimprimir', 'description' => 'cerrar caja'],
            
            //categoria de historial
            ['name' => 'admin-abrir-historial', 'description' => 'abrir historial'],
            ['name' => 'admin-cerrar-historial', 'description' => 'cerrar historial'],

        ];
    
        foreach ($permisos as $key => $permiso) {
            Permission::create($permiso)->assignRole([$admin]);
        }

        $user = User::first();

        $user->assignRole('Administrador');


    }
}
