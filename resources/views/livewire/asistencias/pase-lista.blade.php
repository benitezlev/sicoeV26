<?php

use function Livewire\Volt\{state, computed, updated};
use App\Models\Grupo;
use App\Models\User;
use App\Models\AsistenciaIndividual;
use App\Models\Asistencia;
use Carbon\Carbon;

state([
    'grupoId' => null,
    'fecha' => date('Y-m-d'),
    'asistencias' => [], // [user_id => estatus]
    'observaciones' => [],
]);

$grupos = computed(fn() => Grupo::all());

$cargaAsistencias = function () {
    if (!$this->grupoId) return;

    $alumnos = User::whereHas('roles', fn($q) => $q->where('name', 'alumno'))
        ->whereHas('grupos', fn($q) => $q->where('grupo_id', $this->grupoId))
        ->get();

    $registrosExistentes = AsistenciaIndividual::where('grupo_id', $this->grupoId)
        ->whereDate('fecha', $this->fecha)
        ->get()
        ->keyBy('user_id');

    $this->asistencias = [];
    $this->observaciones = [];

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
    foreach ($this->asistencias as $userId => $estatus) {
        AsistenciaIndividual::updateOrCreate(
            ['user_id' => $userId, 'grupo_id' => $this->grupoId, 'fecha' => $this->fecha],
            ['estatus' => $estatus, 'observaciones' => $this->observaciones[$userId] ?? '']
        );
    }

    Flux::toast(
        heading: 'Pase de lista guardado',
        text: 'El Estado de Fuerza ha sido actualizado.',
        variant: 'success',
    );
};

$listadoAlumnos = computed(function() {
    if (!$this->grupoId) return collect();

    // Obtener alumnos con el estado en que están en el grupo (pivot)
    return User::whereHas('grupos', fn($q) => $q->where('grupo_id', $this->grupoId))
        ->with(['grupos' => fn($q) => $q->where('grupo_id', $this->grupoId)])
        ->get()
        ->map(function($user) {
            $user->estado_en_grupo = $user->grupos->first()->pivot->estado;
            return $user;
        });
});

?>

<div class="space-y-6">
    <flux:card class="p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <flux:select wire:model.live="grupoId" label="Seleccionar Grupo" placeholder="Escoge un grupo..." class="flex-1">
                @foreach($this->grupos as $grupo)
                    <flux:select.option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->periodo }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.live="fecha" label="Fecha de Pase de Lista" />

            <flux:button variant="primary" wire:click="cargaAsistencias" icon="magnifying-glass">Cargar Lista</flux:button>
        </div>
    </flux:card>

    @if($grupoId)
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
    @endif
</div>
