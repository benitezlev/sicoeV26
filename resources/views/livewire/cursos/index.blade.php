<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Curso;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'cursoId' => null,
    'identificador' => '',
    'nombre' => '',
    'tipo' => '',
    'num_horas' => 0,
    'descripcion' => '',
]);

$cursos = computed(function () {
    return Curso::query()
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('identificador', 'like', '%' . $this->search . '%');
        })
        ->orderBy('nombre')
        ->paginate(15);
});

$abrirModalCrear = function () {
    $this->reset(['cursoId', 'identificador', 'nombre', 'tipo', 'num_horas', 'descripcion']);
    $this->dispatch('modal-show', name: 'modal-curso');
};

$editar = function (Curso $curso) {
    $this->fill([
        'cursoId' => $curso->id,
        'identificador' => $curso->identificador,
        'nombre' => $curso->nombre,
        'tipo' => $curso->tipo,
        'num_horas' => $curso->num_horas,
        'descripcion' => $curso->descripcion ?? '',
    ]);
    
    $this->dispatch('modal-show', name: 'modal-curso');
};

$guardar = function () {
    $rules = [
        'identificador' => 'required|unique:cursos,identificador,' . ($this->cursoId ?? 'NULL'),
        'nombre' => 'required|string|max:255',
        'tipo' => 'required|string|max:50',
        'num_horas' => 'required|integer|min:1',
    ];

    $this->validate($rules);

    $datos = [
        'identificador' => $this->identificador,
        'nombre' => $this->nombre,
        'tipo' => $this->tipo,
        'num_horas' => $this->num_horas,
        'descripcion' => $this->descripcion,
    ];

    if ($this->cursoId) {
        Curso::find($this->cursoId)->update($datos);
        Flux::toast(heading: 'Curso actualizado', variant: 'success');
    } else {
        Curso::create($datos);
        Flux::toast(heading: 'Curso registrado', variant: 'success');
    }

    $this->dispatch('modal-hide', name: 'modal-curso');
};

$eliminar = function (Curso $curso) {
    if ($curso->materias()->exists()) {
        Flux::toast(heading: 'No se puede eliminar', text: 'Este curso tiene materias asignadas.', variant: 'danger');
        return;
    }

    $curso->delete();
    Flux::toast(heading: 'Curso eliminado', variant: 'success');
};

$exportarPDF = function () {
    return redirect()->route('cursos.exportar.pdf');
};

?>

<div class="space-y-6">
    <x-slot name="header">Catálogo de Cursos</x-slot>

    <div class="flex justify-between items-center">
        <flux:heading size="xl">Gestión de Cursos</flux:heading>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="document-text" wire:click="exportarPDF">Listado PDF</flux:button>
            <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear">Nuevo Curso</flux:button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre o identificador..." icon="magnifying-glass" class="max-w-md w-full" />
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>ID / Clave</flux:table.column>
                <flux:table.column>Curso / Oferta Académica</flux:table.column>
                <flux:table.column align="center">Tipo</flux:table.column>
                <flux:table.column align="center">Total Horas</flux:table.column>
                <flux:table.column align="center">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->cursos as $curso)
                    <flux:table.row :key="$curso->id">
                        <flux:table.cell class="font-mono text-xs">{{ $curso->identificador }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col min-w-64 text-wrap">
                                <span class="font-bold text-zinc-800 dark:text-white">{{ $curso->nombre }}</span>
                                <span class="text-xs text-zinc-400">{{ $curso->descripcion ?: 'Sin descripción adicional' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:badge size="sm" variant="subtle" color="zinc">{{ $curso->tipo }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="center" class="font-bold">{{ $curso->num_horas }} hrs</flux:table.cell>
                        <flux:table.cell align="center">
                            <div class="flex justify-center gap-1">
                                <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $curso->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="¿Estás seguro de eliminar este curso?" wire:click="eliminar({{ $curso->id }})" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                            No se encontraron cursos registrados.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->cursos->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50">
                {{ $this->cursos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario Curso -->
    <flux:modal name="modal-curso" class="max-w-lg">
        <form wire:submit="guardar" wire:key="form-curso-{{ $cursoId ?? 'new' }}" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $cursoId ? 'Editar Curso' : 'Nuevo Curso' }}</flux:heading>
                <flux:subheading>Define los parámetros generales del curso.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <flux:field>
                    <flux:label>Nombre del Curso</flux:label>
                    <flux:input wire:model="nombre" placeholder="Ej: Licenciatura en Seguridad" />
                    <flux:error name="nombre" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Identificador / Clave</flux:label>
                        <flux:input wire:model="identificador" placeholder="Ej: LIC-01" />
                        <flux:error name="identificador" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo</flux:label>
                        <flux:input wire:model="tipo" placeholder="Ej: Licenciatura" />
                        <flux:error name="tipo" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Número de Horas Totales</flux:label>
                    <flux:input type="number" wire:model="num_horas" min="1" />
                    <flux:error name="num_horas" />
                </flux:field>

                <flux:field>
                    <flux:label>Descripción</flux:label>
                    <flux:textarea wire:model="descripcion" rows="3" />
                    <flux:error name="descripcion" />
                </flux:field>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Curso</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
