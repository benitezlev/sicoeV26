<?php

use function Livewire\Volt\{state, computed, mount, updated};
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\User;
use App\Models\Calificacion;
use Flux\Flux;

use function Livewire\Volt\{layout};

layout('layouts.app');

state([
    'grupo_id' => '',
    'materia_id' => '',
    'unidad' => '1',
    'notas' => [], // [user_id => calificacion]
    'observaciones' => [], // [user_id => observation]
]);

$grupos = computed(fn() => Grupo::where('estado', 'activo')->get());

$materias = computed(function() {
    if (!$this->grupo_id) return [];
    $grupo = Grupo::find($this->grupo_id);
    return $grupo ? $grupo->curso->materias : [];
});

$alumnos = computed(function() {
    if (!$this->grupo_id) return [];
    return Grupo::find($this->grupo_id)->alumnos;
});

mount(function() {
    //
});

updated(['grupo_id', 'materia_id', 'unidad'], function() {
    $this->loadNotas();
});

$loadNotas = function() {
    if (!$this->grupo_id || !$this->materia_id || !$this->unidad) {
        $this->notas = [];
        return;
    }

    $existing = Calificacion::where('grupo_id', $this->grupo_id)
        ->where('materia_id', $this->materia_id)
        ->where('unidad', $this->unidad)
        ->get();

    $this->notas = $existing->pluck('calificacion', 'user_id')->toArray();
    $this->observaciones = $existing->pluck('observaciones', 'user_id')->toArray();
};

$guardar = function() {
    $this->validate([
        'grupo_id' => 'required',
        'materia_id' => 'required',
        'unidad' => 'required',
        'notas.*' => 'nullable|numeric|min:0|max:10',
    ]);

    foreach ($this->notas as $userId => $nota) {
        if ($nota === null || $nota === '') continue;

        Calificacion::updateOrCreate(
            [
                'user_id' => $userId,
                'grupo_id' => $this->grupo_id,
                'materia_id' => $this->materia_id,
                'unidad' => $this->unidad,
            ],
            [
                'calificacion' => $nota,
                'observaciones' => $this->observaciones[$userId] ?? null,
                'registrado_por' => auth()->id(),
            ]
        );
    }

    Flux::toast(heading: 'Calificaciones guardadas correctamente', variant: 'success');
};

?>

<div class="space-y-6">
    <x-slot name="header">Captura de Calificaciones</x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <flux:select wire:model.live="grupo_id" label="Seleccionar Grupo" placeholder="Elija un grupo...">
            @foreach ($this->grupos as $grupo)
                <flux:select.option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->periodo }})</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="materia_id" label="Seleccionar Materia" :disabled="!$grupo_id" placeholder="Elija una materia...">
            @foreach ($this->materias as $materia)
                <flux:select.option value="{{ $materia->id }}">{{ $materia->nombre }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="unidad" label="Unidad / Evaluación">
            <flux:select.option value="1">Unidad 1</flux:select.option>
            <flux:select.option value="2">Unidad 2</flux:select.option>
            <flux:select.option value="3">Unidad 3</flux:select.option>
            <flux:select.option value="PROMEDIO_FINAL">Promedio Final</flux:select.option>
            <flux:select.option value="EXTRAORDINARIO">Extraordinario</flux:select.option>
        </flux:select>
    </div>

    @if($grupo_id && $materia_id)
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Alumno</flux:table.column>
                    <flux:table.column>CURP</flux:table.column>
                    <flux:table.column align="center" class="w-24">Calificación</flux:table.column>
                    <flux:table.column>Observaciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->alumnos as $alumno)
                        <flux:table.row :key="$alumno->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar src="{{ $alumno->profile_photo_url }}" size="xs" />
                                    <span class="font-medium">{{ $alumno->nombre_completo }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-500 font-mono">{{ $alumno->curp }}</flux:cell>
                            <flux:table.cell>
                                <flux:input type="number" step="0.1" min="0" max="10" wire:model.defer="notas.{{ $alumno->id }}" class="text-center" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model.defer="observaciones.{{ $alumno->id }}" placeholder="Opcional..." />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="p-4 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-700 flex justify-between">
                <flux:button 
                    href="{{ route('calificaciones.acta', ['grupo_id' => $grupo_id, 'materia_id' => $materia_id, 'unidad' => $unidad]) }}" 
                    variant="ghost" 
                    icon="document-arrow-down"
                >
                    Generar Acta (PDF)
                </flux:button>
                <flux:button variant="primary" icon="check" wire:click="guardar">Guardar Calificaciones</flux:button>
            </div>
        </div>
    @else
        <div class="py-20 text-center bg-zinc-50 dark:bg-zinc-900/50 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
            <flux:icon name="academic-cap" variant="outline" class="mx-auto h-12 w-12 text-zinc-300" />
            <p class="mt-4 text-zinc-500">Seleccione un grupo y una materia para comenzar la captura.</p>
        </div>
    @endif
</div>
