<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratorioFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

}
