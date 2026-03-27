<?php

namespace App\Http\Controllers;

use App\Models\Subcategoria;
use App\Http\Requests\StoreSubcategoriaRequest;
use App\Http\Requests\UpdateSubcategoriaRequest;
use Symfony\Component\HttpFoundation\Request;

class SubcategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Subcategoria::query(),
            [],
            ['id','nombre'],
            ['id']
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubcategoriaRequest $request)
    {
        return response(Subcategoria::create($request['subcategoria']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategoria $subcategoria)
    {
        return response()->json($subcategoria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubcategoriaRequest $request, Subcategoria $subcategoria)
    {
        $subcategoria->update($request['subcategoria']);
        return response()->json($subcategoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategoria $subcategoria)
    {
        $subcategoria->delete();
        return response()->json(null, 204);
    }
}
