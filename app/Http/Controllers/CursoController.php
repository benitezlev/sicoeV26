<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::orderBy('nombre')->paginate(20);
        return view('admin.cursos.index', compact('cursos'));
    }

    public function create()
    {
        return view('admin.cursos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'identificador' => 'required|unique:cursos',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'num_horas' => 'required|integer|min:1',
        ]);

        Curso::create($request->only('identificador', 'nombre', 'tipo', 'num_horas'));

        return redirect()->route('cursos.index')->with('success', 'Curso registrado correctamente.');
    }

    public function show(Curso $curso)
    {
        return view('admin.cursos.show', compact('curso'));
    }

    public function edit(Curso $curso)
    {
        return view('admin.cursos.edit', compact('curso'));
    }

    public function update(Request $request, Curso $curso)
    {
        $request->validate([
            'identificador' => 'required|unique:cursos,identificador,' . $curso->id,
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'num_horas' => 'required|integer|min:1',
        ]);

        $curso->update($request->only('identificador', 'nombre', 'tipo', 'num_horas'));

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso)
    {
        $curso->delete();
        return redirect()->route('cursos.index')->with('success', 'Curso eliminado.');
    }

    public function exportarPDF()
    {
        $cursos = Curso::orderBy('tipo')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.cursos', compact('cursos'))
            ->setPaper('letter')
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf->download('Listado_Cursos.pdf');
    }

}
