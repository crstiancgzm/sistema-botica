<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use App\Http\Requests\StorePresentacionRequest;
use App\Http\Requests\UpdatePresentacionRequest;
use Symfony\Component\HttpFoundation\Request;

class PresentacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
     {
        return $this->generateViewSetList(
            $request,
            Presentacion::query(),
            ['nombre'],
            ['id','nombre'],
            ['id']
        ); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePresentacionRequest $request)
    {
        return response(Presentacion::create($request['presentacion']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Presentacion $presentacione)
    {
        return response()->json($presentacione);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePresentacionRequest $request, Presentacion $presentacione)
    {
        $presentacione->update($request['presentacion']);
        return response()->json($presentacione);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presentacion $presentacione)
    {
        $presentacione->delete();
        return response()->json(null, 204);
    }
}
