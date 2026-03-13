<?php

use function Livewire\Volt\{state, computed};
use App\Models\User;
use App\Models\Expediente;
use App\Models\Grupo;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;

$stats = computed(function() {
    return [
        'total_usuarios' => User::count(),
        'alumnos' => User::role('alumno')->count(),
        'docentes' => User::role('docente')->count(),
        'expedientes' => [
            'total' => Expediente::count(),
            'completos' => Expediente::where('estatus', 'completo')->count(),
            'incompletos' => Expediente::where('estatus', 'incompleto')->count(),
            'observados' => Expediente::where('estatus', 'observado')->count(),
        ],
        'grupos_activos' => Grupo::where('estado', 'activo')->count(),
        'promedio_general' => Calificacion::avg('calificacion') ?? 0,
    ];
});

$distribution = computed(function() {
    if (!auth()->check()) return null;

    $query = User::query();
    
    // Si no es admin_ti, el Global Scope de HasJurisdiction ya filtra
    // pero para la distribución por nivel queremos ver el desglose
    return $query->select('nivel', DB::raw('count(*) as total'))
        ->groupBy('nivel')
        ->get()
        ->map(function($item) {
            $presentes = \App\Models\AsistenciaIndividual::whereHas('user', function($q) use ($item) {
                    $q->where('nivel', $item->nivel);
                })
                ->whereDate('fecha', now())
                ->where('estatus', 'presente')
                ->count();
            
            $item->presentes = $presentes;
            $item->porcentaje = $item->total > 0 ? round(($presentes / $item->total) * 100) : 0;
            return $item;
        });
});

$nestedStats = computed(function() {
    return \App\Models\Plantel::withCount(['users' => function($q) {
            $q->role('alumno');
        }])
        ->get()
        ->map(function($plantel) {
            // Stats del Plantel
            $presentesPlantel = \App\Models\AsistenciaIndividual::whereHas('user', function($q) use ($plantel) {
                    $q->where('plantel_id', $plantel->id);
                })
                ->whereDate('fecha', now())
                ->where('estatus', 'presente')
                ->count();
            
            $plantel->presentes = $presentesPlantel;
            $plantel->porcentaje = $plantel->users_count > 0 ? round(($presentesPlantel / $plantel->users_count) * 100) : 0;
            
            // Stats de Grupos del Plantel
            $plantel->gruposS_stats = Grupo::where('plantel_id', $plantel->id)
                ->where('estado', 'activo')
                ->with('curso')
                ->withCount(['alumnos' => function($q) {
                    $q->role('alumno');
                }])
                ->get()
                ->map(function($grupo) {
                    $presentesGrupo = \App\Models\AsistenciaIndividual::where('grupo_id', $grupo->id)
                        ->whereDate('fecha', now())
                        ->where('estatus', 'presente')
                        ->count();
                    $grupo->presentes = $presentesGrupo;
                    $grupo->faltantes = max(0, $grupo->alumnos_count - $presentesGrupo);
                    $grupo->porcentaje = $grupo->alumnos_count > 0 ? round(($presentesGrupo / $grupo->alumnos_count) * 100) : 0;
                    return $grupo;
                });

            return $plantel;
        });
});

?>

<div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Usuarios Card -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest font-bold">Total Usuarios</flux:heading>
                <div class="flex items-end gap-2 mt-2">
                    <span class="text-4xl font-black text-zinc-900 dark:text-white">{{ $this->stats['total_usuarios'] }}</span>
                    <span class="text-xs text-zinc-400 mb-1">Registrados</span>
                </div>
                <div class="mt-4 flex gap-4 text-xs font-medium">
                    <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                        <flux:icon name="academic-cap" variant="mini" /> {{ $this->stats['alumnos'] }} Alumnos
                    </span>
                    <span class="flex items-center gap-1 text-zinc-500">
                         <flux:icon name="user-group" variant="mini" /> {{ $this->stats['docentes'] }} Docentes
                    </span>
                </div>
            </div>
            <flux:icon name="users" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-50 dark:text-zinc-700/50 group-hover:scale-110 transition-transform" />
        </div>

        <!-- Expedientes Card -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest font-bold">Expedientes</flux:heading>
                <div class="flex items-end gap-2 mt-2">
                    <span class="text-4xl font-black text-zinc-900 dark:text-white">{{ $this->stats['expedientes']['completos'] }}</span>
                    <span class="text-xs text-emerald-500 font-bold mb-1">Completos</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-[10px] font-bold uppercase tracking-tighter">
                    <div class="p-2 bg-amber-50 dark:bg-amber-900/10 rounded-lg text-amber-600 border border-amber-100 dark:border-amber-800/50">
                         {{ $this->stats['expedientes']['incompletos'] }} Incompletos
                    </div>
                    <div class="p-2 bg-red-50 dark:bg-red-900/10 rounded-lg text-red-600 border border-red-100 dark:border-red-900/50">
                         {{ $this->stats['expedientes']['observados'] }} Observados
                    </div>
                </div>
            </div>
            <flux:icon name="folder-open" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-50 dark:text-zinc-700/50 group-hover:scale-110 transition-transform" />
        </div>

        <!-- Academia Card -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest font-bold">Desempeño</flux:heading>
                <div class="flex items-end gap-2 mt-2">
                    <span class="text-4xl font-black {{ $this->stats['promedio_general'] >= 8 ? 'text-emerald-600' : 'text-zinc-900 dark:text-white' }}">
                        {{ number_format($this->stats['promedio_general'], 1) }}
                    </span>
                    <span class="text-xs text-zinc-400 mb-1">Promedio Gral.</span>
                </div>
                <div class="mt-4">
                    <flux:badge color="blue" variant="solid" size="sm" class="w-full justify-center">
                        <flux:icon name="star" variant="mini" class="mr-1" /> {{ $this->stats['grupos_activos'] }} Grupos en Curso
                    </flux:badge>
                </div>
            </div>
            <flux:icon name="academic-cap" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-50 dark:text-zinc-700/50 group-hover:scale-110 transition-transform" />
        </div>

        <!-- Contexto Jurisdicción -->
        <div class="bg-zinc-900 dark:bg-white p-6 rounded-3xl border border-zinc-800 dark:border-zinc-200 shadow-sm relative overflow-hidden group text-white dark:text-zinc-800">
            <div class="relative z-10">
                <flux:heading size="sm" class="text-zinc-400 dark:text-zinc-500 uppercase tracking-widest font-bold">Jurisdicción</flux:heading>
                <div class="mt-2 text-xl font-black truncate">
                    {{ auth()->user()?->hasRole('admin_ti') ? 'Acceso Total' : (auth()->user()?->plantel?->name ?? ucfirst(auth()->user()?->nivel) ?? 'Personalizado') }}
                </div>
                <div class="mt-4 text-[10px] text-zinc-500 dark:text-zinc-400 font-mono">
                    AUTENTICADO COMO: <br>
                    <span class="text-white dark:text-zinc-900">{{ auth()->user()?->roles?->first()?->name ?? 'Sin rol' }}</span>
                </div>
            </div>
            <flux:icon name="shield-check" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-800 dark:text-zinc-100 group-hover:scale-110 transition-transform opacity-50" />
        </div>
    </div>

    @if($this->distribution)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Nivel de Seguridad -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <flux:heading size="lg" class="mb-6">Fuerza por Nivel</flux:heading>
                <div class="space-y-4">
                    @foreach($this->distribution as $dist)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-widest">{{ $dist->nivel ?? 'S/N' }}</span>
                                <span class="text-[10px] text-emerald-500 font-bold">{{ $dist->porcentaje }}% REAL</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <div class="text-2xl font-black">{{ $dist->presentes }}</div>
                                <div class="text-[10px] text-zinc-400">/ {{ $dist->total }} elementos</div>
                            </div>
                            <div class="mt-2 w-full bg-zinc-200 dark:bg-zinc-700 h-1 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $dist->porcentaje }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-zinc-900 rounded-3xl p-6 text-white overflow-hidden relative">
                <div class="relative z-10">
                    <flux:heading size="sm" class="text-zinc-400 uppercase font-bold">Resumen Diario</flux:heading>
                    <div class="mt-4 text-xs text-zinc-500 leading-relaxed">
                        Los datos presentados corresponden al pase de lista individual realizado por Control Escolar hasta las {{ now()->format('H:i') }} hrs.
                    </div>
                </div>
                <flux:icon name="clock" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-800 opacity-50" />
            </div>
        </div>

        <!-- Columna Derecha: Planteles con sus Grupos (Englobado) -->
        <div class="lg:col-span-2 space-y-6">
            <flux:heading size="lg">Operatividad por Plantel y Grupo</flux:heading>
            
            <div class="grid grid-cols-1 gap-6 overflow-y-auto max-h-[800px] pr-2 custom-scrollbar">
                @foreach($this->nestedStats as $plantel)
                    @if($plantel->users_count > 0 || count($plantel->gruposS_stats) > 0)
                        <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                            <!-- Cabecera del Plantel -->
                            <div class="p-5 bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600">
                                        <flux:icon name="building-office-2" variant="mini" />
                                    </div>
                                    <div>
                                        <div class="font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $plantel->name }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ $plantel->presentes }} presentes de {{ $plantel->users_count }} alumnos totales</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-blue-600">{{ $plantel->porcentaje }}%</div>
                                    <div class="text-[8px] text-zinc-400 font-bold uppercase">Eficiencia Total</div>
                                </div>
                            </div>

                            <!-- Tabla de Grupos del Plantel -->
                            <div class="bg-white dark:bg-zinc-800">
                                @if(count($plantel->gruposS_stats) > 0)
                                    <flux:table>
                                        <flux:table.columns>
                                            <flux:table.column>Grupo / Curso</flux:table.column>
                                            <flux:table.column align="center">Presentes</flux:table.column>
                                            <flux:table.column align="center">Faltantes</flux:table.column>
                                            <flux:table.column align="right">% Real</flux:table.column>
                                        </flux:table.columns>

                                        <flux:table.rows>
                                            @foreach($plantel->gruposS_stats as $grupo)
                                                <flux:table.row>
                                                    <flux:table.cell>
                                                        <div class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200">{{ $grupo->nombre }}</div>
                                                        <div class="text-[9px] text-zinc-500 uppercase">{{ $grupo->curso?->nombre }}</div>
                                                    </flux:table.cell>
                                                    <flux:table.cell align="center">
                                                        <flux:badge size="sm" color="green" inset="top bottom">{{ $grupo->presentes }}</flux:badge>
                                                    </flux:table.cell>
                                                    <flux:table.cell align="center">
                                                        <flux:badge size="sm" :color="$grupo->faltantes > 0 ? 'red' : 'zinc'" inset="top bottom">{{ $grupo->faltantes }}</flux:badge>
                                                    </flux:table.cell>
                                                    <flux:table.cell align="right">
                                                        <div class="text-[10px] font-black {{ $grupo->porcentaje > 80 ? 'text-emerald-500' : 'text-amber-500' }}">
                                                            {{ $grupo->porcentaje }}%
                                                        </div>
                                                    </flux:cell>
                                                </flux:table.row>
                                            @endforeach
                                        </flux:table.rows>
                                    </flux:table>
                                @else
                                    <div class="py-8 text-center text-xs text-zinc-400 italic">
                                        No hay grupos activos registrados en este plantel.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
