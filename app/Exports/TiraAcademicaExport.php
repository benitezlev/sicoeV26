<?php

namespace App\Exports;

use App\Models\Curso;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;


class TiraAcademicaExport implements FromArray
{
    protected $cursoId;

    public function __construct($cursoId)
    {
        $this->cursoId = $cursoId;
    }

    public function array(): array
    {
        $curso = Curso::with('materias')->findOrFail($this->cursoId);

        $data[] = ['Orden', 'Materia', 'Horas', 'Semestre', 'Créditos', 'Obligatoria'];

        foreach ($curso->materias as $materia) {
            $data[] = [
                $materia->pivot->orden,
                $materia->nombre,
                $materia->num_horas,
                $materia->pivot->semestre,
                $materia->pivot->creditos,
                $materia->pivot->obligatoria ? 'Sí' : 'No',
            ];
        }

        return $data;
    }
}

