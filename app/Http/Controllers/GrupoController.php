<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Vista de métricas y estadísticas del grupo.
     * Se mantiene en controlador por la lógica de conteo y vista específica.
     */
    public function metricas($id)
    {
        $grupo = Grupo::with(['curso', 'alumnos', 'expediente'])->findOrFail($id);

        $totalAlumnos = $grupo->alumnos->count();
        $documentos = $grupo->expediente->count();
        $cursosProgramados = Curso::count();
        $cursosImpartidos = Grupo::distinct('curso_id')->count('curso_id');

        return view('grupos.metricas', compact(
            'grupo', 'totalAlumnos', 'documentos', 'cursosProgramados', 'cursosImpartidos'
        ));
    }
}
