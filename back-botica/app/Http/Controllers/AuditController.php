<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer:id,name')
            ->latest();

        if ($request->filled('modelo')) {
            $map = [
                'inventario' => \App\Models\Inventario::class,
                'venta'      => \App\Models\Venta::class,
                'caja'       => \App\Models\Caja::class,
                'usuario'    => \App\Models\User::class,
            ];
            $class = $map[strtolower($request->modelo)] ?? null;
            if ($class) {
                $query->where('subject_type', $class);
            }
        }

        if ($request->filled('evento')) {
            $query->where('event', $request->evento);
        }

        if ($request->filled('usuario_id')) {
            $query->where('causer_id', $request->usuario_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $perPage = (int) $request->input('rowsPerPage', 20);
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(fn (Activity $a) => [
            'id'          => $a->id,
            'descripcion' => $a->description,
            'evento'      => $a->event,
            'modelo'      => class_basename($a->subject_type),
            'modelo_id'   => $a->subject_id,
            'usuario'     => $a->causer?->name ?? 'Sistema',
            'cambios'     => $a->properties->toArray(),
            'fecha'       => $a->created_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'  => $items,
            'total' => $paginated->total(),
        ]);
    }
}
