<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_nombre'        => 'nullable|string|max:200',
            'cliente_dni'           => 'nullable|string|size:8',
            'tipo_comprobante'      => 'required|in:boleta,factura,sin_comprobante',
            'metodo_pago'           => 'required|in:efectivo,tarjeta,transferencia',
            'items'                 => 'required|array|min:1',
            'items.*.inventario_id' => 'required|integer|exists:inventarios,id',
            'items.*.tipo_venta'    => 'required|in:unidad,blister',
            'items.*.cantidad'      => 'required|integer|min:1',
            'items.*.precio_venta'  => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_comprobante.required' => 'El tipo de comprobante es obligatorio.',
            'tipo_comprobante.in'       => 'Comprobante inválido.',
            'metodo_pago.required'      => 'El método de pago es obligatorio.',
            'metodo_pago.in'            => 'Método de pago inválido.',
            'items.required'            => 'La venta debe tener al menos un producto.',
            'items.min'                 => 'La venta debe tener al menos un producto.',
            'items.*.inventario_id.exists'   => 'Uno o más productos no existen en el inventario.',
            'items.*.tipo_venta.in'          => 'Tipo de venta inválido.',
            'items.*.cantidad.min'           => 'La cantidad debe ser al menos 1.',
            'items.*.precio_venta.min'       => 'El precio no puede ser negativo.',
        ];
    }
}
