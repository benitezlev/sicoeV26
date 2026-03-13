<?php

use function Livewire\Volt\{state, computed, updated, layout};
layout('layouts.app');
use App\Models\Grupo;
use App\Models\User;
use App\Models\AsistenciaIndividual;
use App\Models\Asistencia;
use Carbon\Carbon;
use Flux\Flux;

state([
    'grupoId' => '',
    'fecha' => date('Y-m-d'),
    'asistencias' => [],
    'observaciones' => [],
]);

$grupos = computed(fn() => \App\Models\Grupo::orderBy('nombre')->get());

$cargaAsistencias = function () {
    if (!$this->grupoId) {
        Flux::toast(heading: 'Atención', text: 'Selecciona un grupo para cargar la lista.', variant: 'warning');
        return;
    }

    $this->asistencias = [];
    $this->observaciones = [];

    // Ajustado rol si tu sistema usa Laratrust o Spatie
    $alumnos = User::whereHas('roles', fn($q) => $q->where('name', 'alumno'))
        ->whereHas('grupos', fn($q) => $q->where('grupos.id', $this->grupoId))
        ->get();

    $registrosExistentes = AsistenciaIndividual::where('grupo_id', $this->grupoId)
        ->whereDate('fecha', $this->fecha)
        ->get()
        ->keyBy('user_id');

    foreach ($alumnos as $alumno) {
        $this->asistencias[$alumno->id] = $registrosExistentes->has($alumno->id) 
            ? $registrosExistentes[$alumno->id]->estatus 
            : 'falta'; // Falta por defecto
        $this->observaciones[$alumno->id] = $registrosExistentes->has($alumno->id) 
            ? $registrosExistentes[$alumno->id]->observaciones 
            : '';
    }
    
    if($alumnos->count() > 0) {
        Flux::toast(heading: 'Lista Cargada', text: 'Se ha cargado el padrón de alumnos.', variant: 'success');
    }
};

$save = function () {
    if (!$this->grupoId) return;
    
    foreach ($this->asistencias as $userId => $estatus) {
        AsistenciaIndividual::updateOrCreate(
            ['user_id' => $userId, 'grupo_id' => $this->grupoId, 'fecha' => $this->fecha],
            ['estatus' => $estatus, 'observaciones' => $this->observaciones[$userId] ?? '']
        );
    }

    Flux::toast(heading: 'Estado de Fuerza Actualizado', text: 'El pase de lista ha sido registrado correctamente.', variant: 'success');
};

$listadoAlumnos = computed(function() {
    if (!$this->grupoId) return collect();

    return User::whereHas('roles', fn($q) => $q->where('name', 'alumno'))
        ->whereHas('grupos', fn($q) => $q->where('grupos.id', $this->grupoId))
        ->with(['grupos' => fn($q) => $q->where('grupos.id', $this->grupoId)])
        ->get()
        ->map(function($user) {
            $user->estado_en_grupo = $user->grupos->first()->pivot->estado ?? 'activo';
            return $user;
        });
});

?>

<div class="p-6">
    <x-slot name="header">Validación Diaria y Estado de Fuerza</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Pase de Lista Operativo</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Control nominal diario de asistencia y registro de incidencias (Estado de Fuerza).</p>
            </div>
            
            @if($grupoId && count($this->listadoAlumnos) > 0)
                <flux:button variant="primary" icon="check-circle" wire:click="save" class="font-black uppercase tracking-widest text-[10px]">
                    Confirmar y Guardar Estado
                </flux:button>
            @endif
        </div>

        <!-- Panel de Configuración de Lista -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <flux:field>
                        <flux:label>Seleccionar Grupo Escolar</flux:label>
                        <flux:select wire:model.live="grupoId" placeholder="-- Escoge un grupo activo --" searchable icon="users">
                            @foreach($this->grupos as $grupo)
                                <flux:select.option value="{{ (string)$grupo->id }}">
                                    {{ $grupo->nombre }} ({{ $grupo->periodo }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <div class="w-full md:w-64">
                    <flux:field>
                        <flux:label>Fechado de Lista</flux:label>
                        <flux:input type="date" wire:model.live="fecha" max="{{ date('Y-m-d') }}" />
                    </flux:field>
                </div>

                <flux:button variant="primary" wire:click="cargaAsistencias" class="w-full md:w-auto h-[42px] px-8 font-bold uppercase text-[10px] tracking-widest whitespace-nowrap">
                    Generar Formato
                </flux:button>
            </div>
            
            @if(config('app.debug'))
                <div class="mt-4 p-2 bg-zinc-900 text-green-400 font-mono text-[10px] rounded-lg">
                    [DEBUG_MODE] Grupo_ID: {{ $grupoId ?: 'NULL' }} | Fecha: {{ $fecha }}
                </div>
            @endif
        </div>

        <!-- Render de Lista de Asistencia -->
        @if($grupoId && count($this->listadoAlumnos) > 0)
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl shadow-sm overflow-hidden overflow-x-auto">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20">
                            <flux:icon name="clipboard-document-check" variant="mini" class="text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Padrón Nominal</h2>
                            <p class="text-[11px] text-zinc-500 font-bold tracking-widest uppercase">
                                Reporte para el día <span class="text-blue-600 dark:text-blue-400">{{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \d\e F, Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Perfil del Cadete / Alumno</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Rango / Nivel</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Estatus Matricula</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Control Biométrico / Pase</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Justificación / Novedades</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($this->listadoAlumnos as $alumno)
                            <tr wire:key="alm-asist-{{ $alumno->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors {{ ($asistencias[$alumno->id] ?? '') === 'falta' ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-xl bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 font-black text-sm shadow-inner border border-zinc-300 dark:border-zinc-600">
                                            {{ substr($alumno->name ?? $alumno->nombre, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-zinc-900 dark:text-white uppercase text-sm leading-tight">{{ $alumno->name ?? $alumno->nombre }}</span>
                                            <span class="text-[10px] text-zinc-500 font-mono mt-0.5 tracking-wider">{{ $alumno->curp ?? 'SIN CURP' }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-[9px] uppercase tracking-widest rounded border border-zinc-200 dark:border-zinc-700">
                                        {{ $alumno->nivel ?? 'BÁSICO' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $estadoColor = match($alumno->estado_en_grupo) {
                                            'activo' => 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-900/30',
                                            'baja' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30',
                                            default => 'bg-zinc-50 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400'
                                        };
                                    @endphp
                                    <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $estadoColor }}">
                                        {{ $alumno->estado_en_grupo }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($alumno->estado_en_grupo === 'baja')
                                        <span class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-400 font-black text-[10px] rounded-xl uppercase tracking-widest border border-dashed border-zinc-300 dark:border-zinc-700">
                                            Baja Definitiva
                                        </span>
                                    @else
                                        <div class="inline-flex bg-zinc-100 dark:bg-zinc-800/80 p-1.5 rounded-2xl gap-1 border border-zinc-200 dark:border-zinc-700 shadow-inner">
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'presente')" 
                                                class="w-10 h-8 flex items-center justify-center rounded-xl text-xs font-black transition-all duration-200 outline-none
                                                {{ ($asistencias[$alumno->id] ?? '') === 'presente' 
                                                    ? 'bg-green-500 text-white shadow-md shadow-green-500/30 scale-105' 
                                                    : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
                                                    title="Asistencia Plena">
                                                P
                                            </button>
                                            
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'falta')" 
                                                class="w-10 h-8 flex items-center justify-center rounded-xl text-xs font-black transition-all duration-200 outline-none
                                                {{ ($asistencias[$alumno->id] ?? '') === 'falta' 
                                                    ? 'bg-red-500 text-white shadow-md shadow-red-500/30 scale-105' 
                                                    : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-red-500/50 dark:hover:text-red-400/50' }}"
                                                    title="Inasistencia Injustificada">
                                                F
                                            </button>
                                            
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'permiso')" 
                                                class="w-10 h-8 flex items-center justify-center rounded-xl text-xs font-black transition-all duration-200 outline-none
                                                {{ ($asistencias[$alumno->id] ?? '') === 'permiso' 
                                                    ? 'bg-amber-500 text-white shadow-md shadow-amber-500/30 scale-105' 
                                                    : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-amber-500/50 dark:hover:text-amber-400/50' }}"
                                                    title="Baja Médica / Permiso">
                                                J
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($alumno->estado_en_grupo !== 'baja')
                                        <input type="text" wire:model.defer="observaciones.{{ $alumno->id }}" placeholder="Ej. Retardo, incapacidad médica..." 
                                            class="w-full bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-0 text-sm py-2 px-1 text-zinc-900 dark:text-white transition-colors" />
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600 block w-8 h-0.5 bg-current rounded-full"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-700 flex justify-end">
                    <flux:button variant="primary" icon="check-circle" wire:click="save" class="font-black uppercase tracking-widest text-[11px] px-8">
                        Persistir Estado de Fuerza
                    </flux:button>
                </div>
            </div>
            
        @elseif($grupoId)
            <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/20 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 opacity-80 mt-8">
                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                    <flux:icon name="user-group" class="size-8 text-zinc-400" />
                </div>
                <span class="text-zinc-600 dark:text-zinc-400 font-black uppercase tracking-wider text-sm mb-1">Sin Matrícula Activa</span>
                <span class="text-xs text-zinc-500 font-medium max-w-sm text-center">El grupo seleccionado no posee cadetes en alta. Verifica la asignación en el módulo de Grupos.</span>
                
                @if(config('app.debug'))
                    <div class="mt-6 px-4 py-2 bg-zinc-200 dark:bg-zinc-950 text-[10px] text-zinc-500 font-mono rounded-lg border border-zinc-300 dark:border-zinc-800">
                        DIAGNÓSTICO: GRUPO_ID={{ $grupoId }} | ROL_ACTUAL={{ auth()->user()->roles?->first()?->name ?? 'NINGUNO' }} | USERS_FOUND=0
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
