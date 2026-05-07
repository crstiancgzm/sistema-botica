<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::updateOrCreate(['name' => 'Administrador'], ['name' => 'Administrador', 'guard_name' => 'api']);
        Permission::updateOrCreate(['name' => 'admin-permisos'], [
            'name' => 'admin-permisos',
            'description' => 'Administrar Permisos'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-roles'], [
            'name' => 'admin-roles',
            'description' => 'Administrar usuarios'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-usuarios'], [
            'name' => 'admin-usuarios',
            'description' => 'Administrar usuarios'
        ])->assignRole([$admin]);

        Permission::updateOrCreate(['name' => 'admin-usuarios-index'], [
            'name' => 'admin-usuarios-index',
            'description' => 'Ver lista de usuarios'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-roles-index'], [
            'name' => 'admin-roles-index',
            'description' => 'Ver lista de roles'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-permisos-index'], [
            'name' => 'admin-permisos-index',
            'description' => 'Ver lista de permisos'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-presentacion-index'], [
            'name' => 'admin-presentacion-index',
            'description' => 'Ver lista de precentaciones'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-categoria-index'], [
            'name' => 'admin-categoria-index',
            'description' => 'Ver lista de categorias'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-categoria-malestar-index'], [
            'name' => 'admin-categoria-malestar-index',
            'description' => 'Ver lista de categoria de malestar'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-laboratorio-index'], [
            'name' => 'admin-laboratorio-index',
            'description' => 'Ver lista de lagoratorio'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-proveedor-index'], [
            'name' => 'admin-proveedor-index',
            'description' => 'Ver lista de proveedores'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-inventario-index'], [
            'name' => 'admin-inventario-index',
            'description' => 'Ver lista de inventariado'
        ])->assignRole([$admin]);

        Permission::updateOrCreate(['name' => 'admin-presentacion-editar'], [
            'name' => 'admin-presentacion-editar',
            'description' => 'edicion en precentaciones'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-presentacion-eliminar'], [
            'name' => 'admin-presentacion-eliminar',
            'description' => 'eliminación en precentaciones'
        ])->assignRole([$admin]);

        Permission::updateOrCreate(['name' => 'admin-venta-index'], [
            'name' => 'admin-venta-index',
            'description' => 'lista de productos de venta'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-caja-index'], [
            'name' => 'admin-caja-index',
            'description' => 'apertura de caja'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-historial-index'], [
            'name' => 'admin-historial-index',
            'description' => 'historial de venta'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-reporte-index'], [
            'name' => 'admin-reporte-index',
            'description' => 'historial de reporte'
        ])->assignRole([$admin]);



        $user = User::create([
            'name' => 'password',
            'email' => 'password@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Administrador');

        // $user = User::create([
        //     'name' => 'password',
        //     'email' => 'user@gmail.com',
        //     'password' => bcrypt('user@gmail.com'),
        // ]);

        

    }
}
