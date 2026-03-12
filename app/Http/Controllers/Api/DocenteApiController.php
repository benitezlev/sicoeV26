<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Http\Resources\DocenteResource;
use App\Http\Resources\DocenteCollection;
use Illuminate\Http\Request;

class DocenteApiController extends Controller
{
    public function index()
    {
        $docentes = Docente::paginate(10);
        return new DocenteCollection($docentes);
    }

    public function show($id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json([
                'error' => 'Docente no encontrado',
                'id'    => $id
            ], 404);
        }

        return new DocenteResource($docente);
    }

    public function store(Request $request)
    {
        $docente = Docente::create($request->all());
        return new DocenteResource($docente);
    }

    public function update(Request $request, $id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json([
                'error' => 'Docente no encontrado',
                'id'    => $id
            ], 404);
        }

        $docente->update($request->all());
        return new DocenteResource($docente);
    }

    public function destroy($id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json([
                'error' => 'Docente no encontrado',
                'id'    => $id
            ], 404);
        }

        $docente->delete();
        return response()->json(['message' => 'Docente eliminado correctamente']);
    }
}
