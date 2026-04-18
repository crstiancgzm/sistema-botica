<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Registra una nueva venta:
     *  - Requiere caja abierta.
     *  - Valida stock por ítem.
     *  - Descuenta inventario.
     *  - Todo en una transacción.
     */
    public function store(StoreVentaRequest $request)
    {

        $caja = Caja::abierta()->whereDate('fecha', today())->first();
        if (!$caja) {
            return response()->json(['message' => 'No hay una caja abierta. Abrí la caja antes de registrar ventas.'], 422);
        }

        $venta = DB::transaction(function () use ($request, $caja) {
            $total = 0;

            // Validar stock y calcular total antes de tocar nada
            $itemsData = [];
            foreach ($request->items as $item) {
                $inventario = Inventario::lockForUpdate()->find($item['inventario_id']);

                $unidadesConsumidas = $item['tipo_venta'] === 'blister'
                    ? $item['cantidad'] * ($inventario->cantidad_blister ?? 1)
                    : $item['cantidad'];

                if ($inventario->cantidad < $unidadesConsumidas) {
                    throw new \Exception("Stock insuficiente para \"{$inventario->nombre}\".");
                }

                $subtotal  = round($item['precio_venta'] * $item['cantidad'], 2);
                $total    += $subtotal;

                $itemsData[] = [
                    'inventario'         => $inventario,
                    'unidadesConsumidas' => $unidadesConsumidas,
                    'item'               => $item,
                    'subtotal'           => $subtotal,
                ];
            }

            $vuelto = null;
            if ($request->metodo_pago === 'efectivo' && $request->filled('monto_recibido')) {
                $vuelto = round($request->monto_recibido - $total, 2);
            }

            $venta = Venta::create([
                'caja_id'          => $caja->id,
                'usuario_id'       => $request->user()->id,
                'cliente_nombre'   => $request->input('cliente_nombre', 'Consumidor Final'),
                'cliente_dni'      => $request->input('cliente_dni'),
                'tipo_comprobante' => $request->tipo_comprobante,
                'metodo_pago'      => $request->metodo_pago,
                'total'            => $total,
                'monto_recibido'   => $request->input('monto_recibido'),
                'vuelto'           => $vuelto,
            ]);

            foreach ($itemsData as $data) {
                $venta->items()->create([
                    'inventario_id' => $data['item']['inventario_id'],
                    'tipo_venta'    => $data['item']['tipo_venta'],
                    'cantidad'      => $data['item']['cantidad'],
                    'precio_venta'  => $data['item']['precio_venta'],
                    'subtotal'      => $data['subtotal'],
                ]);

                $data['inventario']->decrement('cantidad', $data['unidadesConsumidas']);

                if ($data['inventario']->fresh()->cantidad <= 0) {
                    $data['inventario']->update(['flag_disponible' => false]);
                }
            }

            return $venta->load('items.inventario:id,nombre,codigo');
        });

        $venta->load(['items.inventario:id,nombre', 'usuario:id,name', 'caja:id,turno']);

        $pdf = Pdf::loadView('boleta', compact('venta'))
            ->setOptions(['isRemoteEnabled' => true])
            ->setPaper([0, 0, 226.77, 700], 'portrait'); // 80mm de ancho

        return response()->json([
            'venta' => $venta,
            'pdf'   => base64_encode($pdf->output()),
        ], 201);
    }
}
