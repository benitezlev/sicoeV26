<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ProfesorController extends Controller
{
    /**
     * Listado de profesores
     */


    public function index(Request $request)
    {
        // Captura el parámetro page de la URL (?page=2)
        $page = $request->query('page', 1);

        // Llama al endpoint con query params
        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url').'/docentes', [
                'page'     => $page,
                'per_page' => 20, // opcional, si tu API lo soporta
            ]);

        $docentes = $response->json();

        return view('docentes.index', compact('docentes'));
    }

    /**
     * Consultar profesor externo y sincronizar
     */
    public function sincronizar(Request $request)
    {
        $request->validate([
            'profesor_id' => 'required|integer',
        ]);

        $response = Http::withToken(config('services.profesores.token'))
            ->get(config('services.profesores.url') . '/profesores/' . $request->profesor_id);

        if ($response->successful()) {
            $datos = $response->json();

            $profesor = Profesor::updateOrCreate(
                ['externo_id' => $datos['id']],
                [
                    'nombre' => $datos['nombre'],
                    'curp' => $datos['curp'],
                    'plantel_id' => $datos['plantel_id'],
                    'estatus' => $datos['estatus'],
                ]
            );

            Log::info("Profesor sincronizado", [
                'profesor_id' => $profesor->id,
                'externo_id' => $datos['id'],
            ]);

            return redirect()->back()->with('mensaje', 'Profesor sincronizado correctamente.');
        }

        return redirect()->back()->with('error', 'No se pudo sincronizar el profesor.');
    }

    /**
     * Mostrar detalle de un profesor
     */
    public function show($id)
    {
        $profesor = Profesor::with('plantel', 'grupos')->findOrFail($id);
        return view('docentes.show', compact('profesor'));
    }
}
