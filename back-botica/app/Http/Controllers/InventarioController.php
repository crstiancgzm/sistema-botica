<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Http\Requests\StoreInventarioRequest;
use App\Http\Requests\UpdateInventarioRequest;
use Illuminate\Http\Request;
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
        $query = Inventario::query();

        if ($request->filled('categoria_id')) {
            $query->whereHas('categorias', fn ($q) => $q->where('categorias.id', $request->categoria_id));
        }

        if ($request->filled('subcategoria_id')) {
            $query->whereHas('subcategorias', fn ($q) => $q->where('subcategorias.id', $request->subcategoria_id));
        }

        return $this->generateViewSetList(
            $request,
            $query,
            ['laboratorio_id', 'area_id', 'flag_blister', 'flag_disponible'],
            ['id', 'nombre', 'codigo'],
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

    public function relacionados(Inventario $inventario)
    {
        $categoriaIds    = $inventario->categorias()->pluck('categorias.id');
        $subcategoriaIds = $inventario->subcategorias()->pluck('subcategorias.id');

        if ($categoriaIds->isEmpty() && $subcategoriaIds->isEmpty()) {
            return response()->json([]);
        }

        $relacionados = Inventario::where('id', '!=', $inventario->id)
            ->where(function ($q) use ($categoriaIds, $subcategoriaIds) {
                if ($categoriaIds->isNotEmpty()) {
                    $q->whereHas('categorias', fn ($inner) => $inner->whereIn('categorias.id', $categoriaIds));
                }
                if ($subcategoriaIds->isNotEmpty()) {
                    $method = $categoriaIds->isNotEmpty() ? 'orWhereHas' : 'whereHas';
                    $q->$method('subcategorias', fn ($inner) => $inner->whereIn('subcategorias.id', $subcategoriaIds));
                }
            })
            ->with(['presentacion', 'archivos'])
            ->limit(8)
            ->get();

        return response()->json($relacionados);
    }
}
