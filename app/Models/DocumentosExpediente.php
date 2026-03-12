<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class DocumentosExpediente extends Model
{
     protected $table = 'documentos_expediente';

    protected $fillable = [
        'user_id',
        'expediente_id',
        'tipo',
        'archivo',
        'fecha_carga',
        'cargado_por',
        'estatus',
        'observaciones',
    ];

    // 📎 Relación con el usuario dueño del expediente
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 📁 Relación con el expediente
    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    // 👤 Relación con el usuario que cargó el documento
    public function cargador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }


    public function scopePendientes($query)
    {
        return $query->where('estatus', 'pendiente');
    }

    public function scopeValidado($query)
    {
        return $query->where('estatus', 'validado');
    }

    public function scopeObservado($query)
    {
        return $query->where('estatus', 'observado');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', strtoupper($tipo));
    }

    public function scopePorCURP($query, string $curp)
    {
        return $query->whereHas('user', function ($q) use ($curp) {
            $q->where('curp', strtoupper($curp));
        });
    }

    public function scopePorExpediente($query, int $expedienteId)
    {
        return $query->where('expediente_id', $expedienteId);
    }

    public function scopePorCiclo($query, string $ciclo)
    {
        return $query->whereHas('expediente', function ($q) use ($ciclo) {
            $q->where('ciclo', $ciclo);
        });
    }

    public function scopePorSede($query, string $sede)
    {
        return $query->whereHas('expediente', function ($q) use ($sede) {
            $q->where('sede', $sede);
        });
    }


}
