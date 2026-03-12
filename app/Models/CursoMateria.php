<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoMateria extends Model
{
    use HasFactory;

    protected $table = 'curso_materia';

    protected $fillable = [
        'curso_id',
        'materia_id',
        'orden', // posición de la materia dentro de la tira académica
        'orden',
        'semestre',
        'creditos',
        'obligatoria',
    ];

    /**
     * Relación con Curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Relación con Materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeDelSemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }


}
