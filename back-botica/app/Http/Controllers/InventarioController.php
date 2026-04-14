<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Http\Requests\StoreInventarioRequest;
use App\Http\Requests\UpdateInventarioRequest;
use Symfony\Component\HttpFoundation\Request;
use App\Services\ArchivosService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventarioController extends Controller
{
    public function __construct(
        private ArchivosService $archivosService,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Inventario::query(),
            [],
            ['id','nombre','codigo'],
            ['id']
        );
    }

    public function store(StoreInventarioRequest $request)
    {
        $uploadedFiles = [];

        DB::beginTransaction();
        try {
            $inventario = Inventario::create($request['inventario']);

            $this->archivosService->syncArchivos(
                $request,
                $inventario,
                'archivos',
                'fotografias',
                $uploadedFiles
            );

            if (!empty(data_get($request, 'inventario.subcategorias'))) {
                $inventario->subcategorias()->sync(
                    collect(data_get($request, 'inventario.subcategorias'))->pluck('id')
                );
            }

            if (!empty(data_get($request, 'inventario.categorias'))) {
                $inventario->categorias()->sync(
                    collect(data_get($request, 'inventario.categorias'))->pluck('id')
                );
            }

            DB::commit();
            return response($inventario, 201);
        } catch (\Exception) {
            DB::rollBack();
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['message' => 'Error al guardar el inventario'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventario $inventario)
    {
        $inventario->load('archivos');
        return response()->json($inventario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventarioRequest $request, Inventario $inventario)
    {
        $uploadedFiles = [];

        $inventario->update($request['inventario']);

        $this->archivosService->syncArchivos(
            $request,
            $inventario,
            'archivos',
            'fotografias',
            $uploadedFiles
        );

        $inventario->subcategorias()->sync(
            collect(data_get($request, 'inventario.subcategorias'))->pluck('id')
        );

        $inventario->categorias()->sync(
            collect(data_get($request, 'inventario.categorias'))->pluck('id')
        );

        return response()->json($inventario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        return response()->json(null, 204);
    }
}
