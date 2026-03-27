<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    /** @use HasFactory<\Database\Factories\ProveedorFactory> */
    use HasFactory;

    protected $fillable = [
        'ruc',
        'razon_social',
        'direccion',
        'telefono',
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

}
