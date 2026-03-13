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
    'num_horas' => '',
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
    $this->resetErrorBag();
    $this->reset(['cursoId', 'identificador', 'nombre', 'tipo', 'num_horas', 'descripcion']);
    $this->dispatch('modal-show', name: 'modal-curso');
};

$editar = function (Curso $curso) {
    $this->resetErrorBag();
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
        Flux::toast(heading: 'Curso actualizado', text: 'Los datos del curso se actualizaron correctamente.', variant: 'success');
    } else {
        Curso::create($datos);
        Flux::toast(heading: 'Curso registrado', text: 'El nuevo curso se ha guardado en el catálogo.', variant: 'success');
    }

    $this->dispatch('modal-hide', name: 'modal-curso');
};

$eliminar = function ($id) {
    $curso = Curso::findOrFail($id);
    if ($curso->materias()->exists()) {
        Flux::toast(heading: 'Error', text: 'Este curso tiene materias asignadas.', variant: 'danger');
        return;
    }

    $curso->delete();
    Flux::toast(heading: 'Curso eliminado', text: 'El curso ha sido eliminado permanentemente.', variant: 'success');
};

$exportarPDF = function () {
    return redirect()->route('cursos.exportar.pdf');
};

?>

<div class="p-6">
    <x-slot name="header">Catálogo de Cursos</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Catálogo de Cursos</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Administra la oferta académica y programas educativos.</p>
            </div>
            
            <div class="flex gap-2">
                <flux:button variant="ghost" icon="document-text" wire:click="exportarPDF" size="sm">Listado PDF</flux:button>
                <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear" size="sm">Nuevo Curso</flux:button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 items-end bg-white dark:bg-zinc-800 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="flex-1 min-w-[300px]">
                <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre o identificador..." icon="magnifying-glass" />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">ID / Clave</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Curso / Oferta Académica</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Tipo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Total Horas</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->cursos as $curso)
                        <tr wire:key="curso-{{ $curso->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[10px] font-mono border border-zinc-200 dark:border-zinc-700">{{ $curso->identificador }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-64 text-wrap leading-tight">
                                    <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-sm">{{ $curso->nombre }}</span>
                                    <span class="text-[10px] text-zinc-500 italic mt-0.5">{{ $curso->descripcion ?: 'Sin descripción adicional' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[9px] font-black uppercase border border-blue-100 dark:border-blue-800/30">
                                    {{ $curso->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-zinc-700 dark:text-white">{{ $curso->num_horas }}<span class="text-[10px] text-zinc-400 font-medium ml-1">hrs</span></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-1 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $curso->id }})" wire:loading.attr="disabled" />
                                    <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-{{ $curso->id }}' })" />
                                </div>

                                <!-- Modal de eliminación -->
                                <div x-data="{ open: false }" 
                                     x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-{{ $curso->id }}') open = true" 
                                     x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-{{ $curso->id }}') open = false" 
                                     x-show="open" x-cloak 
                                     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                    <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                        <div class="space-y-2">
                                            <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Curso?</h3>
                                            <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                                Estás por eliminar el curso <span class="font-bold text-zinc-900 dark:text-white underline">{{ $curso->nombre }}</span>. 
                                                Esta acción es irreversible. (Asegúrate que no tenga materias asignadas).
                                            </p>
                                        </div>

                                        <div class="flex gap-3 justify-end pt-2">
                                            <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                            <flux:button type="button" variant="danger" wire:click="eliminar({{ $curso->id }})" x-on:click="open = false" class="font-black uppercase tracking-widest text-[10px]">Confirmar Baja</flux:button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-zinc-400">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon name="magnifying-glass" class="w-8 h-8 opacity-20" />
                                    <span class="italic text-sm text-zinc-400">No se encontraron cursos registrados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->cursos->hasPages())
            <div class="px-2">
                {{ $this->cursos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-curso') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-curso') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="guardar" wire:key="form-curso-{{ $cursoId ?? 'new' }}" class="space-y-8">
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $cursoId ? 'Editar Curso' : 'Nuevo Curso' }}</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Define las características del programa de estudios.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Nombre Oficial del Curso</flux:label>
                            <flux:input wire:model="nombre" placeholder="Ej: Licenciatura en Seguridad Ciudadana" wire:key="curso-nombre-{{ $cursoId ?? 'new' }}" />
                            <flux:error name="nombre" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Clave / Identificador</flux:label>
                        <flux:input wire:model="identificador" placeholder="LIC-001" class="uppercase" wire:key="curso-id-{{ $cursoId ?? 'new' }}" />
                        <flux:error name="identificador" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tipo de Programa</flux:label>
                        <flux:input wire:model="tipo" placeholder="Ej: Licenciatura, Diplomado, Curso..." wire:key="curso-tipo-{{ $cursoId ?? 'new' }}" />
                        <flux:error name="tipo" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Duración (Horas Totales)</flux:label>
                        <flux:input type="number" wire:model="num_horas" min="1" icon="clock" placeholder="Ej: 120" wire:key="curso-horas-{{ $cursoId ?? 'new' }}" />
                        <flux:error name="num_horas" />
                    </flux:field>

                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Descripción y Objetivo</flux:label>
                            <flux:textarea wire:model="descripcion" rows="3" placeholder="Descripción breve del contenido académico o capacidades a desarrollar..." wire:key="curso-desc-{{ $cursoId ?? 'new' }}" />
                            <flux:error name="descripcion" />
                        </flux:field>
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        {{ $cursoId ? 'Actualizar Curso' : 'Registrar Curso' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
