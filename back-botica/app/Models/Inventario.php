<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre', 'codigo', 'cantidad', 'precio', 'precio_unidad',
                'precio_blister', 'cantidad_blister', 'stock_minimo',
                'flag_disponible', 'flag_blister', 'flag_unidad',
                'proveedor_id', 'laboratorio_id', 'area_id',
                'fecha_vencimiento', 'lote',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Inventario {$eventName}");
    }

    protected $with = ['proveedor', 'presentacion', 'laboratorio', 'categorias', 'subcategorias', 'archivos','area'];

    protected $fillable = [
        'nombre',
        'nombre_principio_activo',
        'codigo',
        'cantidad',
        'flag_blister',
        'precio_blister',
        'cantidad_blister',
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
        'flag_disponible',
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
