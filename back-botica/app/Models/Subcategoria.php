<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    /** @use HasFactory<\Database\Factories\SubcategoriaFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    public function inventarios()
    {
        return $this->belongsToMany(Inventario::class);
    }

}
