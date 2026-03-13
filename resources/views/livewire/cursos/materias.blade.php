<?php

use function Livewire\Volt\{state, computed, layout, mount};
use App\Models\Curso;
use App\Models\Materia;
use Flux\Flux;

layout('layouts.app');

state([
    'curso_id' => null,
    'materia_id' => null,
    'orden' => null,
    'semestre' => null,
    'creditos' => null,
    'obligatoria' => true,
    'materias_edit' => [], // Para edición inline
]);

mount(function() {
    $this->curso_id = request()->query('curso_id');
    if ($this->curso_id) {
        $this->cargarMateriasEdit();
    }
});

$cursoSeleccionado = computed(function () {
    if (!$this->curso_id) return null;
    return Curso::with(['materias' => function($q) {
        $q->orderBy('pivot_semestre')->orderBy('pivot_orden');
    }])->find($this->curso_id);
});

$cursos = computed(fn() => Curso::orderBy('nombre')->get());

$materiasDisponibles = computed(function () {
    if (!$this->curso_id) return [];
    $idsAsignadas = $this->cursoSeleccionado->materias->pluck('id')->toArray();
    return Materia::activas()->whereNotIn('id', $idsAsignadas)->orderBy('nombre')->get();
});

$cargarMateriasEdit = function() {
    $curso = Curso::with('materias')->find($this->curso_id);
    if ($curso) {
        $this->materias_edit = [];
        foreach ($curso->materias as $m) {
            $this->materias_edit[$m->id] = [
                'orden' => $m->pivot->orden,
                'semestre' => $m->pivot->semestre,
                'creditos' => $m->pivot->creditos,
                'obligatoria' => (bool) $m->pivot->obligatoria,
                'num_horas' => $m->num_horas,
            ];
        }
    }
};

$seleccionarCurso = function ($id) {
    if ($id) {
        $this->curso_id = $id;
        $this->cargarMateriasEdit();
    } else {
        $this->curso_id = null;
        $this->materias_edit = [];
    }
};

$abrirModalAgregar = function () {
    if (!$this->curso_id) return;
    $this->resetErrorBag();
    $this->reset(['materia_id', 'orden', 'semestre', 'creditos', 'obligatoria']);
    $this->dispatch('modal-show', name: 'modal-agregar-materia');
};

$agregarMateria = function () {
    $this->validate([
        'materia_id' => 'required|exists:materias,id',
        'semestre' => 'nullable|integer',
        'orden' => 'nullable|integer',
        'creditos' => 'nullable|numeric',
    ]);

    $curso = Curso::find($this->curso_id);
    
    if ($curso->materias()->where('materia_id', $this->materia_id)->exists()) {
        Flux::toast(heading: 'Error', text: 'La materia ya está asignada al curso.', variant: 'danger');
        return;
    }

    $curso->materias()->attach($this->materia_id, [
        'orden' => $this->orden,
        'semestre' => $this->semestre,
        'creditos' => $this->creditos,
        'obligatoria' => $this->obligatoria,
    ]);

    $this->cargarMateriasEdit();
    $this->dispatch('modal-hide', name: 'modal-agregar-materia');
    Flux::toast(heading: 'Materia asignada', text: 'Estructura curricular actualizada.', variant: 'success');
};

$desvincular = function ($materiaId) {
    $curso = Curso::find($this->curso_id);
    $curso->materias()->detach($materiaId);
    
    $this->cargarMateriasEdit();
    Flux::toast(heading: 'Materia removida', text: 'Se quitó de la tira académica.', variant: 'warning');
};

$guardarCambios = function () {
    $curso = Curso::find($this->curso_id);

    foreach ($this->materias_edit as $materiaId => $datos) {
        $curso->materias()->updateExistingPivot($materiaId, [
            'orden' => $datos['orden'],
            'semestre' => $datos['semestre'],
            'creditos' => $datos['creditos'],
            'obligatoria' => $datos['obligatoria'],
        ]);

        // También actualizamos las horas en la tabla materias si se modificaron
        Materia::where('id', $materiaId)->update(['num_horas' => $datos['num_horas']]);
    }

    $this->cargarMateriasEdit();
    Flux::toast(heading: 'Cambios guardados', text: 'La configuración de las materias fue actualizada exitosamente.', variant: 'success');
};

$exportarPDF = function () {
    if (!$this->curso_id) return;
    return redirect()->route('panel.materias.export.pdf', $this->curso_id);
};

$exportarExcel = function () {
    if (!$this->curso_id) return;
    return redirect()->route('panel.materias.export.excel', $this->curso_id);
};

?>

<div class="p-6">
    <x-slot name="header">Malla Curricular</x-slot>

    <div class="space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="w-full md:w-1/2 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-500/10 rounded-xl border border-blue-500/20">
                        <flux:icon name="queue-list" variant="mini" class="text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Seleccionar Curso</h2>
                </div>
                <flux:select wire:model.live="curso_id" wire:change="seleccionarCurso($event.target.value)">
                    <flux:select.option value="">-- Elige un Programa Educativo --</flux:select.option>
                    @foreach ($this->cursos as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->identificador }})</flux:select.option>
                    @endforeach
                </flux:select>
                <p class="text-[10px] text-zinc-400 italic">Selecciona para cargar, añadir o modificar materias de un curso.</p>
            </div>

            @if ($this->curso_id)
                <div class="flex flex-wrap gap-2 w-full md:w-auto mt-4 md:mt-0">
                    <flux:button icon="plus" variant="primary" wire:click="abrirModalAgregar" size="sm" class="font-black uppercase tracking-wider text-[10px]">Asignar Materia</flux:button>
                    <flux:dropdown>
                        <flux:button icon="arrow-down-tray" variant="ghost" size="sm" class="font-bold tracking-wide">Exportar</flux:button>
                        <flux:menu>
                            <flux:menu.item icon="document-text" wire:click="exportarPDF">Exportar a PDF</flux:menu.item>
                            <flux:menu.item icon="table-cells" wire:click="exportarExcel">Exportar a Excel</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @endif
        </div>

        @if ($this->curso_id && $this->cursoSeleccionado)
            <div class="space-y-6">
                <div class="flex justify-between items-end px-2">
                    <div class="space-y-1">
                        <h3 class="text-2xl font-black text-zinc-900 dark:text-white">{{ $this->cursoSeleccionado->nombre }}</h3>
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest">Tira Académica / Carga de Materias</p>
                    </div>
                    <flux:button variant="ghost" icon="check-circle" wire:click="guardarCambios" color="green" class="font-black uppercase tracking-tight">Guardar Cambios Rápidos</flux:button>
                </div>

                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Semestre</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Orden</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Materia</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Horas</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Créditos</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Obligatoria</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($this->cursoSeleccionado->materias as $m)
                                <tr wire:key="mat-cur-{{ $m->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.semestre" class="w-20 mx-auto text-center font-mono" size="sm" />
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.orden" class="w-20 mx-auto text-center font-mono" size="sm" />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="min-w-48 text-wrap">
                                            <span class="font-black text-zinc-800 dark:text-white uppercase leading-tight block text-[11px]">{{ $m->nombre }}</span>
                                            <div class="text-[10px] text-zinc-400 font-mono mt-1 opacity-80">{{ $m->clave }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.num_horas" class="w-24 mx-auto text-center font-bold" size="sm" />
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:input type="number" step="0.5" wire:model="materias_edit.{{ $m->id }}.creditos" class="w-20 mx-auto text-center" size="sm" />
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <flux:checkbox wire:model="materias_edit.{{ $m->id }}.obligatoria" class="mx-auto" />
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div x-data="{ openConfirm: false }" class="inline-block relative">
                                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red" x-on:click="openConfirm = true" />
                                            
                                            <div x-show="openConfirm" x-cloak class="absolute right-0 z-10 w-64 p-4 mt-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl flex flex-col gap-3 items-end">
                                                <p class="text-xs text-left font-bold text-zinc-800 dark:text-white leading-tight">¿Quitar materia del curso?</p>
                                                <div class="flex gap-2">
                                                    <flux:button variant="ghost" size="sm" x-on:click="openConfirm = false">Cancelar</flux:button>
                                                    <flux:button variant="danger" size="sm" wire:click="desvincular({{ $m->id }})" x-on:click="openConfirm = false">Quitar</flux:button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-20 text-center text-zinc-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <flux:icon name="queue-list" class="w-8 h-8 opacity-20" />
                                            <span class="italic text-sm text-zinc-400 font-medium">Este curso aún no tiene materias integradas en su tira académica.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif (!$this->curso_id)
            <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/20 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 opacity-60">
                <flux:icon name="book-open" class="size-16 text-zinc-300 mb-4" />
                <span class="text-zinc-500 font-black uppercase tracking-wider text-sm">Panel en espera</span>
                <span class="text-xs text-zinc-400 mt-1">Selecciona un curso arriba para desglosar sus materias asignadas.</span>
            </div>
        @endif

        <!-- Modal Agregar Materia -->
        <div x-data="{ open: false }" 
             x-on:modal-show.window="if ($event.detail.name === 'modal-agregar-materia') open = true" 
             x-on:modal-hide.window="if ($event.detail.name === 'modal-agregar-materia') open = false" 
             x-show="open" x-cloak 
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-800 w-full max-w-xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                <form wire:submit="agregarMateria" class="space-y-6">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Integrar Materia a Currícula</h2>
                        <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Selecciona desde el catálogo global activo.</p>
                    </div>

                    <flux:field>
                        <flux:label>Materia u Asignatura</flux:label>
                        <flux:select wire:model="materia_id" placeholder="Selecciona materia..." searchable>
                            @foreach ($this->materiasDisponibles as $md)
                                <flux:select.option value="{{ $md->id }}">{{ $md->nombre }} ({{ $md->clave }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="materia_id" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <flux:field>
                            <flux:label>Semestre / Módulo</flux:label>
                            <flux:input type="number" wire:model="semestre" min="1" placeholder="Ej: 1" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Orden Visual de Tira</flux:label>
                            <flux:input type="number" wire:model="orden" min="1" placeholder="Ej: 1, 2, 3..." />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Valor Curricular (Créditos)</flux:label>
                        <flux:input type="number" step="0.5" wire:model="creditos" icon="academic-cap" placeholder="Opcional" />
                    </flux:field>

                    <flux:checkbox wire:model="obligatoria" label="Requiere aprobación obligatoria para el egreso" />

                    <div class="flex gap-3 justify-end pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        <flux:button variant="ghost" x-on:click="open = false">Rechazar</flux:button>
                        <flux:button type="submit" variant="primary" class="font-black uppercase tracking-widest text-[10px]">Agrupar Materia</flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
