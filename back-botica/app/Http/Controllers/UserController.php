<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            User::query(),
            [],
            ['id', 'name'],
            ['id', 'name']
        );
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->syncRoles($request->rolesSelected);
        $user->syncPermissions($request->permisosSelected);
        return response()->json([$user->save()], 201);
    }

    public function show(User $usuario)
    {
        return response()->json([
            'user' => $usuario,
            'rolesSelected' => $usuario->roles->pluck('id'),
            'permisosSelected' => $usuario->permissions->pluck('id'),
        ]);
        // $data = [
        //     'user' => $usuario,
        //     'rolesSelected' => $usuario->roles->pluck('id'),
        //     'permisosSelected' => $usuario->permissions->pluck('id'),
        // ];

        $encryptedData = Crypt::encryptString(json_encode($data));


        return response()->json([
            'data' => $encryptedData
        ]);
    }

    public function update(StoreUserRequest $request, User $usuario)
    {
        try {
            $usuario->syncRoles($request->rolesSelected);
            $usuario->syncPermissions($request->permisosSelected);

            $usuario->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? bcrypt($request->password) : $usuario->password,
            ]);

            return response()->json($usuario);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el usuario',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $usuario)
    {
        return response()->json($usuario->delete());
    }

    // public function usuario_password(StoreUpdatePasswordRequest $request, $id)
    // {
    //     $usuario = User::find($id);
    //     $usuario->update([
    //         'password' => $request->filled('password') ? bcrypt($request->password) : $usuario->password,
    //     ]);

    //     return response()->json($usuario);

    // }

    // public function usuarios_reset($usuario)
    // {
    //     $usuario = User::find($usuario);
    //     $nuevoPassword = $usuario->dni;

    //     $usuario->update([
    //         'password' => bcrypt($nuevoPassword),
    //     ]);

    //     return response()->json($usuario);
    // }
}
