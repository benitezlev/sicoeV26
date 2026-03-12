<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CursoController extends Controller
{
    /**
     * Exportar el listado de cursos en formato PDF institucional.
     */
    public function exportarPDF()
    {
        $cursos = Curso::orderBy('tipo')->get();

        $pdf = Pdf::loadView('pdf.cursos', compact('cursos'))
            ->setPaper('letter')
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf->download('Listado_Cursos.pdf');
    }
}
