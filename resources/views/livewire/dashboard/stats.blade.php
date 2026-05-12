<?php

use function Livewire\Volt\{state, computed};
use App\Models\User;
use App\Models\Expediente;
use App\Models\Grupo;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;

$stats = computed(function() {
    $plantelId = auth()->user()->plantel_id;
    $isOperador = auth()->user()->hasRole('operador');

    $userQuery = User::query();
    $grupoQuery = Grupo::query();
    $califQuery = Calificacion::query();
    $expQuery = Expediente::query();

    if ($isOperador && $plantelId) {
        $userQuery->where('plantel_id', $plantelId);
        $grupoQuery->where('plantel_id', $plantelId);
        $califQuery->whereHas('user', fn($q) => $q->where('plantel_id', $plantelId));
        $expQuery->whereHas('user', fn($q) => $q->where('plantel_id', $plantelId));
    }

    return [
        'total_usuarios' => (clone $userQuery)->count(),
        'alumnos' => (clone $userQuery)->role('alumno')->count(),
        'docentes' => (clone $userQuery)->role('docente')->count(),
        'expedientes' => [
            'total' => (clone $expQuery)->count(),
            'completos' => (clone $expQuery)->where('estatus', 'completo')->count(),
            'incompletos' => (clone $expQuery)->where('estatus', 'incompleto')->count(),
            'observados' => (clone $expQuery)->where('estatus', 'observado')->count(),
        ],
        'grupos_activos' => (clone $grupoQuery)->where('estado', 'activo')->count(),
        'promedio_general' => round((clone $califQuery)->whereHas('user')->avg('calificacion') ?? 0, 1),
    ];
});

$statsCapacitacion = computed(function() {
    $plantelId = auth()->user()->plantel_id;
    $isOperador = auth()->user()->hasRole('operador');

    // Consulta base de inscripciones activas (excluyendo bajas)
    $baseQuery = DB::table('grupo_user')
        ->join('users', 'grupo_user.user_id', '=', 'users.id')
        ->join('grupos', 'grupo_user.grupo_id', '=', 'grupos.id')
        ->where('users.tipo', 'alumno')
        ->where('grupo_user.estado', '!=', 'baja');

    if ($isOperador && $plantelId) {
        $baseQuery->where('grupos.plantel_id', $plantelId);
    }

    $totalCapacitados = (clone $baseQuery)->count();
    $hombres = (clone $baseQuery)->where('users.sexo', 'H')->count();
    $mujeres = (clone $baseQuery)->where('users.sexo', 'M')->count();

    // Desglose por Recurso Financiero
    $porRecurso = DB::table('grupo_user')
        ->join('users', 'grupo_user.user_id', '=', 'users.id')
        ->join('grupos', 'grupo_user.grupo_id', '=', 'grupos.id')
        ->leftJoin('recursos', 'grupos.recurso_id', '=', 'recursos.id')
        ->where('users.tipo', 'alumno')
        ->where('grupo_user.estado', '!=', 'baja')
        ->when($isOperador && $plantelId, fn($q) => $q->where('grupos.plantel_id', $plantelId))
        ->select(
            DB::raw("COALESCE(recursos.nombre, 'No Definido / Propio') as recurso_nombre"),
            DB::raw("COALESCE(recursos.clave, 'S/C') as recurso_clave"),
            DB::raw('count(*) as total'),
            DB::raw("sum(case when users.sexo = 'H' then 1 else 0 end) as hombres"),
            DB::raw("sum(case when users.sexo = 'M' then 1 else 0 end) as mujeres")
        )
        ->groupBy('recursos.nombre', 'recursos.clave')
        ->orderByDesc('total')
        ->get();

    // Desglose por Plantel/Campus
    $porPlantel = DB::table('grupo_user')
        ->join('users', 'grupo_user.user_id', '=', 'users.id')
        ->join('grupos', 'grupo_user.grupo_id', '=', 'grupos.id')
        ->join('planteles', 'grupos.plantel_id', '=', 'planteles.id')
        ->where('users.tipo', 'alumno')
        ->where('grupo_user.estado', '!=', 'baja')
        ->when($isOperador && $plantelId, fn($q) => $q->where('grupos.plantel_id', $plantelId))
        ->select(
            'planteles.name as assignment_name',
            DB::raw('count(*) as total'),
            DB::raw("sum(case when users.sexo = 'H' then 1 else 0 end) as hombres"),
            DB::raw("sum(case when users.sexo = 'M' then 1 else 0 end) as mujeres")
        )
        ->groupBy('planteles.name')
        ->orderByDesc('total')
        ->get();

    // Comparativa de Metas Anuales vs Avance Real
    $actualsByYear = DB::table('grupo_user')
        ->join('users', 'grupo_user.user_id', '=', 'users.id')
        ->join('grupos', 'grupo_user.grupo_id', '=', 'grupos.id')
        ->where('users.tipo', 'alumno')
        ->where('grupo_user.estado', '!=', 'baja')
        ->when($isOperador && $plantelId, fn($q) => $q->where('grupos.plantel_id', $plantelId))
        ->select(
            DB::raw("EXTRACT(YEAR FROM COALESCE(grupos.fecha_inicio, grupos.created_at))::integer as anio"),
            DB::raw('count(*) as total')
        )
        ->groupBy(DB::raw("EXTRACT(YEAR FROM COALESCE(grupos.fecha_inicio, grupos.created_at))"))
        ->get()
        ->pluck('total', 'anio')
        ->toArray();

    $goals = \App\Models\MetaCapacitacion::orderBy('anio', 'asc')->get();

    $comparativaMetas = $goals->map(function($g) use ($actualsByYear) {
        $actual = $actualsByYear[$g->anio] ?? 0;
        $porcentaje = $g->meta > 0 ? min(100, round(($actual / $g->meta) * 100)) : 0;
        $exceso = $g->meta > 0 && $actual > $g->meta ? round((($actual - $g->meta) / $g->meta) * 100) : 0;
        return [
            'anio' => $g->anio,
            'meta' => $g->meta,
            'actual' => $actual,
            'porcentaje' => $porcentaje,
            'exceso' => $exceso,
        ];
    });

    return [
        'total' => $totalCapacitados,
        'hombres' => $hombres,
        'mujeres' => $mujeres,
        'por_recurso' => $porRecurso,
        'por_plantel' => $porPlantel,
        'comparativa_metas' => $comparativaMetas,
    ];
});

$distribution = computed(function() {
    if (!auth()->check()) return null;

    $query = User::role('alumno');
    
    if (auth()->user()->hasRole('operador') && auth()->user()->plantel_id) {
        $query->where('plantel_id', auth()->user()->plantel_id);
    }
    return $query->select('nivel', DB::raw('count(*) as total'))
        ->groupBy('nivel')
        ->get()
        ->map(function($item) {
            $presentes = \App\Models\AsistenciaIndividual::whereHas('user', function($q) use ($item) {
                    $q->where('nivel', $item->nivel)
                      ->role('alumno'); // Solo alumnos en estado de fuerza
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
    $plantelId = auth()->user()->plantel_id;
    $isOperador = auth()->user()->hasRole('operador');

    return \App\Models\Plantel::query()
        ->when($isOperador && $plantelId, fn($q) => $q->where('id', $plantelId))
        ->get()
        ->map(function($plantel) {
            // Stats de Grupos del Plantel
            $gruposStats = \App\Models\Grupo::where('plantel_id', $plantel->id)
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

            // Consolidar Totales del Plantel a partir de sus grupos
            $totalAlumnos = $gruposStats->sum('alumnos_count');
            $totalPresentes = $gruposStats->sum('presentes');
            
            $plantel->users_count = $totalAlumnos;
            $plantel->presentes = $totalPresentes;
            $plantel->porcentaje = $totalAlumnos > 0 ? round(($totalPresentes / $totalAlumnos) * 100) : 0;
            $plantel->gruposS_stats = $gruposStats;

            return $plantel;
        })
        ->filter(function($plantel) use ($isOperador) {
            if ($isOperador) return true; // Siempre mostrar su plantel aunque esté vacío
            return $plantel->users_count > 0 || count($plantel->gruposS_stats) > 0;
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

        <!-- Resumen Diario (Reemplaza a Jurisdicción) -->
        <div class="bg-zinc-900 dark:bg-zinc-100 p-6 rounded-3xl border border-zinc-800 dark:border-zinc-200 shadow-sm relative overflow-hidden group text-white dark:text-zinc-800">
            <div class="relative z-10">
                <flux:heading size="sm" class="text-zinc-400 dark:text-zinc-500 uppercase tracking-widest font-bold">Resumen Diario</flux:heading>
                <div class="mt-2 text-xs leading-relaxed opacity-90">
                    Datos actualizados al pases de lista realizado por Control Escolar al corte de las 
                    <span class="font-bold text-blue-400 dark:text-blue-600">{{ now()->format('H:i') }} hrs</span> de hoy.
                </div>
                <div class="mt-4 flex gap-2">
                    <flux:badge size="sm" color="blue" variant="solid">{{ date('d/M/Y') }}</flux:badge>
                </div>
            </div>
            <flux:icon name="clock" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-800 dark:text-zinc-200 opacity-50 group-hover:scale-110 transition-transform" />
        </div>
    </div>

    <!-- 💰 SECCIÓN: ANALÍTICA PRESUPUESTARIA Y POBLACIONAL DE CAPACITACIÓN -->
    <div class="space-y-6">
        <div class="space-y-1">
            <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight uppercase flex items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="text-emerald-500" /> Analítica y Financiamiento de Capacitación
            </h2>
            <p class="text-xs text-zinc-500 font-medium italic">Distribución presupuestaria, eficiencia por plantel operativo y desglose por género de elementos capacitados vigentes.</p>
        </div>

        <!-- Tarjetas Clave de Capacitación -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Capacitados -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-3xl text-white shadow-md relative overflow-hidden group">
                <div class="relative z-10 space-y-1">
                    <span class="text-[10px] text-emerald-100 uppercase font-black tracking-widest">Matrícula Total Capacitada</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black">{{ $this->statsCapacitacion['total'] }}</span>
                        <span class="text-xs text-emerald-100 font-bold">Inscripciones</span>
                    </div>
                    <p class="text-[10px] text-emerald-100/80 leading-relaxed font-medium italic pt-2 border-t border-emerald-400/30">Total de elementos capacitados de forma activa (excluyendo bajas).</p>
                </div>
                <flux:icon name="banknotes" class="absolute -right-4 -bottom-4 w-28 h-28 text-emerald-400/20 group-hover:scale-110 transition-transform" />
            </div>

            <!-- Hombres -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden group">
                <div class="relative z-10 space-y-1">
                    <span class="text-[10px] text-zinc-500 font-black uppercase tracking-widest">Población Masculina</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-blue-600 dark:text-blue-400">{{ $this->statsCapacitacion['hombres'] }}</span>
                        <span class="text-xs text-zinc-400 font-bold">Elementos ({{ $this->statsCapacitacion['total'] > 0 ? round(($this->statsCapacitacion['hombres'] / $this->statsCapacitacion['total']) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-700 h-2 rounded-full overflow-hidden mt-3">
                        <div class="bg-blue-500 h-full" style="width: {{ $this->statsCapacitacion['total'] > 0 ? round(($this->statsCapacitacion['hombres'] / $this->statsCapacitacion['total']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <flux:icon name="user" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-50 dark:text-zinc-700/30 group-hover:scale-110 transition-transform" />
            </div>

            <!-- Mujeres -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden group">
                <div class="relative z-10 space-y-1">
                    <span class="text-[10px] text-zinc-500 font-black uppercase tracking-widest">Población Femenina</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-pink-600 dark:text-pink-400">{{ $this->statsCapacitacion['mujeres'] }}</span>
                        <span class="text-xs text-zinc-400 font-bold">Elementos ({{ $this->statsCapacitacion['total'] > 0 ? round(($this->statsCapacitacion['mujeres'] / $this->statsCapacitacion['total']) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-700 h-2 rounded-full overflow-hidden mt-3">
                        <div class="bg-pink-500 h-full" style="width: {{ $this->statsCapacitacion['total'] > 0 ? round(($this->statsCapacitacion['mujeres'] / $this->statsCapacitacion['total']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <flux:icon name="user" class="absolute -right-4 -bottom-4 w-24 h-24 text-zinc-50 dark:text-zinc-700/30 group-hover:scale-110 transition-transform" />
            </div>
        </div>

        <!-- Tablas de Desglose Cobertura -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Desglose por Recurso de Financiamiento -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white uppercase tracking-tight font-black">Por Recurso de Financiamiento</flux:heading>
                    <flux:icon name="banknotes" variant="mini" class="text-zinc-400" />
                </div>
                
                <div class="space-y-4">
                    @forelse($this->statsCapacitacion['por_recurso'] as $rec)
                        @php
                            $porcentajeRec = $this->statsCapacitacion['total'] > 0 ? round(($rec->total / $this->statsCapacitacion['total']) * 100) : 0;
                        @endphp
                        <div class="p-4 bg-zinc-50/50 dark:bg-zinc-900/30 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 flex flex-col gap-2 hover:border-emerald-500/30 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-tight block">{{ $rec->recurso_nombre }}</span>
                                    <span class="text-[9px] text-zinc-400 font-mono font-bold uppercase">Clave: {{ $rec->recurso_clave }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 block">{{ $rec->total }} <span class="text-[10px] text-zinc-400 font-normal">elem.</span></span>
                                    <span class="text-[9px] text-zinc-500 font-bold block">{{ $porcentajeRec }}% del total</span>
                                </div>
                            </div>
                            <!-- Desglose de Género para el Recurso -->
                            <div class="grid grid-cols-2 gap-4 text-[10px] text-zinc-500 border-t border-dashed border-zinc-200 dark:border-zinc-700 pt-2">
                                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Hombres: <strong class="text-zinc-800 dark:text-zinc-200 font-bold">{{ $rec->hombres }}</strong></span>
                                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span> Mujeres: <strong class="text-zinc-800 dark:text-zinc-200 font-bold">{{ $rec->mujeres }}</strong></span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full" style="width: {{ $porcentajeRec }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400 font-bold italic text-center py-6 uppercase tracking-tighter">No hay grupos con financiamiento activo registrado.</p>
                    @endforelse
                </div>
            </div>

            <!-- Desglose por Plantel/Campus -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-white uppercase tracking-tight font-black">Por Plantel / Campus Operativo</flux:heading>
                    <flux:icon name="building-office-2" variant="mini" class="text-zinc-400" />
                </div>
                
                <div class="space-y-4">
                    @forelse($this->statsCapacitacion['por_plantel'] as $plant)
                        @php
                            $porcentajePlant = $this->statsCapacitacion['total'] > 0 ? round(($plant->total / $this->statsCapacitacion['total']) * 100) : 0;
                        @endphp
                        <div class="p-4 bg-zinc-50/50 dark:bg-zinc-900/30 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 flex flex-col gap-2 hover:border-blue-500/30 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-tight block">{{ $plant->assignment_name }}</span>
                                    <span class="text-[9px] text-zinc-400 font-semibold uppercase">Jurisdicción Registrada</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-blue-600 dark:text-blue-400 block">{{ $plant->total }} <span class="text-[10px] text-zinc-400 font-normal">elem.</span></span>
                                    <span class="text-[9px] text-zinc-500 font-bold block">{{ $porcentajePlant }}% del total</span>
                                </div>
                            </div>
                            <!-- Desglose de Género para el Plantel -->
                            <div class="grid grid-cols-2 gap-4 text-[10px] text-zinc-500 border-t border-dashed border-zinc-200 dark:border-zinc-700 pt-2">
                                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Hombres: <strong class="text-zinc-800 dark:text-zinc-200 font-bold">{{ $plant->hombres }}</strong></span>
                                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span> Mujeres: <strong class="text-zinc-800 dark:text-zinc-200 font-bold">{{ $plant->mujeres }}</strong></span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full" style="width: {{ $porcentajePlant }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400 font-bold italic text-center py-6 uppercase tracking-tighter">No hay planteles con alumnos activos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 🎯 COMPARATIVA ANUALIZADA: META VS REAL -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
            <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-700 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                        <flux:icon name="chart-bar" variant="mini" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="text-zinc-900 dark:text-white uppercase tracking-tight font-black">Ciclos Fiscales: Metas Anuales vs Avance Real</flux:heading>
                        <p class="text-[10px] text-zinc-500 font-medium">Histórico acumulativo y comparativa del cumplimiento de metas institucionales de capacitación por año.</p>
                    </div>
                </div>
                <flux:badge size="sm" color="indigo" variant="solid">Anualizado</flux:badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($this->statsCapacitacion['comparativa_metas'] as $meta)
                    <div class="p-5 bg-zinc-50/50 dark:bg-zinc-900/30 rounded-2xl border border-zinc-100 dark:border-zinc-700/50 flex flex-col justify-between gap-4 relative overflow-hidden group hover:border-indigo-500/30 transition-all duration-300">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Ciclo {{ $meta['anio'] }}
                            </span>
                            @if($meta['exceso'] > 0)
                                <span class="text-[9px] bg-emerald-500/10 text-emerald-600 font-black px-2 py-0.5 rounded-full uppercase tracking-widest">+{{ $meta['exceso'] }}% Superado!</span>
                            @elseif($meta['porcentaje'] >= 100)
                                <span class="text-[9px] bg-emerald-500/10 text-emerald-600 font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Meta Cumplida</span>
                            @else
                                <span class="text-[9px] bg-amber-500/10 text-amber-600 font-black px-2 py-0.5 rounded-full uppercase tracking-widest">{{ $meta['porcentaje'] }}% Avance</span>
                            @endif
                        </div>

                        <!-- Comparación Numérica -->
                        <div class="grid grid-cols-2 gap-4 border-t border-b border-dashed border-zinc-200 dark:border-zinc-700 py-3">
                            <div>
                                <span class="text-[8px] text-zinc-400 font-bold uppercase tracking-wider block">Meta Oficial</span>
                                <span class="text-base font-black text-zinc-900 dark:text-white block">{{ number_format($meta['meta']) }} <span class="text-[10px] text-zinc-400 font-normal">elem.</span></span>
                            </div>
                            <div class="border-l border-zinc-200 dark:border-zinc-700 pl-4">
                                <span class="text-[8px] text-zinc-400 font-bold uppercase tracking-wider block">Capacitados</span>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400 block">{{ number_format($meta['actual']) }} <span class="text-[10px] text-zinc-400 font-normal">elem.</span></span>
                            </div>
                        </div>

                        <!-- Gráfico Comparativo Paralelo Integrado -->
                        <div class="space-y-2">
                            <!-- Barra Meta (Azul/Zinc) -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[8px] font-bold text-zinc-400 uppercase">
                                    <span>Meta Programada</span>
                                    <span>100%</span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-zinc-400 dark:bg-zinc-500 h-full w-full"></div>
                                </div>
                            </div>
                            
                            <!-- Barra Real (Emerald/Indigo) -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[8px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">
                                    <span>Avance Registrado</span>
                                    <span>{{ $meta['porcentaje'] }}%</span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $meta['porcentaje'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left border-collapse">
                                                    <thead>
                                                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/30 dark:bg-zinc-900/30">
                                                            <th class="px-4 py-2 text-[10px] font-bold uppercase text-zinc-500">Grupo / Curso</th>
                                                            <th class="px-4 py-2 text-[10px] font-bold uppercase text-zinc-500 text-center">Presentes</th>
                                                            <th class="px-4 py-2 text-[10px] font-bold uppercase text-zinc-500 text-center">Faltantes</th>
                                                            <th class="px-4 py-2 text-[10px] font-bold uppercase text-zinc-500 text-right">% Real</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                                            @foreach($plantel->gruposS_stats as $grupo)
                                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-900/30 transition-colors">
                                                    <td class="px-4 py-2">
                                                        <div class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200">{{ $grupo->nombre }}</div>
                                                        <div class="text-[9px] text-zinc-500 uppercase">{{ $grupo->curso?->nombre }}</div>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <flux:badge size="sm" color="green" inset="top bottom">{{ $grupo->presentes }}</flux:badge>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <flux:badge size="sm" :color="$grupo->faltantes > 0 ? 'red' : 'zinc'" inset="top bottom">{{ $grupo->faltantes }}</flux:badge>
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        <div class="text-[10px] font-black {{ $grupo->porcentaje > 80 ? 'text-emerald-500' : 'text-amber-500' }}">
                                                            {{ $grupo->porcentaje }}%
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
