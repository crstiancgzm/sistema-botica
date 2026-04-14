<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioFactory> */
    use HasFactory;

    protected $with = ['proveedor', 'presentacion', 'laboratorio', 'categorias', 'subcategorias', 'archivos'];

    protected $fillable = [
        'nombre',
        'codigo',
        'cantidad',
        'flag_blister',
        'precio_blister',
        'flag_unidad',
        'precio_unidad',
        'stock_minimo',
        'precio_oficial',
        'fecha_vencimiento',
        'registro_sanitario',
        'lote',
        'ubicacion',
        'precio',
        'proveedor_id',
        'laboratorio_id',
        'presentacion_id',
        'area_id',
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

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class);
    }

    public function subcategorias()
    {
        return $this->belongsToMany(Subcategoria::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function archivos()
    {
        return $this->morphMany(Archivo::class, 'archivable');
    } 
}
