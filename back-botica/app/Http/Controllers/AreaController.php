<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use Symfony\Component\HttpFoundation\Request;

class AreaController extends Controller
{

    public function index(Request $request)
    {
       return $this->generateViewSetList(
            $request,
            Area::query(),
            [],
            ['id','nombre'],
            ['id']
        ); 
    }

    public function store(StoreAreaRequest $request)
    {
        return response(Area::create($request['area']), 201);
    }

    public function show(Area $area)
    {
        return response()->json($area);
    }

    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->update($request['area']);
        return response()->json($area);
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return response()->json(null, 204);
    }
}
