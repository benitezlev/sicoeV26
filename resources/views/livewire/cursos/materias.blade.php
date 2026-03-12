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
        Flux::toast(heading: 'Error', text: 'La materia ya está asignada.', variant: 'danger');
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
    Flux::toast(heading: 'Materia asignada correctamente', variant: 'success');
};

$desvincular = function ($materiaId) {
    $curso = Curso::find($this->curso_id);
    $curso->materias()->detach($materiaId);
    
    $this->cargarMateriasEdit();
    Flux::toast(heading: 'Materia removida del curso', variant: 'warning');
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
    Flux::toast(heading: 'Cambios guardados correctamente', variant: 'success');
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

<div class="space-y-6">
    <x-slot name="header">Materia-Curso Assignment</x-slot>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="w-full md:w-1/2 space-y-2">
            <flux:heading size="lg">Seleccionar Curso</flux:heading>
            <flux:select wire:model.live="curso_id" wire:change="seleccionarCurso($event.target.value)" placeholder="Elige un curso para ver su tira académica...">
                <flux:select.option value="">-- Elige un curso --</flux:select.option>
                @foreach ($this->cursos as $c)
                    <flux:select.option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->identificador }})</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        @if ($this->curso_id)
            <div class="flex gap-2 w-full md:w-auto">
                <flux:button icon="plus" variant="primary" wire:click="abrirModalAgregar">Asignar Materia</flux:button>
                <flux:dropdown>
                    <flux:button icon="arrow-down-tray" variant="ghost">Exportar</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="document-text" wire:click="exportarPDF">PDF</flux:menu.item>
                        <flux:menu.item icon="table-cells" wire:click="exportarExcel">Excel</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        @endif
    </div>

    @if ($this->curso_id && $this->cursoSeleccionado)
        <div class="space-y-4">
            <div class="flex justify-between items-end px-2">
                <div>
                    <flux:heading size="xl">{{ $this->cursoSeleccionado->nombre }}</flux:heading>
                    <flux:subheading>Tira Académica / Carga de Materias</flux:subheading>
                </div>
                <flux:button variant="ghost" icon="check" wire:click="guardarCambios" color="green">Guardar Cambios de Orden/Créditos</flux:button>
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column align="center">Semestre</flux:table.column>
                        <flux:table.column align="center">Orden</flux:table.column>
                        <flux:table.column>Materia</flux:table.column>
                        <flux:table.column align="center">Horas</flux:table.column>
                        <flux:table.column align="center">Créditos</flux:table.column>
                        <flux:table.column align="center">Obligatoria</flux:table.column>
                        <flux:table.column align="center">Desvincular</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->cursoSeleccionado->materias as $m)
                            <flux:table.row :key="$m->id">
                                <flux:table.cell align="center">
                                    <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.semestre" class="w-16 mx-auto" size="sm" />
                                </flux:table.cell>
                                <flux:table.cell align="center">
                                    <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.orden" class="w-16 mx-auto" size="sm" />
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="min-w-48 text-wrap">
                                        <span class="font-bold text-zinc-800 dark:text-white leading-tight block">{{ $m->nombre }}</span>
                                        <div class="text-[10px] text-zinc-400 font-mono mt-0.5">{{ $m->clave }}</div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell align="center">
                                    <flux:input type="number" wire:model="materias_edit.{{ $m->id }}.num_horas" class="w-16 mx-auto" size="sm" />
                                </flux:table.cell>
                                <flux:table.cell align="center">
                                    <flux:input type="number" step="0.5" wire:model="materias_edit.{{ $m->id }}.creditos" class="w-16 mx-auto" size="sm" />
                                </flux:table.cell>
                                <flux:table.cell align="center">
                                    <flux:checkbox wire:model="materias_edit.{{ $m->id }}.obligatoria" />
                                </flux:table.cell>
                                <flux:table.cell align="center">
                                    <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="¿Deseas desvincular esta materia del curso?" wire:click="desvincular({{ $m->id }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7" align="center" class="py-12 text-zinc-400 italic">
                                    Este curso aún no tiene materias asignadas.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    @elseif (!$this->curso_id)
        <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
            <flux:icon name="book-open" class="size-12 text-zinc-300 mb-4" />
            <span class="text-zinc-500 font-medium text-lg">Selecciona un curso para gestionar su tira académica</span>
        </div>
    @endif

    <!-- Modal Agregar Materia -->
    <flux:modal name="modal-agregar-materia" class="max-w-md">
        <form wire:submit="agregarMateria" class="space-y-6">
            <div>
                <flux:heading size="lg">Asignar Materia al Curso</flux:heading>
                <flux:subheading>Elige una materia del catálogo global.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Materia</flux:label>
                <flux:select wire:model="materia_id" placeholder="Selecciona materia...">
                    @foreach ($this->materiasDisponibles as $md)
                        <flux:select.option value="{{ $md->id }}">{{ $md->nombre }} ({{ $md->clave }})</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="materia_id" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Semestre</flux:label>
                    <flux:input type="number" wire:model="semestre" min="1" />
                </flux:field>
                <flux:field>
                    <flux:label>Orden en Tira</flux:label>
                    <flux:input type="number" wire:model="orden" min="1" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Créditos</flux:label>
                <flux:input type="number" step="0.5" wire:model="creditos" />
            </flux:field>

            <flux:checkbox wire:model="obligatoria" label="Esta materia es obligatoria para el egreso" />

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Asignar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
