<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Http\Requests\StoreLaboratorioRequest;
use App\Http\Requests\UpdateLaboratorioRequest;
use Symfony\Component\HttpFoundation\Request;

class LaboratorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Laboratorio::query(),
            [],
            ['id','nombre'],
            ['id']
        ); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLaboratorioRequest $request)
    {
        return response(Laboratorio::create($request['laboratorio']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Laboratorio $laboratorio)
    {
        return response()->json($laboratorio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLaboratorioRequest $request, Laboratorio $laboratorio)
    {
        $laboratorio->update($request['laboratorio']);
        return response()->json($laboratorio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laboratorio $laboratorio)
    {
        $laboratorio->delete();
        return response()->json(null, 204);
    }
}
