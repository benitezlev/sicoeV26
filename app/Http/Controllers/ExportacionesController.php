<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportacionesController extends Controller
{
    public function exportarGrupo($id)
    {
        $grupo = Grupo::with([
            'curso',
            'plantel',
            'docente',
            'alumnos.calificaciones',
            'alumnos.asistencias'
        ])->findOrFail($id);

        // Fechas únicas de asistencia para la tabla
        $fechas = $grupo->asistencias()
            ->select('fecha')
            ->distinct()
            ->orderBy('fecha')
            ->pluck('fecha');

        return Pdf::loadView('pdf.exportar-grupo', compact('grupo', 'fechas'))
            ->setPaper('letter')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->download("Grupo_{$grupo->clave}.pdf");
    }

}
