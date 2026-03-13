<?php

use function Livewire\Volt\{state, computed, updated, layout};
layout('layouts.app');
use App\Models\Grupo;
use App\Models\User;
use App\Models\AsistenciaIndividual;
use App\Models\Asistencia;
use Carbon\Carbon;

state([
    'grupoId' => '',
    'fecha' => date('Y-m-d'),
    'asistencias' => [],
    'observaciones' => [],
]);

$grupos = computed(fn() => \App\Models\Grupo::all());

$cargaAsistencias = function () {
    if (!$this->grupoId) return;

    $this->asistencias = [];
    $this->observaciones = [];

    $alumnos = User::role('alumno')
        ->whereHas('grupos', fn($q) => $q->where('grupos.id', $this->grupoId))
        ->get();

    $registrosExistentes = AsistenciaIndividual::where('grupo_id', $this->grupoId)
        ->whereDate('fecha', $this->fecha)
        ->get()
        ->keyBy('user_id');

    foreach ($alumnos as $alumno) {
        $this->asistencias[$alumno->id] = $registrosExistentes->has($alumno->id) 
            ? $registrosExistentes[$alumno->id]->estatus 
            : 'falta';
        $this->observaciones[$alumno->id] = $registrosExistentes->has($alumno->id) 
            ? $registrosExistentes[$alumno->id]->observaciones 
            : '';
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

    $this->dispatch('toast', heading: 'Estado de Fuerza actualizado', variant: 'success');
};

$listadoAlumnos = computed(function() {
    if (!$this->grupoId) return collect();

    return User::role('alumno')
        ->whereHas('grupos', fn($q) => $q->where('grupos.id', $this->grupoId))
        ->with(['grupos' => fn($q) => $q->where('grupos.id', $this->grupoId)])
        ->get()
        ->map(function($user) {
            $user->estado_en_grupo = $user->grupos->first()->pivot->estado ?? 'activo';
            return $user;
        });
});

?>

<div class="space-y-6">
    <flux:card class="p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <flux:select wire:model.live="grupoId" label="Seleccionar Grupo" placeholder="Escoge un grupo..." class="flex-1">
                @foreach($this->grupos as $grupo)
                    <flux:select.option value="{{ (string)$grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->periodo }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.live="fecha" label="Fecha de Pase de Lista" />

            <flux:button variant="primary" wire:click="cargaAsistencias" icon="magnifying-glass">Cargar Lista</flux:button>
        </div>
        
        @if(config('app.debug'))
            <div class="mt-2 text-[10px] text-zinc-400">DEBUG: Grupo ID seleccionado = "{{ $grupoId }}"</div>
        @endif
    </flux:card>

    @if($grupoId && count($this->listadoAlumnos) > 0)
        <div class="bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50">
                <div>
                    <flux:heading size="lg">Control de Asistencia Individual</flux:heading>
                    <flux:subheading>Estado de Fuerza para el día {{ Carbon::parse($fecha)->format('d/m/Y') }}</flux:subheading>
                </div>
                <flux:button variant="primary" icon="check" wire:click="save">Guardar Estado de Fuerza</flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Alumno</flux:table.column>
                    <flux:table.column>Nivel</flux:table.column>
                    <flux:table.column>Estatus en Grupo</flux:table.column>
                    <flux:table.column align="center">Asistencia</flux:table.column>
                    <flux:table.column>Observaciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->listadoAlumnos as $alumno)
                        <flux:table.row :key="$alumno->id">
                            <flux:table.cell>
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $alumno->nombre_completo }}</div>
                                <div class="text-[10px] text-zinc-400">{{ $alumno->curp }}</div>
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom">{{ strtoupper($alumno->nivel) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$alumno->estado_en_grupo === 'activo' ? 'green' : 'red'" variant="pill">
                                    {{ ucfirst($alumno->estado_en_grupo) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($alumno->estado_en_grupo === 'baja')
                                    <div class="flex justify-center">
                                        <flux:badge color="zinc" variant="solid">BAJA</flux:badge>
                                    </div>
                                @else
                                    <div class="flex justify-center">
                                        <div class="flex bg-zinc-100 dark:bg-zinc-700 p-1 rounded-xl gap-1">
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'presente')" 
                                                class="px-3 py-1 rounded-lg text-[10px] font-bold transition-all {{ ($asistencias[$alumno->id] ?? '') === 'presente' ? 'bg-emerald-500 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-600' }}">
                                                P
                                            </button>
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'falta')" 
                                                class="px-3 py-1 rounded-lg text-[10px] font-bold transition-all {{ ($asistencias[$alumno->id] ?? '') === 'falta' ? 'bg-red-500 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-600' }}">
                                                F
                                            </button>
                                            <button wire:click="$set('asistencias.{{ $alumno->id }}', 'permiso')" 
                                                class="px-3 py-1 rounded-lg text-[10px] font-bold transition-all {{ ($asistencias[$alumno->id] ?? '') === 'permiso' ? 'bg-amber-500 text-white shadow-sm' : 'text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-600' }}">
                                                J
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:input size="sm" wire:model="observaciones.{{ $alumno->id }}" placeholder="Ej. Permiso médico..." />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-700 flex justify-end">
                <flux:button variant="primary" icon="check" wire:click="save">Guardar Estado de Fuerza</flux:button>
            </div>
        </div>
    @elseif($grupoId)
        <flux:card class="p-12 text-center">
            <flux:icon name="user-group" class="mx-auto w-12 h-12 text-zinc-300 mb-4" />
            <flux:heading>No se encontraron alumnos</flux:heading>
            <flux:subheading>Verifica que el grupo tenga alumnos inscritos y que tu cuenta tenga permisos para verlos.</flux:subheading>
            
            <div class="mt-4 text-[10px] text-zinc-400 font-mono">
                GRUPO_ID: {{ $grupoId }} | ROL: {{ auth()->user()->roles?->first()?->name }}
            </div>
        </flux:card>
    @endif
</div>
