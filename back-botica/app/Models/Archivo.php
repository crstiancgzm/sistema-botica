<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $fillable = [
        'archivable_id',
        'archivable_type',
        'nombre',
        'url',
        'tipo_1',
        'tipo_2',
        'aux_1',
        'aux_2',
    ];

    public function archivable()
    {
        return $this->morphTo();
    }
}
