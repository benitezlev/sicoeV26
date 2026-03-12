<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Docente extends Model
{
     protected $fillable = [

        'name',
        'cuid',
        'sexo',
        'curp',
        'cuip',
        'tel',
        'email',
        'cve_servidor',
        'adscrip',
        'plantel',
        'cargo',
        'puesto',
        'ingreso',
        'grado_estudio',
        'acredita',
        'cedula',
        'campo_estudio',
        'status',
    ];

    protected $dates = [
        'ingreso'
    ];

        public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

}
