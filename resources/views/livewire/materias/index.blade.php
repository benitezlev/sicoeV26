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
    'num_horas' => '',
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
    $this->resetErrorBag();
    $this->reset(['materiaId', 'nombre', 'clave', 'num_horas', 'descripcion', 'tipo', 'activo']);
    $this->dispatch('modal-show', name: 'modal-materia');
};

$editar = function (Materia $materia) {
    $this->resetErrorBag();
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
        Flux::toast(heading: 'Materia actualizada', text: 'La información se ha actualizado en el catálogo.', variant: 'success');
    } else {
        Materia::create($datos);
        Flux::toast(heading: 'Materia creada', text: 'La nueva materia ha sido registrada exitosamente.', variant: 'success');
    }

    $this->dispatch('modal-hide', name: 'modal-materia');
};

$eliminar = function ($id) {
    $materia = Materia::findOrFail($id);
    
    // Verificar si tiene cursos asociados antes de eliminar
    if ($materia->cursos()->exists()) {
        Flux::toast(heading: 'Error', text: 'Esta materia está asignada a uno o más programas.', variant: 'danger');
        return;
    }

    $materia->delete();
    Flux::toast(heading: 'Materia eliminada', text: 'El registro se ha borrado de forma permanente.', variant: 'success');
};

?>

<div class="p-6">
    <x-slot name="header">Catálogo de Materias</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Catálogo de Materias</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Gestiona las asignaturas que conforman los programas educativos.</p>
            </div>
            
            <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear" size="sm">Nueva Materia</flux:button>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 items-end bg-white dark:bg-zinc-800 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="flex-1 min-w-[300px]">
                <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre o clave..." icon="magnifying-glass" />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Clave</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Materia</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Horas</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Tipo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Estatus</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->materias as $materia)
                        <tr wire:key="materia-{{ $materia->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[10px] font-mono border border-zinc-200 dark:border-zinc-700">{{ $materia->clave }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-48 text-wrap leading-tight">
                                    <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-sm">{{ $materia->nombre }}</span>
                                    <span class="text-[10px] text-zinc-500 italic mt-0.5">{{ $materia->descripcion ?: 'Sin descripción adicional' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-zinc-700 dark:text-white">{{ $materia->num_horas ?: '-' }}<span class="text-[10px] text-zinc-400 font-medium ml-1">hrs</span></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $tipoColor = match($materia->tipo) {
                                        'teorica' => 'bg-blue-50 text-blue-600 border-blue-200 dark:border-blue-900/30 dark:bg-blue-900/20 dark:text-blue-400',
                                        'practica' => 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:border-emerald-900/30 dark:bg-emerald-900/20 dark:text-emerald-400',
                                        'mixta' => 'bg-purple-50 text-purple-600 border-purple-200 dark:border-purple-900/30 dark:bg-purple-900/20 dark:text-purple-400',
                                        default => 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase border {{ $tipoColor }}">
                                    {{ $materia->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase border {{ $materia->activo ? 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-900/30' : 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30' }}">
                                    {{ $materia->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-1 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $materia->id }})" wire:loading.attr="disabled" />
                                    <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-{{ $materia->id }}' })" />
                                </div>

                                <!-- Modal de eliminación -->
                                <div x-data="{ open: false }" 
                                     x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-{{ $materia->id }}') open = true" 
                                     x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-{{ $materia->id }}') open = false" 
                                     x-show="open" x-cloak 
                                     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                    <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                        <div class="space-y-2">
                                            <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Materia?</h3>
                                            <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                                Estás por eliminar la materia <span class="font-bold text-zinc-900 dark:text-white underline">{{ $materia->nombre }}</span>. 
                                                Esta acción es irreversible.
                                            </p>
                                        </div>

                                        <div class="flex gap-3 justify-end pt-2">
                                            <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                            <flux:button type="button" variant="danger" wire:click="eliminar({{ $materia->id }})" x-on:click="open = false" class="font-black uppercase tracking-widest text-[10px]">Confirmar Baja</flux:button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center text-zinc-400">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon name="magnifying-glass" class="w-8 h-8 opacity-20" />
                                    <span class="italic text-sm text-zinc-400">No se encontraron materias registradas.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->materias->hasPages())
            <div class="px-2">
                {{ $this->materias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-materia') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-materia') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="guardar" wire:key="form-materia-{{ $materiaId ?? 'new' }}" class="space-y-8">
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $materiaId ? 'Editar Materia' : 'Nueva Materia' }}</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Completa los datos académicos de la asignatura.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <flux:field>
                        <flux:label>Nombre de la Materia</flux:label>
                        <flux:input wire:model="nombre" placeholder="Ej: Introducción al Derecho" wire:key="mat-nombre-{{ $materiaId ?? 'new' }}" />
                        <flux:error name="nombre" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Clave</flux:label>
                        <flux:input wire:model="clave" placeholder="Ej: DER-101" class="uppercase" wire:key="mat-clave-{{ $materiaId ?? 'new' }}" />
                        <flux:error name="clave" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Número de Horas</flux:label>
                        <flux:input type="number" wire:model="num_horas" min="0" icon="clock" placeholder="40" wire:key="mat-horas-{{ $materiaId ?? 'new' }}" />
                        <flux:error name="num_horas" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo de Materia</flux:label>
                        <flux:select wire:model="tipo" wire:key="mat-tipo-{{ $materiaId ?? 'new' }}">
                            <flux:select.option value="teorica">Teórica</flux:select.option>
                            <flux:select.option value="practica">Práctica</flux:select.option>
                            <flux:select.option value="mixta">Mixta</flux:select.option>
                        </flux:select>
                        <flux:error name="tipo" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Descripción (Opcional)</flux:label>
                    <flux:textarea wire:model="descripcion" rows="3" placeholder="Información sobre objetivos o contenido de la materia..." wire:key="mat-desc-{{ $materiaId ?? 'new' }}" />
                    <flux:error name="descripcion" />
                </flux:field>

                <flux:checkbox wire:model="activo" label="Materia activa para el sistema y asignación a programas" wire:key="mat-activo-{{ $materiaId ?? 'new' }}" />

                <div class="flex gap-3 justify-end pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        {{ $materiaId ? 'Guardar Cambios' : 'Generar Materia' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
