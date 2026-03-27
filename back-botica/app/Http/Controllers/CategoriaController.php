<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Symfony\Component\HttpFoundation\Request;

class CategoriaController extends Controller
{

    public function index(Request $request)
    {
         return $this->generateViewSetList(
                $request,
                Categoria::query(),
                [],
                ['id','nombre'],
                ['id']
          );
    }

    public function store(StoreCategoriaRequest $request)
    {
        return response(Categoria::create($request['categoria']), 201);
    }

    public function show(Categoria $categoria)
    {
        return response()->json($categoria);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request['categoria']);
        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return response()->json(null, 204);
    }
}
