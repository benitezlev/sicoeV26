<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = "cursos";

    protected $fillable = [
        'identificador',
        'nombre',
        'tipo',
        'num_horas',
        'categoria',
        'descripcion',
    ];

    public function materias()
    {
        return $this->belongsToMany(Materia::class)
                    ->withPivot('orden', 'semestre', 'creditos', 'obligatoria')
                    ->withTimestamps();
    }


}
