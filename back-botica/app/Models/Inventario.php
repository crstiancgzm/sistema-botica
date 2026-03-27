<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioFactory> */
    use HasFactory;

    protected $with = ['proveedor', 'presentacion', 'laboratorio'];

    protected $fillable = [
        'nombre',
        'codigo',
        'cantidad',
        'presentacion',
        'flag_blister',
        'precio_blister',
        'flag_unidad',
        'precio_unidad',
        'stock_minimo',
        'precio_oficial',
        'fecha_vencimiento',
        'registro_sanitario',
        'lote',
        'laboratorio',
        'ubicacion',
        'precio',
        'proveedor_id',
        'proveedor',
        'laboratorio_id',
        'presentacion_id'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class);
    }
}
