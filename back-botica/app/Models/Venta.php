<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'cliente_nombre',
        'cliente_dni',
        'tipo_comprobante',
        'metodo_pago',
        'total',
        'monto_recibido',
        'vuelto',
    ];

    protected $casts = [
        'total'          => 'decimal:2',
        'monto_recibido' => 'decimal:2',
        'vuelto'         => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(VentaItem::class);
    }
}
