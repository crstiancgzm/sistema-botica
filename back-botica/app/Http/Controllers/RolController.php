<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRolRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Role::query(),
            [],
            ['id', 'name'],
            ['id', 'name']
        );
    }

    public function store(StoreRolRequest $request)
    {
        $rol = Role::create($request['rol']);
        
        if (!empty(data_get($request, 'rol.permisosSelected'))) {
            $rol->syncPermissions(data_get($request, 'rol.permisosSelected'));
        };

        return response()->json([$rol->save()], 201);
    }

    public function show(Role $role)
    {
        // return response()->json($role);
        return response()->json([
            'rol' => $role,
            'permisosSelected' => $role->permissions->pluck('id'),
        ]);
    }

    public function update(StoreRolRequest $request,  Role $role)
    {
        //
        $role->syncPermissions(data_get($request, 'rol.permisosSelected'));
        return response()->json($role->update($request['rol']));
    }

    public function destroy(Role $role)
    {
        //
        return response()->json($role->delete());
    }
}
