<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Grupo;
use App\Models\Plantel;
use App\Models\Curso;
use App\Models\GrupoExpediente;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'filtroPlantel' => '',
    'filtroEstado' => '',
    
    // Para el modal de creación/edición
    'grupoId' => null,
    'nombre' => '',
    'plantel_id' => '',
    'curso_id' => '',
    'periodo' => '',
    'estado' => 'activo',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'hora_inicio' => '09:00',
    'hora_fin' => '14:00',
    'total_horas' => 0,
    'dias_clase' => [1, 2, 3, 4, 5],
]);

$grupos = computed(function () {
    return Grupo::query()
        ->with(['plantel', 'curso'])
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhereHas('curso', fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%'));
        })
        ->when($this->filtroPlantel, fn($query) => $query->where('plantel_id', $this->filtroPlantel))
        ->when($this->filtroEstado, fn($query) => $query->where('estado', $this->filtroEstado))
        ->orderBy('created_at', 'desc')
        ->paginate(10);
});

$planteles = computed(fn() => Plantel::all());
$cursos = computed(fn() => Curso::all());

$abrirModalCrear = function () {
    $this->reset(['grupoId', 'nombre', 'plantel_id', 'curso_id', 'periodo', 'estado', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'hora_fin', 'total_horas', 'dias_clase']);
    $this->dias_clase = [1, 2, 3, 4, 5];
    $this->dispatch('modal-show', name: 'modal-grupo');
};

$editar = function (Grupo $grupo) {
    $this->fill([
        'grupoId' => $grupo->id,
        'nombre' => $grupo->nombre,
        'plantel_id' => $grupo->plantel_id,
        'curso_id' => $grupo->curso_id,
        'periodo' => $grupo->periodo,
        'estado' => $grupo->estado,
        'fecha_inicio' => $grupo->fecha_inicio?->format('Y-m-d'),
        'fecha_fin' => $grupo->fecha_fin?->format('Y-m-d'),
        'hora_inicio' => $grupo->hora_inicio,
        'hora_fin' => $grupo->hora_fin,
        'total_horas' => $grupo->total_horas,
        'dias_clase' => $grupo->dias_clase ?? [1, 2, 3, 4, 5],
    ]);
    
    $this->dispatch('modal-show', name: 'modal-grupo');
};

$guardar = function () {
    $rules = [
        'nombre' => 'required|string|max:255',
        'plantel_id' => 'required|exists:planteles,id',
        'curso_id' => 'required|exists:cursos,id',
        'periodo' => 'required|string|max:20',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'hora_inicio' => 'required',
        'hora_fin' => 'required',
        'total_horas' => 'required|integer|min:1',
    ];

    $this->validate($rules);

    $datos = [
        'nombre' => $this->nombre,
        'plantel_id' => $this->plantel_id,
        'curso_id' => $this->curso_id,
        'periodo' => $this->periodo,
        'estado' => $this->estado,
        'fecha_inicio' => $this->fecha_inicio,
        'fecha_fin' => $this->fecha_fin,
        'hora_inicio' => $this->hora_inicio,
        'hora_fin' => $this->hora_fin,
        'total_horas' => $this->total_horas,
        'dias_clase' => $this->dias_clase,
    ];

    if ($this->grupoId) {
        Grupo::find($this->grupoId)->update($datos);
        Flux::toast(heading: 'Grupo actualizado', variant: 'success');
    } else {
        $nuevoGrupo = Grupo::create($datos);
        
        // Crear el registro de expediente inicial
        GrupoExpediente::create([
            'grupo_id' => $nuevoGrupo->id,
            'tipo_documento' => 'expediente_inicial',
            'archivo' => null,
            'usuario_id' => auth()->id(),
        ]);

        Flux::toast(heading: 'Grupo creado correctamente', variant: 'success');
    }

    $this->dispatch('modal-hide', name: 'modal-grupo');
};

$eliminar = function (Grupo $grupo) {
    if ($grupo->alumnos()->exists()) {
        Flux::toast(heading: 'Error', text: 'No se puede eliminar un grupo con alumnos inscritos.', variant: 'danger');
        return;
    }
    $grupo->delete();
    Flux::toast(heading: 'Grupo eliminado', variant: 'success');
};

?>

<div class="space-y-6">
    <x-slot name="header">Gestión de Grupos</x-slot>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl">Directorio de Grupos</flux:heading>
            <flux:subheading>Administra la apertura y estatus de los grupos académicos.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear">Nuevo Grupo</flux:button>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="md:col-span-2">
            <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre de grupo o curso..." icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filtroPlantel" placeholder="Todos los planteles">
            <flux:select.option value="">Todos los planteles</flux:select.option>
            @foreach($this->planteles as $p)
                <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filtroEstado" placeholder="Cualquier estado">
            <flux:select.option value="">Cualquier estado</flux:select.option>
            <flux:select.option value="activo">Activos</flux:select.option>
            <flux:select.option value="concluido">Concluidos</flux:select.option>
            <flux:select.option value="cancelado">Cancelados</flux:select.option>
        </flux:select>
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Grupo / Curso</flux:table.column>
                <flux:table.column>Plantel</flux:table.column>
                <flux:table.column align="center">Periodo</flux:table.column>
                <flux:table.column align="center">Vigencia</flux:table.column>
                <flux:table.column align="center">Estado</flux:table.column>
                <flux:table.column align="right">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->grupos as $grupo)
                    <flux:table.row :key="$grupo->id">
                        <flux:table.cell>
                            <div class="flex flex-col min-w-48 text-wrap">
                                <span class="font-bold text-zinc-900 dark:text-white">{{ $grupo->nombre }}</span>
                                <span class="text-xs text-zinc-500">{{ $grupo->curso->nombre }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-sm">{{ $grupo->plantel->name }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <span class="text-xs font-mono bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded">{{ $grupo->periodo }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <div class="text-[10px] text-zinc-500 leading-tight">
                                {{ $grupo->fecha_inicio?->format('d/m/Y') }}<br>
                                {{ $grupo->fecha_fin?->format('d/m/Y') }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:badge size="sm" :color="match($grupo->estado) {
                                'activo' => 'green',
                                'concluido' => 'blue',
                                'cancelado' => 'red',
                                default => 'zinc'
                            }" variant="pill">
                                {{ ucfirst($grupo->estado) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="right">
                            <div class="flex justify-end gap-1">
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('grupos.show', $grupo->id) }}" />
                                <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $grupo->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="¿Estás seguro de eliminar este grupo?" wire:click="eliminar({{ $grupo->id }})" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center" class="py-12 text-zinc-400">
                            No se encontraron grupos con los criterios seleccionados.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->grupos->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50">
                {{ $this->grupos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario Grupo -->
    <flux:modal name="modal-grupo" class="max-w-2xl">
        <form wire:submit="guardar" wire:key="form-grupo-{{ $grupoId ?? 'new' }}" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $grupoId ? 'Editar Grupo' : 'Apertura de Nuevo Grupo' }}</flux:heading>
                <flux:subheading>Define los parámetros generales y vigencia del grupo.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label>Nombre del Grupo / Identificador</flux:label>
                    <flux:input wire:model="nombre" placeholder="Ej: Grupo A 2024" />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>Plantel</flux:label>
                    <flux:select wire:model="plantel_id" placeholder="Selecciona plantel...">
                        @foreach($this->planteles as $p)
                            <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plantel_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Curso / Programa</flux:label>
                    <flux:select wire:model="curso_id" placeholder="Selecciona curso...">
                        @foreach($this->cursos as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="curso_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Periodo Escolar</flux:label>
                    <flux:input wire:model="periodo" placeholder="Ej: 2024-1" />
                    <flux:error name="periodo" />
                </flux:field>

                <flux:field>
                    <flux:label>Estado</flux:label>
                    <flux:select wire:model="estado">
                        <flux:select.option value="activo">Activo</flux:select.option>
                        <flux:select.option value="concluido">Concluido</flux:select.option>
                        <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    </flux:select>
                    <flux:error name="estado" />
                </flux:field>

                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Fecha Inicio</flux:label>
                        <flux:input type="date" wire:model="fecha_inicio" />
                        <flux:error name="fecha_inicio" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Fecha Fin</flux:label>
                        <flux:input type="date" wire:model="fecha_fin" />
                        <flux:error name="fecha_fin" />
                    </flux:field>
                </div>

                <div class="md:col-span-2 grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Hora Inicio</flux:label>
                        <flux:input type="time" wire:model="hora_inicio" />
                        <flux:error name="hora_inicio" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Hora Fin</flux:label>
                        <flux:input type="time" wire:model="hora_fin" />
                        <flux:error name="hora_fin" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Total Horas</flux:label>
                        <flux:input type="number" wire:model="total_horas" min="1" />
                        <flux:error name="total_horas" />
                    </flux:field>
                </div>

                <div class="md:col-span-2">
                    <flux:label class="mb-2">Días de Clase</flux:label>
                    <div class="flex flex-wrap gap-4 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        @foreach([1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo'] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="dias_clase" value="{{ $val }}" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="dias_clase" />
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Grupo</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
