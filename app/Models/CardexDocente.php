<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardexDocente extends Model
{
    protected $table = 'cardex_docentes';

    protected $fillable = [
        'docente_id',
        'grado_academico',
        'especialidad',
        'experiencia_docente',
        'certificaciones',
        'formacion_complementaria',
        'observaciones',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

}
