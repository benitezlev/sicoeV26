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

    public function exportarActa(Request $request)
    {
        $grupo = Grupo::with('plantel')->findOrFail($request->grupo_id);
        $materia = \App\Models\Materia::findOrFail($request->materia_id);
        $unidad = $request->unidad;

        $alumnos = $grupo->alumnos()->orderBy('paterno')->get();
        
        $calificaciones = \App\Models\Calificacion::where('grupo_id', $grupo->id)
            ->where('materia_id', $materia->id)
            ->where('unidad', $unidad)
            ->get();

        return Pdf::loadView('pdf.acta-calificaciones', compact('grupo', 'materia', 'unidad', 'alumnos', 'calificaciones'))
            ->setPaper('letter', 'portrait')
            ->download("Acta_{$grupo->id}_U{$unidad}.pdf");
    }
}
