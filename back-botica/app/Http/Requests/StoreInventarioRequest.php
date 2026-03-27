<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inventario.nombre' => 'required|string|max:255',
            'inventario.codigo' => 'required|string|max:255',
            'inventario.cantidad' => 'required|integer',
            'inventario.flag_blister' => 'nullable|boolean',
            'inventario.flag_unidad' => 'nullable|boolean',
            'inventario.precio_blister' => 'required_if:inventario.flag_blister,1',
            'inventario.cantidad_blister' => 'required_if:inventario.flag_blister,1',
            'inventario.precio_unidad' => 'required_if:inventario.flag_unidad,1',
            'inventario.stock_minimo' => 'nullable|string|max:255',
            'inventario.precio_oficial' => 'required|string|max:255',
            'inventario.fecha_vencimiento' => 'required|date',
            'inventario.registro_sanitario' => 'nullable|string|max:255',
            'inventario.lote' => 'nullable|string|max:255',
            'inventario.ubicacion' => 'nullable|string|max:255',
            'inventario.precio' => 'nullable|numeric',
            'inventario.proveedor_id' => 'nullable|exists:proveedors,id',
            'inventario.laboratorio_id' => 'nullable|exists:laboratorios,id',
            'inventario.presentacion_id' => 'required|exists:presentacions,id',
            'inventario.area_id' => 'required|exists:areas,id',
            
        ];
    }
}