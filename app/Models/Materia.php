<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Materia extends Model implements HasMedia
{
    use InteractsWithMedia;

    use HasFactory;

    protected $fillable = [
        'nombre',
        'clave',
        'num_horas',
        'descripcion',
        'tipo',          // teorica, practica, mixta
        'activo',
    ];

    /**
     * Relación con cursos (formaciones iniciales, licenciaturas, etc.)
     */
    public function cursos()
    {
        return $this->belongsToMany(Curso::class)
                    ->withPivot('orden', 'semestre', 'creditos', 'obligatoria')
                    ->withTimestamps();
    }


    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

}
