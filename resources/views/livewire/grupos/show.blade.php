<?php

use function Livewire\Volt\{state, computed, layout, mount, usesFileUploads};
use App\Models\Grupo;
use App\Models\User;
use App\Models\GrupoExpediente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();

layout('layouts.app');

state([
    'grupoId' => null,
    'docente' => null,
    'searchAlumnos' => '',
    'selectedAlumnos' => [],
    'tipoDocumento' => '',
    'archivo' => null,
    'docentesAPI' => [],
    'searchDocente' => '',
    'escaneoAsistencia' => null,
]);

mount(function ($grupo) {
    $this->grupoId = is_numeric($grupo) ? $grupo : $grupo->id;
    $this->cargarDocente();
});

$grupo = computed(function () {
    return Grupo::with(['plantel', 'curso', 'alumnos', 'expediente'])->findOrFail($this->grupoId);
});

$cargarDocente = function () {
    $grupo = Grupo::find($this->grupoId);
    if ($grupo && $grupo->docente_id) {
        // En un entorno real asíncrono
        try {
            $response = Http::withToken(config('services.sad.token'))
                ->timeout(3)
                ->get(config('services.sad.url') . '/docentes/' . $grupo->docente_id);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->docente = $data['data'] ?? $data;
            }
        } catch (\Exception $e) {
            // Silencioso o manejo local
            $this->docente = ['name' => 'Docente SAD (' . $grupo->docente_id . ')', 'email' => 'docente@sad.com', 'cargo' => 'N/A'];
        }
    }
};

$alumnosDisponibles = computed(function () {
    $inscritosIds = $this->grupo->alumnos->pluck('id')->toArray();
    return User::where('tipo', 'alumno')
        ->whereNotIn('id', $inscritosIds)
        ->when($this->searchAlumnos, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchAlumnos . '%')
                  ->orWhere('curp', 'like', '%' . $this->searchAlumnos . '%');
            });
        })
        ->limit(10)
        ->get();
});

$asignarAlumnos = function () {
    if (empty($this->selectedAlumnos)) {
        Flux::toast(heading: 'Error', text: 'Selecciona al menos un alumno de la lista.', variant: 'danger');
        return;
    }

    $grupo = Grupo::find($this->grupoId);
    foreach ($this->selectedAlumnos as $alumnoId) {
        $grupo->alumnos()->syncWithoutDetaching([
            $alumnoId => ['fecha_asignacion' => now(), 'estado' => 'activo']
        ]);
    }

    $this->reset(['selectedAlumnos', 'searchAlumnos']);
    $this->dispatch('modal-hide', name: 'modal-asignar-alumnos');
    Flux::toast(heading: 'Inscripción Exitosa', text: 'Los alumnos fueron matriculados en el grupo.', variant: 'success');
};

$desvincularAlumno = function ($alumnoId) {
    $this->grupo->alumnos()->detach($alumnoId);
    Flux::toast(heading: 'Baja Académica', text: 'El alumno fue removido del grupo actual.', variant: 'warning');
};

$buscarDocentesAPI = function () {
    try {
        $response = Http::withToken(config('services.sad.token'))
            ->timeout(5)
            ->get(config('services.sad.url') . '/docentes', [
                'plantel' => $this->grupo->plantel->name,
                'search' => $this->searchDocente,
                'per_page' => 10
            ]);

        if ($response->successful()) {
            $this->docentesAPI = $response->json()['data'] ?? [];
        } else {
             $mockData = [
                 ['id' => 1, 'name' => 'Juan Pérez', 'cargo' => 'Titular A', 'email' => 'juan@sad.com'],
                 ['id' => 2, 'name' => 'Ana Gómez', 'cargo' => 'Asignatura', 'email' => 'ana@gomez.com'],
                 ['id' => 3, 'name' => 'Carlos Barrera', 'cargo' => 'Titular B', 'email' => 'barrera@sad.com'],
                 ['id' => 4, 'name' => 'Eduardo Medina', 'cargo' => 'Sustituto', 'email' => 'emedina@sad.com'],
             ];

             if (!empty($this->searchDocente)) {
                 $this->docentesAPI = array_values(array_filter($mockData, function($d) {
                     return stripos($d['name'], $this->searchDocente) !== false || stripos($d['cargo'], $this->searchDocente) !== false;
                 }));
             } else {
                 $this->docentesAPI = $mockData;
             }

             Flux::toast(heading: 'Aviso', text: 'Mostrando datos locales de prueba debido a un error de conexión con SAD.', variant: 'warning');
        }
    } catch (\Exception $e) {
        $mockData = [
            ['id' => 1, 'name' => 'Juan Pérez', 'cargo' => 'Titular A', 'email' => 'juan@sad.com'],
            ['id' => 2, 'name' => 'Ana Gómez', 'cargo' => 'Asignatura', 'email' => 'ana@gomez.com'],
            ['id' => 3, 'name' => 'Carlos Barrera', 'cargo' => 'Titular B', 'email' => 'barrera@sad.com'],
            ['id' => 4, 'name' => 'Eduardo Medina', 'cargo' => 'Sustituto', 'email' => 'emedina@sad.com'],
        ];

        if (!empty($this->searchDocente)) {
            $this->docentesAPI = array_values(array_filter($mockData, function($d) {
                return stripos($d['name'], $this->searchDocente) !== false || stripos($d['cargo'], $this->searchDocente) !== false;
            }));
        } else {
            $this->docentesAPI = $mockData;
        }

        Flux::toast(heading: 'Modo Local', text: 'Conexión SAD fallida. Simulando directorio local.', variant: 'warning');
    }
};

$asignarDocente = function ($docenteId) {
    $grupo = Grupo::find($this->grupoId);
    $grupo->docente_id = $docenteId;
    $grupo->save();

    $this->cargarDocente();
    $this->dispatch('modal-hide', name: 'modal-asignar-docente');
    Flux::toast(heading: 'Docente Asignado', text: 'La titularidad del grupo ha sido actualizada.', variant: 'success');
};

$subirDocumento = function () {
    $this->validate([
        'tipoDocumento' => 'required|string',
        'archivo' => 'required|file|max:10240', // 10MB
    ]);

    $path = $this->archivo->store('expedientes_grupos', 'public');

    GrupoExpediente::create([
        'grupo_id' => $this->grupoId,
        'tipo_documento' => $this->tipoDocumento,
        'archivo' => $path,
        'usuario_id' => auth()->id(),
    ]);

    $this->reset(['tipoDocumento', 'archivo']);
    $this->dispatch('modal-hide', name: 'modal-subir-documento');
    Flux::toast(heading: 'Evidencia Agregada', text: 'El documento fue anexado al expediente del grupo.', variant: 'success');
};

$subirAsistencia = function () {
    $this->validate([
        'escaneoAsistencia' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
    ]);

    $path = $this->escaneoAsistencia->store('asistencias', 'public');

    App\Models\Asistencia::create([
        'grupo_id' => $this->grupoId,
        'plantel_id' => $this->grupo->plantel_id,
        'archivo' => $path,
        'estado' => 'pendiente',
        'subido_at' => now(),
        'fecha_inicio_real' => now(),
    ]);

    $this->reset('escaneoAsistencia');
    $this->dispatch('modal-hide', name: 'modal-subir-asistencia');
    Flux::toast(heading: 'Asistencia Enviada', text: 'La lista está lista para validación de coordinación (Límite 3 hrs).', variant: 'success');
};

?>

<div class="p-6 max-w-[1600px] mx-auto space-y-8">
    
    <!-- Encabezado y Acciones Rápidas -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 bg-zinc-900 dark:bg-zinc-800 p-8 rounded-3xl text-white shadow-xl relative overflow-hidden">
        <!-- Decoración de fondo -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

        <div class="flex items-center gap-6 relative z-10 w-full xl:w-auto">
            <a href="{{ route('grupos.index') }}" class="p-3 bg-white/10 hover:bg-white/20 rounded-2xl transition-all border border-white/10 backdrop-blur-sm group">
                <flux:icon name="chevron-left" class="size-6 text-white group-hover:-translate-x-1 transition-transform" />
            </a>
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black uppercase tracking-tight">{{ $this->grupo->nombre }}</h1>
                    @php
                        $badgeColor = match($this->grupo->estado) {
                            'activo' => 'bg-green-500/20 text-green-300 border-green-500/30',
                            'concluido' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                            default => 'bg-zinc-500/20 text-zinc-300 border-zinc-500/30'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border tracking-widest {{ $badgeColor }}">
                        {{ $this->grupo->estado }}
                    </span>
                </div>
                <div class="text-sm font-medium text-zinc-400 flex items-center gap-2">
                    <flux:icon name="academic-cap" variant="mini" class="text-zinc-500" />
                    {{ $this->grupo->curso->nombre }}
                    <span class="text-zinc-600 px-1">•</span>
                    <flux:icon name="building-office" variant="mini" class="text-zinc-500" />
                    {{ $this->grupo->plantel->name }}
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 xl:gap-2 relative z-10 w-full xl:w-auto justify-start xl:justify-end">
            <!-- Simulación de botón Métricas (link normal ya que no hay vista) -->
            <a href="#" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition-all cursor-not-allowed opacity-50">
                <flux:icon name="chart-bar" class="size-4" /> Métricas (Próximamente)
            </a>
            <!-- Botón generar lista -->
            @if($this->grupo->alumnos->count() > 0)
                <a href="{{ route('asistencias.generar', $this->grupo->id) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-md">
                    <flux:icon name="printer" class="size-4" /> Lista de Asistencia (PDF)
                </a>
            @else
                <button disabled title="Inscribe alumnos primero para imprimir el reporte" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white text-zinc-400 border border-white hover:bg-zinc-100 dark:bg-zinc-800 dark:border-zinc-700 rounded-xl font-bold text-[10px] uppercase tracking-wider transition-all cursor-not-allowed opacity-75">
                    <flux:icon name="printer" class="size-4" /> Lista de Asistencia (PDF)
                </button>
            @endif
            <!-- Botón subir lista (Modal) -->
            <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-subir-asistencia' })" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 border border-blue-500 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-blue-500/20">
                <flux:icon name="arrow-up-tray" class="size-4" /> Entregar Asistencia
            </button>
        </div>
    </div>

    <!-- Layout Principal -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Columna Izquierda: Detalles y Alumnos (Toma 2 columnas) -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Tarjetas de Información Operativa -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col justify-center">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-1">Ciclo Escolar</span>
                    <span class="text-xl font-black text-zinc-900 dark:text-white">{{ $this->grupo->periodo }}</span>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col justify-center">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-1">Carga Horaria</span>
                    <span class="text-xl font-black text-zinc-900 dark:text-white flex items-baseline gap-1">{{ $this->grupo->total_horas }} <span class="text-sm text-zinc-400 font-medium">hrs</span></span>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col justify-center">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-1">Periodo Visual</span>
                    <span class="text-sm font-bold text-zinc-900 dark:text-white leading-tight">
                        {{ $this->grupo->fecha_inicio?->format('d/m/Y') }} <span class="text-zinc-400 font-normal mx-1">al</span><br>
                        {{ $this->grupo->fecha_fin?->format('d/m/Y') }}
                    </span>
                </div>
                <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col justify-center relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-5">
                        <flux:icon name="clock" class="size-24" />
                    </div>
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-1 relative z-10">Horario Fijo</span>
                    <span class="text-lg font-black text-zinc-900 dark:text-white font-mono relative z-10">
                        {{ \Carbon\Carbon::parse($this->grupo->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($this->grupo->hora_fin)->format('H:i') }}
                    </span>
                    <span class="text-[9px] text-zinc-400 mt-1 uppercase relative z-10 font-bold overflow-hidden text-ellipsis whitespace-nowrap">
                        Dias: 
                        @foreach(is_array($this->grupo->dias_clase) ? $this->grupo->dias_clase : json_decode($this->grupo->dias_clase, true) ?? [] as $d)
                            {{ ['Lun','Mar','Mie','Jue','Vie','Sab','Dom'][$d-1] ?? '' }} 
                        @endforeach
                    </span>
                </div>
            </div>

            <!-- Matrícula de Alumnos -->
            <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden flex flex-col h-[600px]">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center bg-zinc-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h2 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Matrícula del Grupo</h2>
                        <p class="text-xs text-zinc-500 font-medium">Hay <strong class="text-blue-600 dark:text-blue-400">{{ $this->grupo->alumnos->count() }} alumnos</strong> inscritos activamente.</p>
                    </div>
                    <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-asignar-alumnos' })" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all hover:bg-zinc-800 dark:hover:bg-zinc-100 shadow-lg shadow-zinc-900/10">
                        <flux:icon name="user-plus" variant="mini" /> Inscribir Alumnos
                    </button>
                </div>
                
                <div class="overflow-y-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white dark:bg-zinc-800 z-10 shadow-sm">
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Perfil del Alumno</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Identificación (CURP)</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Alta en Grupo</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-right">Administrar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($this->grupo->alumnos as $alumno)
                                <tr wire:key="alumno-grp-{{ $alumno->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-4">
                                            <div class="size-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-sm">
                                                {{ substr($alumno->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-sm text-zinc-900 dark:text-white">{{ $alumno->name }}</span>
                                                <span class="text-[10px] text-zinc-500 font-mono mt-0.5">{{ $alumno->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 font-mono text-xs font-bold rounded border border-zinc-200 dark:border-zinc-700">{{ $alumno->curp }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-center text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ \Carbon\Carbon::parse($alumno->pivot->fecha_asignacion)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                         <div x-data="{ openConfirm: false }" class="inline-block relative">
                                            <flux:button variant="ghost" size="sm" color="red" icon="user-minus" x-on:click="openConfirm = true" />
                                            
                                            <!-- Mini Delete Modal -->
                                            <div x-show="openConfirm" x-cloak class="absolute right-0 top-full mt-2 z-20 w-64 p-4 bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-900/30 rounded-xl shadow-xl flex flex-col gap-3 items-end ring-1 ring-black/5 dark:ring-white/10">
                                                <p class="text-[11px] text-left font-bold text-zinc-800 dark:text-white leading-tight break-words whitespace-normal w-full">¿Dar de baja a <b>{{ $alumno->name }}</b> de este grupo?</p>
                                                <div class="flex gap-2 w-full justify-between mt-1">
                                                    <flux:button variant="ghost" size="sm" x-on:click="openConfirm = false" class="text-[10px]">Conservar</flux:button>
                                                    <flux:button variant="danger" size="sm" wire:click="desvincularAlumno({{ $alumno->id }})" x-on:click="openConfirm = false" class="text-[10px]">Expulsar</flux:button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-24 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="p-6 bg-zinc-50 dark:bg-zinc-900 rounded-full border border-zinc-100 dark:border-zinc-800">
                                                <flux:icon name="users" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                            </div>
                                            <span class="font-bold text-zinc-600 dark:text-zinc-400">Lista Vacía</span>
                                            <span class="text-xs text-zinc-400">Agrega alumnos para comenzar a gestionar el grupo académico.</span>
                                            <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-asignar-alumnos' })" class="mt-4 text-xs font-bold text-blue-600 hover:text-blue-500 uppercase tracking-widest border-b border-blue-600/30">Inscribir Ahora</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Docente y Expedientes -->
        <div class="space-y-8">
             
            <!-- Docente Titular -->
             <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <flux:icon name="star" variant="mini" class="text-yellow-500" /> Docente Titular
                    </h2>
                    @if($this->docente)
                        <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-asignar-docente' })" class="text-[10px] font-bold text-zinc-500 hover:text-blue-600 uppercase tracking-widest transition-colors flex items-center gap-1">
                            <flux:icon name="arrows-right-left" variant="micro" /> Sustituir
                        </button>
                    @endif
                </div>

                <div class="p-6 text-center text-wrap relative overflow-hidden">
                    @if($this->docente)
                        <!-- Patrón de fondo ornamental -->
                        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-blue-50/50 dark:from-blue-900/10 to-transparent"></div>
                        
                        <div class="relative z-10 flex flex-col items-center gap-4">
                            <div class="size-20 rounded-2xl bg-white dark:bg-zinc-800 shadow-lg border border-zinc-100 dark:border-zinc-700 p-1">
                                <div class="w-full h-full bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-2xl">
                                    {{ substr($this->docente['name'], 0, 1) }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-lg font-black text-zinc-900 dark:text-white leading-tight">{{ $this->docente['name'] }}</h3>
                                <p class="text-xs text-zinc-500 font-mono">{{ $this->docente['email'] }}</p>
                            </div>
                            <span class="px-3 py-1 bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-full text-[10px] font-black uppercase text-zinc-600 dark:text-zinc-400 tracking-wider">
                                {{ $this->docente['cargo'] }}
                            </span>
                        </div>
                    @else
                        <div class="py-8 flex flex-col items-center gap-4 text-zinc-500">
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-full border border-dashed border-zinc-300 dark:border-zinc-700">
                                <flux:icon name="user" class="size-8 opacity-40" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Plaza Vacante</p>
                                <p class="text-xs">No hay docente titular asignado.</p>
                            </div>
                            <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-asignar-docente' })" class="mt-2 px-5 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-xs font-black uppercase tracking-widest hover:border-blue-500 hover:text-blue-600 transition-all shadow-sm">
                                Asignar del SAD
                            </button>
                        </div>
                    @endif
                </div>
             </div>

             <!-- Expediente Documental -->
             <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="p-5 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <flux:icon name="folder-open" variant="mini" class="text-indigo-500" /> Repositorio
                    </h2>
                    <button x-data x-on:click="$dispatch('modal-show', { name: 'modal-subir-documento' })" class="size-8 flex items-center justify-center bg-zinc-100 dark:bg-zinc-900 hover:bg-zinc-200 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 rounded-xl transition-colors">
                        <flux:icon name="plus" variant="mini" />
                    </button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 space-y-3">
                    @forelse($this->grupo->expediente as $doc)
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:border-blue-300 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="p-2 {{ $doc->archivo ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'bg-zinc-50 dark:bg-zinc-900 text-zinc-400' }} rounded-xl">
                                    <flux:icon :name="$doc->archivo ? 'document-check' : 'document-text'" class="size-5" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold leading-tight text-zinc-800 dark:text-white">{{ str_replace('_', ' ', ucfirst($doc->tipo_documento)) }}</span>
                                    <span class="text-[9px] text-zinc-400 font-mono mt-0.5">{{ $doc->created_at->format('d M, Y H:i') }}</span>
                                </div>
                            </div>
                            @if($doc->archivo)
                                <a href="{{ Storage::url($doc->archivo) }}" target="_blank" class="p-2 text-zinc-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Descargar">
                                    <flux:icon name="arrow-down-tray" class="size-4" />
                                </a>
                            @else
                                <span class="px-2 py-1 bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-400 text-[8px] font-black uppercase rounded uppercase tracking-widest">Faltante</span>
                            @endif
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 opacity-60">
                            <flux:icon name="folder-minus" class="size-10 text-zinc-300 mb-3" />
                            <span class="text-sm font-bold text-zinc-500">Expediente Vacío</span>
                            <span class="text-[10px] text-zinc-400 mt-1">Añade planeaciones o reportes asociados al grupo.</span>
                        </div>
                    @endforelse
                </div>
             </div>
        </div>
    </div>

    <!-- Modales Alpine + Livewire (Estándar Tailwind) -->
    
    <!-- Modal: Asignar Alumnos -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-asignar-alumnos') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-asignar-alumnos') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col max-h-[90vh]" x-on:click.away="open = false">
            <div class="p-6 border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Inscripción Masiva</h2>
                <p class="text-[11px] text-zinc-500 font-bold uppercase tracking-tighter italic mt-1">Busca y selecciona alumnos de la base general para agregarlos al padrón.</p>
            </div>
            
            <form wire:submit="asignarAlumnos" class="flex flex-col h-full overflow-hidden">
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <flux:input wire:model.live.debounce.300ms="searchAlumnos" placeholder="Escribe el nombre o CURP para filtrar el padrón..." icon="magnifying-glass" class="shadow-sm" />

                    <!-- Contenedor de selección -->
                    <div class="bg-zinc-50 dark:bg-zinc-900/30 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="max-h-72 overflow-y-auto p-2 space-y-1 relative">
                            @foreach($this->alumnosDisponibles as $a)
                                <label class="flex items-center gap-4 p-3 hover:bg-white dark:hover:bg-zinc-800 rounded-xl cursor-pointer transition-colors border border-transparent hover:border-zinc-200 dark:hover:border-zinc-700 hover:shadow-sm">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" wire:model="selectedAlumnos" value="{{ $a->id }}" class="size-4 rounded border-zinc-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <span class="text-sm font-bold text-zinc-900 dark:text-white leading-tight uppercase">{{ $a->name }}</span>
                                        <span class="text-[10px] text-zinc-500 font-mono mt-0.5">CURP: {{ $a->curp }}</span>
                                    </div>
                                    <div class="text-[10px] text-zinc-400 invisible group-hover:visible font-bold">Seleccionar</div>
                                </label>
                            @endforeach
                            
                            <!-- Estado de carga de Wire (Visual) -->
                            <div wire:loading wire:target="searchAlumnos" class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm z-10 flex flex-col items-center justify-center pb-8">
                                <flux:icon name="arrow-path" class="size-8 text-blue-500 animate-spin mb-2" />
                                <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Buscando padrón...</span>
                            </div>

                            @if($this->alumnosDisponibles->isEmpty())
                                <div class="text-center py-12 text-zinc-400" wire:loading.remove wire:target="searchAlumnos">
                                    <flux:icon name="user-group" class="size-12 opacity-20 mx-auto mb-3" />
                                    <span class="text-sm font-bold">No se encontraron resultados</span>
                                    <p class="text-[10px] mt-1">Prueba con otro término de búsqueda.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="pt-2 text-right">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                            Seleccionados: <span x-text="$wire.selectedAlumnos.length" class="text-blue-600 dark:text-blue-400 text-sm"></span>
                        </span>
                    </div>
                </div>

                <div class="flex gap-3 justify-end p-6 border-t border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        Ejecutar Inscripción Masiva
                    </flux:button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Asignar Docente -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-asignar-docente') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-asignar-docente') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-lg rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left flex flex-col max-h-[85vh]" x-on:click.away="open = false">
            <div class="space-y-1 border-b border-zinc-100 dark:border-zinc-700 pb-4 shrink-0">
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Titularidad SAD</h2>
                <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Extrae y asigna un docente validado por la plataforma SAD.</p>
            </div>

            <div class="flex gap-2 shrink-0">
                <div class="relative w-full">
                    <input type="text" wire:model.live.debounce.300ms="searchDocente" wire:keydown.enter="buscarDocentesAPI" placeholder="Nombre del docente..." class="w-full pl-10 pr-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-2.5 size-5 text-zinc-400" />
                </div>
                <flux:button variant="primary" wire:click="buscarDocentesAPI">Buscar</flux:button>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-900/30 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-y-auto flex-1 min-h-[200px] p-2 space-y-2">
                
                <div wire:loading wire:target="buscarDocentesAPI" class="w-full py-12 flex flex-col items-center">
                    <flux:icon name="arrow-path" class="size-8 text-blue-500 animate-spin mb-2" />
                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Contactando API SAD...</span>
                </div>

                <div wire:loading.remove wire:target="buscarDocentesAPI" class="space-y-2 w-full">
                    @forelse($this->docentesAPI as $d)
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-100 dark:border-zinc-700 shadow-sm hover:border-blue-300 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center font-black text-zinc-600 dark:text-zinc-300 uppercase">
                                    {{ substr($d['name'], 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-black uppercase text-zinc-900 dark:text-white leading-tight">{{ $d['name'] }}</span>
                                    <span class="text-[9px] text-zinc-500">{{ $d['cargo'] }}</span>
                                </div>
                            </div>
                            <button wire:click="asignarDocente({{ $d['id'] }})" class="px-3 py-1 bg-zinc-100 hover:bg-blue-600 text-zinc-600 hover:text-white dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-blue-600 font-bold uppercase tracking-wider text-[10px] rounded border border-transparent hover:border-blue-500 transition-all">
                                Elegir
                            </button>
                        </div>
                    @empty
                        @if(!empty($this->searchDocente))
                            <div class="text-center py-8 text-zinc-400">
                                <span class="text-sm font-bold">Sin coincidencias</span>
                                <p class="text-[10px] mt-1">SAD no devolvió resultados en el plantel actual.</p>
                            </div>
                        @else
                            <div class="text-center py-8 text-zinc-400">
                                <flux:icon name="cloud-arrow-down" class="size-8 mx-auto opacity-30 mb-2" />
                                <span class="text-xs font-bold uppercase tracking-widest block">Directorio Externo</span>
                            </div>
                        @endif
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end pt-2 shrink-0">
                <flux:button variant="ghost" x-on:click="open = false">Desechar Operación</flux:button>
            </div>
        </div>
    </div>

    <!-- Modal: Subir Documento Expediente -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-subir-documento') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-subir-documento') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="subirDocumento" class="space-y-6">
                <div class="space-y-1">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Integrar Evidencia</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Carga oficios, reportes u homologaciones al exp. de grupo.</p>
                </div>

                <flux:field>
                    <flux:label>Tipología Documental</flux:label>
                    <flux:select wire:model="tipoDocumento">
                        <flux:select.option value="">-- Selecciona Categoría --</flux:select.option>
                        <flux:select.option value="lista_asistencia">Copia Formato Lista de Asistencia</flux:select.option>
                        <flux:select.option value="planeacion_didactica">Planeación Didáctica Visada</flux:select.option>
                        <flux:select.option value="reporte_evaluacion">Reporte Consignación de Evaluación</flux:select.option>
                        <flux:select.option value="acta_conformidad">Acta de Conformidad Estudiantil</flux:select.option>
                        <flux:select.option value="otro">Misceláneo / Otros Anexos</flux:select.option>
                    </flux:select>
                    <flux:error name="tipoDocumento" />
                </flux:field>

                <flux:field>
                    <flux:label>Archivo Soporte (PDF/JPG/PNG max: 10MB)</flux:label>
                    <div class="mt-1 w-full bg-zinc-50 dark:bg-zinc-900 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-4 text-center">
                        <input type="file" wire:model="archivo" class="w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <div wire:loading wire:target="archivo" class="text-[10px] text-blue-500 font-bold mt-2 uppercase">Procesando carga...</div>
                    </div>
                    <flux:error name="archivo" class="mt-1" />
                </flux:field>

                <div class="flex gap-3 justify-end pt-2">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="font-black uppercase tracking-widest text-[10px]">Guardar al Acervo</flux:button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Subir Asistencia Escaneada -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-subir-asistencia') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-subir-asistencia') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-sm rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-center" x-on:click.away="open = false">
            <form wire:submit="subirAsistencia" class="space-y-6">
                <div class="mx-auto w-16 h-16 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-blue-500 mb-2">
                    <flux:icon name="document-arrow-up" class="size-8" />
                </div>
                
                <div class="space-y-1">
                    <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Entrega de Lista</h2>
                    <p class="text-[11px] text-zinc-500 leading-tight">Adjunta la hoja de firmas escaneadas para remitirla a coordinación.</p>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3 text-[10px] text-blue-700 dark:text-blue-300 text-left flex gap-3">
                    <flux:icon name="exclamation-circle" variant="mini" class="shrink-0 text-blue-500" />
                    <p>Tras enviar, la coordinación cuenta con un plazo legal de <strong>3 horas</strong> hábiles para dictaminar el registro.</p>
                </div>

                <div class="mt-1 w-full text-left">
                    <flux:label class="sr-only">Archivo (Imágen o PDF)</flux:label>
                    <input type="file" wire:model="escaneoAsistencia" class="w-full text-xs text-zinc-500 file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white cursor-pointer" accept=".pdf,.jpg,.jpeg,.png"/>
                    <flux:error name="escaneoAsistencia" class="mt-1" />
                    <div wire:loading wire:target="escaneoAsistencia" class="text-[10px] text-blue-500 font-bold mt-2 text-center uppercase w-full">Adjuntando archivo en caché...</div>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <flux:button type="submit" variant="primary" class="w-full font-black uppercase tracking-widest text-[10px]">Certificar Envío</flux:button>
                    <flux:button variant="ghost" x-on:click="open = false" class="w-full">Abortar Trámite</flux:button>
                </div>
            </form>
        </div>
    </div>

</div>
