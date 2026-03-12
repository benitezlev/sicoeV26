<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoAlumno extends Model
{
    protected $table = 'grupo_alumno';

    protected $fillable = [
        'grupo_id', 'alumno_id', 'fecha_asignacion', 'estado'
    ];

}
