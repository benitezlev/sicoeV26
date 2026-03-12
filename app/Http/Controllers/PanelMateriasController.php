<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TiraAcademicaExport;
use Illuminate\Support\Facades\Log;



class PanelMateriasController extends Controller
{
    /**
     * Mostrar listado de cursos y tira académica seleccionada
     */
    public function index(Request $request)
    {
        $cursos = Curso::all();
        $cursoSeleccionado = null;

        if ($request->has('curso_id')) {
            $cursoSeleccionado = Curso::with('materias')->find($request->curso_id);
        }

        return view('panel.materias.index', compact('cursos', 'cursoSeleccionado'));
    }

    /**
     * Vista de edición inline de materias de un curso
     */
    public function edit($cursoId)
    {
        $curso = Curso::with('materias')->findOrFail($cursoId);
        return view('panel.materias.edit', compact('curso'));
    }

    /**
     * Actualizar materias de un curso (orden, semestre, créditos, obligatoriedad, horas)
     */
    public function update(Request $request, $cursoId)
    {
        $curso = Curso::findOrFail($cursoId);

        foreach ($request->materias as $materiaId => $datos) {
            // Actualizar pivote
            $curso->materias()->updateExistingPivot($materiaId, [
                'orden'       => $datos['orden'] ?? null,
                'semestre'    => $datos['semestre'] ?? null,
                'creditos'    => $datos['creditos'] ?? null,
                'obligatoria' => isset($datos['obligatoria']) ? true : false,
            ]);

            // Actualizar horas de la materia
            Materia::where('id', $materiaId)->update([
                'num_horas' => $datos['num_horas'] ?? null,
            ]);
        }

        return redirect()->back()->with('mensaje', 'Materias actualizadas correctamente.');
    }

    /**
     * Formulario para agregar nueva materia a un curso
     */
    public function create($cursoId)
    {
        $curso = Curso::findOrFail($cursoId);
        $materiasDisponibles = Materia::whereNotIn('id', $curso->materias->pluck('id'))->get();

        return view('panel.materias.add', compact('curso', 'materiasDisponibles'));
    }

    /**
     * Guardar nueva materia asignada a un curso
     */
    public function store(Request $request, $cursoId)
{
    $curso = Curso::findOrFail($cursoId);
    $materiaId = $request->materia_id;

    try {
        // Verifica si ya está ligada
        if ($curso->materias()->where('materia_id', $materiaId)->exists()) {
            Log::warning("Intento de duplicar materia en curso", [
                'curso_id' => $cursoId,
                'materia_id' => $materiaId,
            ]);
            return redirect()->back()->with('error', 'La materia ya está asignada a este curso.');
        }

        // Intentar attach
        $curso->materias()->attach($materiaId, [
            'orden'       => $request->orden ?? null,
            'semestre'    => $request->semestre ?? null,
            'creditos'    => $request->creditos ?? null,
            'obligatoria' => $request->has('obligatoria'),
        ]);

        Log::info("Materia ligada correctamente", [
            'curso_id' => $cursoId,
            'materia_id' => $materiaId,
        ]);

        return redirect()->route('panel.materias', ['curso_id' => $cursoId])
                         ->with('mensaje', 'Materia ligada correctamente.');
    } catch (\Exception $e) {
        Log::error("Error al ligar materia", [
            'curso_id' => $cursoId,
            'materia_id' => $materiaId,
            'error' => $e->getMessage(),
        ]);

        return redirect()->back()->with('error', 'No se pudo ligar la materia: ' . $e->getMessage());
    }
}

    /**
     * Eliminar materia de un curso
     */
    public function destroy($cursoId, $materiaId)
    {
        $curso = Curso::findOrFail($cursoId);
        $curso->materias()->detach($materiaId);

        return redirect()->route('panel.materias', ['curso_id' => $cursoId])
                         ->with('mensaje', 'Materia eliminada correctamente.');
    }

     // Exportar en PDF
    public function exportPdf($cursoId)
    {
        $curso = Curso::with('materias')->findOrFail($cursoId);

        $pdf = Pdf::loadView('panel.materias.export-pdf', compact('curso'));
        return $pdf->download('tira_academica_'.$curso->id.'.pdf');
    }

    // Exportar en Excel
    public function exportExcel($cursoId)
    {
        return Excel::download(new TiraAcademicaExport($cursoId), 'tira_academica_'.$cursoId.'.xlsx');
    }

}
