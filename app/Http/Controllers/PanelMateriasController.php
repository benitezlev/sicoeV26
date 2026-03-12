<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TiraAcademicaExport;

class PanelMateriasController extends Controller
{
    /**
     * Exportar la tira académica del curso en formato PDF.
     */
    public function exportPdf($cursoId)
    {
        $curso = Curso::with('materias')->findOrFail($cursoId);

        $pdf = Pdf::loadView('panel.materias.export-pdf', compact('curso'));
        return $pdf->download('tira_academica_'.$curso->id.'.pdf');
    }

    /**
     * Exportar la tira académica del curso en formato Excel.
     */
    public function exportExcel($cursoId)
    {
        return Excel::download(new TiraAcademicaExport($cursoId), 'tira_academica_'.$cursoId.'.xlsx');
    }
}
