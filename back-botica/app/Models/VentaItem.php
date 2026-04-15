<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'inventario_id',
        'tipo_venta',
        'cantidad',
        'precio_venta',
        'subtotal',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }
}
