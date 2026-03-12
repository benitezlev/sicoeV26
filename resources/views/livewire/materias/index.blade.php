<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Materia;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'materiaId' => null,
    'nombre' => '',
    'clave' => '',
    'num_horas' => 0,
    'descripcion' => '',
    'tipo' => 'teorica',
    'activo' => true,
]);

$materias = computed(function () {
    return Materia::query()
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('clave', 'like', '%' . $this->search . '%');
        })
        ->orderBy('nombre')
        ->paginate(15);
});

$abrirModalCrear = function () {
    $this->reset(['materiaId', 'nombre', 'clave', 'num_horas', 'descripcion', 'tipo', 'activo']);
    $this->dispatch('modal-show', name: 'modal-materia');
};

$editar = function (Materia $materia) {
    $this->fill([
        'materiaId' => $materia->id,
        'nombre' => $materia->nombre,
        'clave' => $materia->clave,
        'num_horas' => $materia->num_horas,
        'descripcion' => $materia->descripcion ?? '',
        'tipo' => $materia->tipo,
        'activo' => (bool) $materia->activo,
    ]);
    
    $this->dispatch('modal-show', name: 'modal-materia');
};

$guardar = function () {
    $rules = [
        'nombre' => 'required|string|max:255',
        'clave' => 'required|string|max:50|unique:materias,clave,' . ($this->materiaId ?? 'NULL'),
        'num_horas' => 'nullable|integer|min:0',
        'tipo' => 'required|in:teorica,practica,mixta',
        'activo' => 'boolean',
    ];

    $this->validate($rules);

    $datos = [
        'nombre' => $this->nombre,
        'clave' => $this->clave,
        'num_horas' => $this->num_horas,
        'descripcion' => $this->descripcion,
        'tipo' => $this->tipo,
        'activo' => $this->activo,
    ];

    if ($this->materiaId) {
        Materia::find($this->materiaId)->update($datos);
        Flux::toast(heading: 'Materia actualizada', variant: 'success');
    } else {
        Materia::create($datos);
        Flux::toast(heading: 'Materia creada', variant: 'success');
    }

    $this->dispatch('modal-hide', name: 'modal-materia');
};

$eliminar = function (Materia $materia) {
    // Verificar si tiene cursos asociados antes de eliminar (opcional pero recomendado)
    if ($materia->cursos()->exists()) {
        Flux::toast(heading: 'No se puede eliminar', text: 'Esta materia está asignada a uno o más cursos.', variant: 'danger');
        return;
    }

    $materia->delete();
    Flux::toast(heading: 'Materia eliminada', variant: 'success');
};

?>

<div class="space-y-6">
    <x-slot name="header">Catálogo de Materias</x-slot>

    <div class="flex justify-between items-center">
        <flux:heading size="xl">Gestión de Materias</flux:heading>
        <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear">Nueva Materia</flux:button>
    </div>

    <!-- Filtros -->
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre o clave..." icon="magnifying-glass" class="max-w-md w-full" />
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Clave</flux:table.column>
                <flux:table.column>Materia</flux:table.column>
                <flux:table.column align="center">Horas</flux:table.column>
                <flux:table.column align="center">Tipo</flux:table.column>
                <flux:table.column align="center">Estatus</flux:table.column>
                <flux:table.column align="center">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->materias as $materia)
                    <flux:table.row :key="$materia->id">
                        <flux:table.cell class="font-mono text-xs">{{ $materia->clave }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col min-w-48 text-wrap">
                                <span class="font-bold text-zinc-800 dark:text-white">{{ $materia->nombre }}</span>
                                <span class="text-xs text-zinc-400">{{ $materia->descripcion ?: 'Sin descripción' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="center">{{ $materia->num_horas }}</flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:badge size="sm" :color="match($materia->tipo) {
                                'teorica' => 'blue',
                                'practica' => 'green',
                                'mixta' => 'purple',
                                default => 'zinc'
                            }" variant="inset">
                                {{ ucfirst($materia->tipo) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:badge size="sm" :color="$materia->activo ? 'green' : 'red'" variant="pill">
                                {{ $materia->activo ? 'Activa' : 'Inactiva' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <div class="flex justify-center gap-1">
                                <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $materia->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="¿Estás seguro de eliminar esta materia?" wire:click="eliminar({{ $materia->id }})" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center" class="py-12 text-zinc-400">
                            No se encontraron materias cargadas.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->materias->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50">
                {{ $this->materias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario Materia -->
    <flux:modal name="modal-materia" class="max-w-lg">
        <form wire:submit="guardar" wire:key="form-materia-{{ $materiaId ?? 'new' }}" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $materiaId ? 'Editar Materia' : 'Nueva Materia' }}</flux:heading>
                <flux:subheading>Completa los datos de la asignatura.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nombre de la Materia</flux:label>
                    <flux:input wire:model="nombre" placeholder="Ej: Matemáticas I" />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>Clave</flux:label>
                    <flux:input wire:model="clave" placeholder="Ej: MAT-001" />
                    <flux:error name="clave" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de Horas</flux:label>
                    <flux:input type="number" wire:model="num_horas" min="0" />
                    <flux:error name="num_horas" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo de Materia</flux:label>
                    <flux:select wire:model="tipo">
                        <flux:select.option value="teorica">Teórica</flux:select.option>
                        <flux:select.option value="practica">Práctica</flux:select.option>
                        <flux:select.option value="mixta">Mixta</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Descripción (Opcional)</flux:label>
                <flux:textarea wire:model="descripcion" rows="3" />
                <flux:error name="descripcion" />
            </flux:field>

            <flux:checkbox wire:model="activo" label="Materia activa para el sistema" />

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Materia</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
