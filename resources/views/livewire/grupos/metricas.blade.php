<?php

use function Livewire\Volt\{state, layout, mount, computed};
use App\Models\Grupo;
use App\Models\Curso;
use App\Models\Calificacion;

layout('layouts.app');

state(['grupo' => null]);

mount(function (Grupo $grupo) {
    $this->grupo = $grupo->load(['curso', 'alumnos', 'expediente', 'plantel']);
});

$totalAlumnos = computed(fn() => $this->grupo?->alumnos->count() ?? 0);
$alumnosAlta = computed(fn() => $this->grupo?->alumnos()->wherePivot('estado', 'activo')->count() ?? 0);
$alumnosBaja = computed(fn() => $this->grupo?->alumnos()->wherePivot('estado', 'baja')->count() ?? 0);
$documentos = computed(fn() => $this->grupo?->expediente->count() ?? 0);
$evaluacionesEmitidas = computed(fn() => Calificacion::where('grupo_id', $this->grupo?->id)->count());

?>

<div class="p-6">
    <x-slot name="header">Insights Operativos</x-slot>

    <div class="space-y-6">
        <!-- Header Interactivo -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Dashboard y Métricas Académicas</h1>
                </div>
                <div class="text-xs text-zinc-500 font-medium flex gap-2 items-center">
                    <flux:icon name="chart-pie" variant="mini" class="text-blue-500" />
                    Analítica procesada para el grupo de <strong class="uppercase text-blue-600 dark:text-blue-400">{{ $this->grupo->nombre }}</strong>
                </div>
            </div>
            
            <flux:button href="{{ route('grupos.show', $this->grupo->id) }}" variant="ghost" icon="arrow-left" class="text-[10px] uppercase font-black tracking-widest">
                Retornar al Expediente
            </flux:button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            
            <!-- Panel Izquierdo: Ficha Rápida & Donut Chart (Simulado) -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Ficha del Grupo -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 opacity-5">
                        <flux:icon name="building-office-2" class="size-32" />
                    </div>
                    
                    <flux:badge color="zinc" size="sm" class="mb-4">PERFIL DE CURSO</flux:badge>
                    
                    <div class="space-y-4 relative z-10">
                        <div>
                            <span class="block text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Programa Educativo</span>
                            <span class="block text-sm font-black text-zinc-800 dark:text-zinc-200 leading-tight mt-0.5">{{ $this->grupo->curso->nombre }}</span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Sede Asignada</span>
                            <span class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $this->grupo->plantel->name ?? $this->grupo->plantel->nombre }}</span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-zinc-400 uppercase tracking-widest">Periodo Académico</span>
                            <span class="block text-xs font-mono font-bold text-zinc-600 dark:text-zinc-400">{{ $this->grupo->periodo }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tasa de Retención (Widget Visual) -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col items-center text-center">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest w-full text-left mb-6">Tasa de Retención</span>
                    
                    @php
                        $retencion = $this->totalAlumnos > 0 ? round(($this->alumnosAlta / $this->totalAlumnos) * 100) : 0;
                        $color = $retencion >= 80 ? 'text-green-500' : ($retencion >= 50 ? 'text-amber-500' : 'text-red-500');
                    @endphp

                    <div class="relative size-32 mb-4 group cursor-help">
                        <svg class="size-full -rotate-90" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-zinc-100 dark:text-zinc-700/50" stroke-width="3.5" stroke-dasharray="100 100" stroke-linecap="round"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current {{ $color }} drop-shadow-md transition-all duration-1000 ease-in-out group-hover:drop-shadow-lg" stroke-width="3.5" stroke-dasharray="{{ $retencion }} 100" stroke-linecap="round"></circle>
                        </svg>
                        
                        <div class="absolute top-1/2 start-1/2 transform -translate-y-1/2 -translate-x-1/2 flex flex-col items-center">
                            <span class="text-3xl font-black text-zinc-800 dark:text-white tracking-tighter">{{ $retencion }}<span class="text-sm">%</span></span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-zinc-500 font-medium">Cadetes que permanecen activos respecto al total de matrículas expedidas.</p>
                </div>
            </div>

            <!-- Panel Derecho: Data Grid -->
            <div class="xl:col-span-3 space-y-6">
                
                <!-- Tarjetas de Totalizadores -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/10 p-5 rounded-3xl border border-blue-100 dark:border-blue-900/30 flex flex-col relative overflow-hidden shadow-sm group hover:scale-[1.02] transition-transform">
                        <div class="absolute -right-2 -bottom-2 bg-blue-500/10 dark:bg-blue-500/20 p-4 rounded-full group-hover:scale-110 transition-transform">
                            <flux:icon name="users" class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest mb-1">Matrícula Total</span>
                        <span class="text-3xl font-black text-blue-700 dark:text-blue-300 font-mono">{{ $this->totalAlumnos }}</span>
                        <span class="text-[9px] text-blue-500/70 uppercase font-black tracking-widest mt-2">Registros Históricos</span>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/10 p-5 rounded-3xl border border-green-100 dark:border-green-900/30 flex flex-col relative overflow-hidden shadow-sm group hover:scale-[1.02] transition-transform">
                        <div class="absolute -right-2 -bottom-2 bg-green-500/10 dark:bg-green-500/20 p-4 rounded-full group-hover:scale-110 transition-transform">
                            <flux:icon name="user-check" class="size-6 text-green-600 dark:text-green-400" />
                        </div>
                        <span class="text-[10px] text-green-600 dark:text-green-400 font-bold uppercase tracking-widest mb-1">Alta Activa</span>
                        <span class="text-3xl font-black text-green-700 dark:text-green-300 font-mono">{{ $this->alumnosAlta }}</span>
                        <span class="text-[9px] text-green-500/70 uppercase font-black tracking-widest mt-2">En proceso de Formación</span>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/10 p-5 rounded-3xl border border-red-100 dark:border-red-900/30 flex flex-col relative overflow-hidden shadow-sm group hover:scale-[1.02] transition-transform">
                        <div class="absolute -right-2 -bottom-2 bg-red-500/10 dark:bg-red-500/20 p-4 rounded-full group-hover:scale-110 transition-transform">
                            <flux:icon name="user-minus" class="size-6 text-red-600 dark:text-red-400" />
                        </div>
                        <span class="text-[10px] text-red-600 dark:text-red-400 font-bold uppercase tracking-widest mb-1">Bajas / Deserciones</span>
                        <span class="text-3xl font-black text-red-700 dark:text-red-300 font-mono">{{ $this->alumnosBaja }}</span>
                        <span class="text-[9px] text-red-500/70 uppercase font-black tracking-widest mt-2">Incidencias Críticas</span>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-800/50 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 flex flex-col relative overflow-hidden shadow-sm group hover:scale-[1.02] transition-transform">
                        <div class="absolute -right-2 -bottom-2 bg-zinc-200/50 dark:bg-zinc-700/50 p-4 rounded-full group-hover:scale-110 transition-transform">
                            <flux:icon name="document-duplicate" class="size-6 text-zinc-500 dark:text-zinc-400" />
                        </div>
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-widest mb-1">Volumen Documental</span>
                        <span class="text-3xl font-black text-zinc-700 dark:text-zinc-300 font-mono">{{ $this->documentos }}</span>
                        <span class="text-[9px] text-zinc-400 uppercase font-black tracking-widest mt-2">Archivos en Plataforma</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-amber-500/10 rounded-xl">
                            <flux:icon name="bolt" variant="mini" class="text-amber-500" />
                        </div>
                        <h2 class="text-sm font-black text-zinc-800 dark:text-white uppercase tracking-wider">Actividad Académica Registrada</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-900/40 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                            <div class="flex items-center gap-4">
                                <div class="size-10 bg-white dark:bg-zinc-800 rounded-xl shadow-sm flex items-center justify-center border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon name="pencil-square" class="size-5 text-zinc-500" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-tight">Evaluaciones Emitidas</span>
                                    <span class="text-[10px] text-zinc-500">Calificaciones parciales/Cursos capturados</span>
                                </div>
                            </div>
                            <span class="text-xl font-black text-zinc-800 dark:text-white font-mono">{{ $this->evaluacionesEmitidas }}</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-900/40 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                            <div class="flex items-center gap-4">
                                <div class="size-10 bg-white dark:bg-zinc-800 rounded-xl shadow-sm flex items-center justify-center border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon name="flag" class="size-5 text-blue-500" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-tight">Estatus de Titulación</span>
                                    <span class="text-[10px] text-zinc-500">Avance procedimental SICOE</span>
                                </div>
                            </div>
                            <span class="text-[10px] px-3 py-1 font-black bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg uppercase tracking-widest">{{ $this->grupo->estado }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-4 bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100/50 dark:border-blue-900/30 rounded-2xl flex gap-3 text-sm">
                        <flux:icon name="information-circle" class="size-5 text-blue-500 shrink-0 mt-0.5" />
                        <div class="text-zinc-600 dark:text-zinc-400">
                            <p class="font-bold text-blue-800 dark:text-blue-300 mb-1 text-xs uppercase tracking-widest">Desarrollo Futuro</p>
                            <p class="text-[11px] leading-relaxed">
                                Estas métricas provienen de datos orgánicos crudos de la base de datos de <strong>SICOE</strong>. En versiones posteriores de esta interfaz, este dashboard interactuará dinámicamente con los módulos de <em>Asistencias Diarias</em> para generar gráficas de calor (heatmaps) sobre los meses más conflictivos por deserción y analítica sobre qué materias presentan mayor riesgo de reprobación.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
