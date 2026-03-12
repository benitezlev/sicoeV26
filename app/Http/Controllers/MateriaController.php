<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;

class MateriaController extends Controller
{
    /**
     * Listar todas las materias
     */
    public function index()
    {
        $materias = Materia::orderBy('nombre')->paginate(15);
        return view('materias.index', compact('materias'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('materias.create');
    }

    /**
     * Guardar nueva materia
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:50|unique:materias,clave',
            'num_horas' => 'nullable|integer|min:0',
            'tipo' => 'required|in:teorica,practica,mixta',
            'activo' => 'boolean',
        ]);

        $materia = Materia::create([
            'nombre' => $request->nombre,
            'clave' => $request->clave,
            'num_horas' => $request->num_horas,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()->route('materias.index')->with('mensaje', 'Materia registrada correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $materia = Materia::findOrFail($id);
        return view('materias.edit', compact('materia'));
    }

    /**
     * Actualizar materia
     */
    public function update(Request $request, $id)
    {
        $materia = Materia::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:50|unique:materias,clave,' . $materia->id,
            'num_horas' => 'nullable|integer|min:0',
            'tipo' => 'required|in:teorica,practica,mixta',
            'activo' => 'boolean',
        ]);

        $materia->update([
            'nombre' => $request->nombre,
            'clave' => $request->clave,
            'num_horas' => $request->num_horas,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()->route('materias.index')->with('mensaje', 'Materia actualizada correctamente.');
    }

    /**
     * Eliminar materia
     */
    public function destroy($id)
    {
        $materia = Materia::findOrFail($id);
        $materia->delete();

        return redirect()->route('materias.index')->with('mensaje', 'Materia eliminada correctamente.');
    }
}
