<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Plantel;
use Illuminate\Http\Request;
use App\Models\GrupoExpediente;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with(['curso', 'plantel', 'alumnos'])->paginate(15);
        return view('grupos.index', compact('grupos'));
    }

    public function create()
    {
        $planteles = Plantel::all();
        $cursos = Curso::all();
        return view('grupos.create', compact('planteles', 'cursos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'plantel_id' => 'required|exists:planteles,id',
            'curso_id' => 'required|exists:cursos,id',
            'periodo' => 'required|string|max:20',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'total_horas' => 'required|integer|min:1',
        ]);

        $grupo = Grupo::create([
            'nombre' => $request->nombre,
            'plantel_id' => $request->plantel_id,
            'curso_id' => $request->curso_id,
            'periodo' => $request->periodo,
            'estado' => 'activo',
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'total_horas' => $request->total_horas,
        ]);

        GrupoExpediente::create([
            'grupo_id' => $grupo->id,
            'tipo_documento' => 'expediente_inicial',
            'archivo' => null,
            'usuario_id' => auth()->id(),
        ]);

        return redirect()->route('grupos.index')->with('mensaje', 'Grupo y expediente inicial creados correctamente.');
    }


    public function show($id)
    {
        $grupo = Grupo::with(['curso', 'plantel', 'alumnos', 'expediente'])->findOrFail($id);



        // Traer todos los usuarios que son alumnos, ordenados únicamente por apellido paterno
        $alumnos = User::where('tipo', 'alumno')
            ->orderBy('paterno', 'asc')
            ->get();

        $docenteNombre = '---';

        if ($grupo->docente_id) {
            $response = Http::withToken(config('services.sad.token'))
                ->get(config('services.sad.url') . '/docentes/' . $grupo->docente_id);

            if ($response->successful()) {
                $docente = $response->json();

                if (isset($docente['data'])) {
                    $docente = $docente['data'];
                }

                $docenteNombre = $docente['name'] ?? '---';
            }
        }

        return view('grupos.show', compact('grupo', 'docenteNombre', 'alumnos'));
    }

    public function edit($id)
    {
        $grupo = Grupo::with(['curso', 'plantel', 'alumnos', 'expediente'])->findOrFail($id);
        $alumnos = User::where('tipo', 'alumno')->get();

        $docentes = ['data' => []];
        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url').'/docentes', [
                'plantel' => $grupo->plantel->name, // usar el campo correcto
                'page' => request()->get('page', 1),
                'per_page' => 50,
            ]);

        if ($response->successful()) {
            $docentes = $response->json();
        }

        return view('grupos.edit', compact('grupo', 'alumnos', 'docentes'));
    }

    public function update(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'periodo' => 'required|string|max:20',
            'estado' => 'required|in:activo,concluido,cancelado',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'total_horas' => 'nullable|integer|min:1',
        ]);

        $grupo->update($request->only(
            'nombre', 'periodo', 'estado',
            'fecha_inicio','fecha_fin','hora_inicio','hora_fin','total_horas'
        ));

        return redirect()->route('grupos.show', $grupo->id)->with('mensaje', 'Grupo actualizado correctamente.');
    }


    public function asignarDocente(Request $request, $id)
    {
        $request->validate([
            'docente_id' => 'required|integer|min:1',
        ]);

        $grupo = Grupo::with('plantel')->findOrFail($id);

        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url') . '/docentes/' . $request->docente_id);

        if ($response->successful()) {
            $docente = $response->json();

            // Si la API devuelve dentro de "data"
            if (isset($docente['data'])) {
                $docente = $docente['data'];
            }

            // Validar que exista la clave plantel
            if (!isset($docente['plantel'])) {
                return redirect()->back()->with('error', 'La API no devolvió información de plantel para este docente.');
            }

            $plantelDocente = strtolower(trim($docente['plantel']));
            $plantelGrupo   = strtolower(trim($grupo->plantel->name));

            if (!\Illuminate\Support\Str::contains($plantelDocente, $plantelGrupo)) {
                return redirect()->back()->with('error', 'El docente no pertenece al mismo plantel del grupo.');
            }

            $grupo->docente_id = $docente['id'];
            $grupo->save();

            \Log::info("Docente asignado a grupo", [
                'grupo_id'   => $grupo->id,
                'docente_id' => $docente['id'],
                'usuario_id' => auth()->id(),
            ]);

            return redirect()->route('grupos.show', $grupo->id)->with('mensaje', 'Docente asignado correctamente.');
        }

        return redirect()->back()->with('error', 'No se pudo asignar el docente.');
    }

    public function asignarDocenteForm($id)
    {
        $grupo = Grupo::with('plantel')->findOrFail($id);

        $docentes = ['data' => []];
        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url') . '/docentes', [
                'plantel' => $grupo->plantel->name, // usamos el campo correcto
                'page' => request()->get('page', 1),
                'per_page' => 50,
            ]);

        if ($response->successful()) {
            $docentes = $response->json();
        }

        return view('grupos.asignar-docente', compact('grupo', 'docentes'));
    }

    public function asignarAlumnos(Request $request, $id)
    {
        $request->validate([
            'alumnos' => 'required|array',
            'alumnos.*' => 'exists:users,id',
        ]);

        $grupo = Grupo::findOrFail($id);

        foreach ($request->alumnos as $userId) {
            $grupo->alumnos()->syncWithoutDetaching([
                $userId => ['fecha_asignacion' => now(), 'estado' => 'activo']
            ]);
        }

        return redirect()->back()->with('mensaje', 'Alumnos asignados correctamente.');
    }

    public function subirExpediente(Request $request, $id)
    {
        $request->validate([
            'tipo_documento' => 'required|string|max:100',
            'archivo' => 'required|file|mimes:pdf,xlsx',
        ]);

        $grupo = Grupo::findOrFail($id);
        $path = $request->file('archivo')->store('expedientes', 'public');

        GrupoExpediente::create([
            'grupo_id' => $grupo->id,
            'tipo_documento' => $request->tipo_documento,
            'archivo' => $path,
            'usuario_id' => auth()->id(),
        ]);

        return redirect()->back()->with('mensaje', 'Documento agregado al expediente.');
    }

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
