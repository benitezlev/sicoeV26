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

        // Si es curso con formato especial (marcado manual) o corto (<= 40 hrs)
        if ($grupo->formato_especial || $grupo->total_horas <= 40) {
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

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('asistencias.formato_horizontal', [
                'grupo'      => $grupo,
                'mes'        => $mes,
                'diasDelMes' => $diasDelMes,
                // Ya vienen ordenados desde la consulta:
                'alumnos'    => $grupo->alumnos,
                'sinDias'    => empty($diasDelMes) ? "Sin clases programadas en este mes dentro del periodo del grupo." : null,
            ])
            ->setPaper([0, 0, 612, 1008], 'landscape')
            ->download("lista_asistencia_{$grupo->id}_{$inicioMes->format('Y_m')}.pdf");
    }

    private function generarLista40Horas($grupo)
    {
        $alumnos = $grupo->alumnos;
        
        // Obtener calificaciones para diagnóstica y final
        foreach ($alumnos as $alumno) {
            $alumno->nota_diagnostica = \App\Models\Calificacion::where('grupo_id', $grupo->id)
                ->where('user_id', $alumno->id)
                ->where('unidad', 'diagnostica')
                ->value('calificacion') ?? '';
            
            $alumno->nota_final = \App\Models\Calificacion::where('grupo_id', $grupo->id)
                ->where('user_id', $alumno->id)
                ->where('unidad', 'final')
                ->value('calificacion') ?? '';
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('asistencias.formato_40hrs', [
                'grupo'   => $grupo,
                'alumnos' => $alumnos,
                'docente' => $grupo->docente(),
            ])
            ->setPaper('letter', 'portrait')
            ->download("asistencia_40hrs_{$grupo->id}.pdf");
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
