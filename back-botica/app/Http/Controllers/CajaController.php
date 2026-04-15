<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbrirCajaRequest;
use App\Http\Requests\CerrarCajaRequest;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Devuelve la caja actualmente abierta (o null si no hay ninguna).
     */
    public function estado()
    {
        $caja = Caja::abierta()
            ->with(['usuarioApertura:id,name'])
            ->whereDate('fecha', today())
            ->first();

        return response()->json($caja);
    }

    /**
     * Devuelve el historial de cajas del día actual.
     */
    public function hoy()
    {
        $cajas = Caja::whereDate('fecha', today())
            ->with(['usuarioApertura:id,name', 'usuarioCierre:id,name'])
            ->withCount('ventas')
            ->withSum('ventas', 'total')
            ->orderBy('hora_apertura')
            ->get();

        return response()->json($cajas);
    }

    /**
     * Abre una nueva caja para el turno solicitado.
     * Reglas:
     *   - Solo se puede tener UNA caja abierta a la vez.
     *   - Máximo 2 cajas por día (mañana y tarde) — garantizado por unique(fecha, turno).
     */
    public function abrir(AbrirCajaRequest $request)
    {

        $abierta = Caja::abierta()->whereDate('fecha', today())->first();
        if ($abierta) {
            return response()->json([
                'message' => 'Ya hay una caja abierta. Cerrala antes de abrir una nueva.',
                'caja'    => $abierta,
            ], 422);
        }

        $yaExiste = Caja::whereDate('fecha', today())
            ->where('turno', $request->turno)
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => "El turno \"{$request->turno}\" ya fue registrado hoy.",
            ], 422);
        }

        $caja = DB::transaction(function () use ($request) {
            return Caja::create([
                'fecha'               => today(),
                'turno'               => $request->turno,
                'estado'              => 'abierta',
                'monto_inicial'       => $request->input('monto_inicial', 0),
                'hora_apertura'       => now(),
                'usuario_apertura_id' => $request->user()->id,
            ]);
        });

        return response()->json($caja->load('usuarioApertura:id,name'), 201);
    }

    /**
     * Cierra la caja indicada.
     */
    public function cerrar(CerrarCajaRequest $request, Caja $caja)
    {
        if ($caja->estado === 'cerrada') {
            return response()->json(['message' => 'Esta caja ya está cerrada.'], 422);
        }

        $caja->update([
            'estado'           => 'cerrada',
            'monto_final'      => $request->input('monto_final'),
            'hora_cierre'      => now(),
            'observaciones'    => $request->input('observaciones'),
            'usuario_cierre_id' => $request->user()->id,
        ]);

        $caja->load(['usuarioApertura:id,name', 'usuarioCierre:id,name']);
        $caja->loadCount('ventas');
        $caja->loadSum('ventas', 'total');

        return response()->json($caja);
    }
}
