<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Grupo;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;



class AsistenciaController extends Controller
{
    // Generar lista en PDF
    public function generarLista($grupoId)
    {
        // Ordenar alumnos por paterno desde la BD, acento-insensible
        $grupo = Grupo::with([
            'plantel',
            'curso',
            'alumnos' => function ($q) {
                // Compatible con PostgreSQL: Orden estándar por apellidos y nombre
                $q->orderBy('paterno', 'ASC')
                  ->orderBy('materno', 'ASC')
                  ->orderBy('nombre', 'ASC');
            },
        ])->findOrFail($grupoId);

        // El formato depende ahora exclusivamente del marcador manual (con auto-detección en UI)
        if ($grupo->formato_especial) {
            return $this->generarLista40Horas($grupo);
        }

        // Mes solicitado (default: actual)
        $mesObjetivo = request('mes', now()->format('Y-m'));
        $inicioMes   = Carbon::createFromFormat('Y-m', $mesObjetivo)->startOfMonth();
        $finMes      = Carbon::createFromFormat('Y-m', $mesObjetivo)->endOfMonth();

        // Días hábiles del grupo en todo su rango
        $diasRango = $grupo->diasHabilesEntreFechas();

        // Filtra solo los días que caen dentro del mes solicitado
        $diasDelMes = array_values(array_filter($diasRango, function ($d) use ($inicioMes, $finMes) {
            return $d['fecha']->between($inicioMes, $finMes, true);
        }));

        // Etiqueta institucional del mes
        $mes = $inicioMes->translatedFormat('F Y');

        $alumnos = $grupo->alumnos;
        // Bulk load calificaciones
        $calificaciones = \App\Models\Calificacion::where('grupo_id', $grupo->id)
            ->whereIn('user_id', $alumnos->pluck('id'))
            ->get()
            ->groupBy('user_id');

        // Bulk load asistencias para los días del mes
        $fechasMes = collect($diasDelMes)->map(fn($d) => $d['fecha']->format('Y-m-d'))->toArray();
        $asistencias = \App\Models\AsistenciaIndividual::where('grupo_id', $grupo->id)
            ->whereIn('user_id', $alumnos->pluck('id'))
            ->whereIn('fecha', $fechasMes)
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->keyBy(fn($i) => $i->fecha->format('Y-m-d')));

        foreach ($alumnos as $alumno) {
            $userCalifs = $calificaciones->get($alumno->id, collect());
            $alumno->nota_diagnostica = $userCalifs->firstWhere('unidad', 'diagnostica')->calificacion ?? '';
            $alumno->nota_final = $userCalifs->firstWhere('unidad', 'final')->calificacion ?? '';
            
            $alumno->mapa_asistencia = $asistencias->get($alumno->id, collect());

            // Retrocompatibilidad con propiedades esperadas por el formato horizontal antiguo (si aplica)
            $mapOld = ['L' => 'asistencia_l', 'M' => 'asistencia_m', 'M_2' => 'asistencia_mi', 'J' => 'asistencia_j', 'V' => 'asistencia_v'];
            // Nota: Este mapeo antiguo es impreciso para meses largos, solo se mantiene si el formato lo usa de forma fija
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('asistencias.formato_horizontal', [
                'grupo'      => $grupo,
                'mes'        => $mes,
                'diasDelMes' => $diasDelMes,
                'alumnos'    => $alumnos,
                'docente'    => $grupo->docente(),
                'estadisticas' => [
                    'hombres' => $alumnos->where('sexo', 'H')->count(),
                    'mujeres' => $alumnos->where('sexo', 'M')->count(),
                    'total' => $alumnos->count(),
                ],
                'sinDias'    => empty($diasDelMes) ? "Sin clases programadas en este mes dentro del periodo del grupo." : null,
            ])
            ->setPaper([0, 0, 612, 1008], 'landscape')
            ->download("lista_asistencia_{$grupo->id}_{$inicioMes->format('Y_m')}.pdf");
    }

    private function generarLista40Horas($grupo)
    {
        $alumnos = $grupo->alumnos;
        $diasFull = $grupo->diasHabilesEntreFechas();
        
        $feriados = [
            '2026-01-01', '2026-02-02', '2026-03-16', '2026-05-01', 
            '2026-09-16', '2026-11-16', '2026-12-25'
        ];

        $semanas = collect($diasFull)->groupBy(function($dia) {
            return $dia['fecha']->format('o-W');
        })->map(function($dias, $key) use ($feriados) {
            return [
                'identificador' => $key,
                'dias' => $dias->sortBy('fecha')->values(),
                'es_feriado' => function($fecha) use ($feriados) {
                    return in_array($fecha->format('Y-m-d'), $feriados);
                }
            ];
        })->values();

        // Bulk load calificaciones
        $calificaciones = \App\Models\Calificacion::where('grupo_id', $grupo->id)
            ->whereIn('user_id', $alumnos->pluck('id'))
            ->get()
            ->groupBy('user_id');

        // Bulk load asistencias individuales (incluyendo justificaciones)
        $asistencias = \App\Models\AsistenciaIndividual::where('grupo_id', $grupo->id)
            ->whereIn('user_id', $alumnos->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->keyBy(fn($i) => $i->fecha->format('Y-m-d')));

        foreach ($alumnos as $alumno) {
            $userCalifs = $calificaciones->get($alumno->id, collect());
            $alumno->nota_diagnostica = $userCalifs->firstWhere('unidad', 'diagnostica')->calificacion ?? '';
            $alumno->nota_final = $userCalifs->firstWhere('unidad', 'final')->calificacion ?? '';
            
            // Inyectamos el mapa de asistencia pre-cargado para acceso instantáneo
            $alumno->mapa_asistencia = $asistencias->get($alumno->id, collect());
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('asistencias.formato_40hrs', [
                'grupo'   => $grupo,
                'alumnos' => $alumnos,
                'semanas' => $semanas,
                'docente' => $grupo->docente(),
                'estadisticas' => [
                    'hombres' => $alumnos->where('sexo', 'H')->count(),
                    'mujeres' => $alumnos->where('sexo', 'M')->count(),
                    'total' => $alumnos->count(),
                ],
            ])
            ->setPaper('letter', 'landscape')
            ->download("asistencia_extendida_{$grupo->id}.pdf");
    }

    // Subir lista escaneada
    public function subirLista(Request $request, $grupoId) {
        $path = $request->file('archivo')->store('asistencias');
        $asistencia = Asistencia::create([
            'grupo_id' => $grupoId,
            'plantel_id' => Grupo::find($grupoId)->plantel_id,
            'archivo' => $path,
            'estado' => 'pendiente',
            'subido_at' => now(),
            'fecha_inicio_real' => now(),
        ]);
        return back()->with('mensaje','Lista subida correctamente. Tienes 3 horas para validarla.');
    }

    // Validar lista
    public function validarLista($id) {
        $asistencia = Asistencia::findOrFail($id);
        $limite = $asistencia->subido_at->addHours(3);

        if (now()->greaterThan($limite)) {
            $asistencia->estado = 'expirado';
            $asistencia->save();
            return back()->with('error','El periodo de validación ha expirado.');
        }

        $asistencia->estado = 'validado';
        $asistencia->validado_at = now();
        $asistencia->validado_por = auth()->id();
        $asistencia->save();

        return back()->with('mensaje','Lista validada correctamente.');
    }

}
