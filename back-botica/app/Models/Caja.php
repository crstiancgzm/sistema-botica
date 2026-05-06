<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Caja extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'turno', 'estado', 'monto_inicial', 'monto_final',
                'hora_apertura', 'hora_cierre', 'observaciones',
                'usuario_apertura_id', 'usuario_cierre_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Caja {$eventName}");
    }

    protected $fillable = [
        'fecha',
        'turno',
        'estado',
        'monto_inicial',
        'monto_final',
        'hora_apertura',
        'hora_cierre',
        'observaciones',
        'usuario_apertura_id',
        'usuario_cierre_id',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'hora_apertura' => 'datetime',
        'hora_cierre'   => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final'   => 'decimal:2',
    ];

    public function usuarioApertura()
    {
        return $this->belongsTo(User::class, 'usuario_apertura_id');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(User::class, 'usuario_cierre_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }
}
