<?php

use function Livewire\Volt\{state, computed, layout, usesPagination, updated};
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
    'total_horas' => '',
    'dias_clase' => [1, 2, 3, 4, 5],
    'formato_especial' => false,
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
        ->paginate(15);
});

$planteles = computed(fn() => Plantel::orderBy('name')->get());
$cursos = computed(fn() => Curso::orderBy('nombre')->get());

updated(['total_horas' => function ($value) {
    $horasEspeciales = [40, 60, 80, 100, 120];
    if (in_array(intval($value), $horasEspeciales)) {
        $this->formato_especial = true;
    }
}]);

$abrirModalCrear = function () {
    $this->resetErrorBag();
    $this->reset(['grupoId', 'nombre', 'plantel_id', 'curso_id', 'periodo', 'estado', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'hora_fin', 'total_horas', 'formato_especial']);
    $this->estado = 'activo';
    $this->dias_clase = [1, 2, 3, 4, 5];
    $this->formato_especial = false;
    $this->dispatch('modal-show', name: 'modal-grupo');
};

$editar = function (Grupo $grupo) {
    $this->resetErrorBag();
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
        'dias_clase' => is_array($grupo->dias_clase) ? $grupo->dias_clase : json_decode($grupo->dias_clase, true) ?? [1, 2, 3, 4, 5],
        'formato_especial' => $grupo->formato_especial ? true : (intval($grupo->total_horas) <= 40),
    ]);
    
    $this->dispatch('modal-show', name: 'modal-grupo');
};

$guardar = function () {
    $rules = [
        'nombre' => 'required|string|max:255',
        'plantel_id' => 'required|exists:planteles,id',
        'curso_id' => 'required|exists:cursos,id',
        'periodo' => 'required|string|max:20',
        'estado' => 'required|in:activo,concluido,cancelado',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'hora_inicio' => 'required',
        'hora_fin' => 'required',
        'total_horas' => 'required|integer|min:1',
        'dias_clase' => 'required|array|min:1',
        'formato_especial' => 'boolean',
    ];

    $datos = $this->validate($rules);

    if ($this->grupoId) {
        Grupo::find($this->grupoId)->update($datos);
        Flux::toast(heading: 'Grupo actualizado', text: 'Los datos del ciclo académico han sido actualizados.', variant: 'success');
    } else {
        $nuevoGrupo = Grupo::create($datos);
        
        // Crear el registro de expediente inicial
        GrupoExpediente::create([
            'grupo_id' => $nuevoGrupo->id,
            'tipo_documento' => 'expediente_inicial',
            'archivo' => null,
            'usuario_id' => auth()->id(),
        ]);

        Flux::toast(heading: 'Apertura Exitosa', text: 'El grupo fue aperturado y registrado en el directorio.', variant: 'success');
    }

    unset($this->grupos);
    $this->dispatch('modal-hide', name: 'modal-grupo');
};

$eliminar = function ($id) {
    $grupo = Grupo::findOrFail($id);
    if ($grupo->alumnos()->exists()) {
        Flux::toast(heading: 'Acción Denegada', text: 'No se puede eliminar un grupo que contiene matrículas activas.', variant: 'danger');
        return;
    }
    $grupo->delete();
    Flux::toast(heading: 'Grupo Eliminado', text: 'El registro del grupo fue borrado del sistema.', variant: 'success');
};

?>

<div class="p-6">
    <x-slot name="header">Gestión de Grupos y Generaciones</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Directorio de Grupos</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Administra la apertura, estatus y control de generaciones académicas.</p>
            </div>
            
            <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear" size="sm">Aperturar Grupo</flux:button>
        </div>

        <!-- Filtros Dinámicos -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por clave, nombre o generación..." icon="magnifying-glass" />
            </div>
            <div class="w-full md:w-64">
                <flux:select wire:model.live="filtroPlantel" placeholder="Filtrar por Sedes">
                    <flux:select.option value="">Todas las Sedes / Planteles</flux:select.option>
                    @foreach($this->planteles as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full md:w-48">
                <flux:select wire:model.live="filtroEstado" placeholder="Estatus Académico">
                    <flux:select.option value="">Cualquier Estatus</flux:select.option>
                    <flux:select.option value="activo">Activos / En Curso</flux:select.option>
                    <flux:select.option value="concluido">Generaciones Concluidas</flux:select.option>
                    <flux:select.option value="cancelado">Bajas / Cancelados</flux:select.option>
                </flux:select>
            </div>
        </div>

        <!-- Tabla Estándar Receptiva CSS -->
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Grupo / Programa Curricular</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Sede / Ubicación</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Periodo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Vigencia y Horario</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Estatus Operativo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-right">Manejo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->grupos as $grupo)
                        <tr wire:key="grupo-{{ $grupo->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-48 text-wrap leading-tight">
                                    <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-sm">{{ $grupo->nombre }}</span>
                                    <span class="text-[10px] text-zinc-500 italic mt-0.5 max-w-[250px] truncate">{{ optional($grupo->curso)->nombre ?? 'Sin programa asignado' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ optional($grupo->plantel)->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-[10px] font-mono font-bold border border-zinc-200 dark:border-zinc-700">
                                    {{ $grupo->periodo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-[10px] text-zinc-500 flex flex-col items-center leading-tight">
                                    <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $grupo->fecha_inicio?->format('d/m/Y') }} <span class="opacity-50 font-normal mx-1">al</span> {{ $grupo->fecha_fin?->format('d/m/Y') }}</span>
                                    <div class="flex items-center gap-2 mt-1 shrink-0">
                                        <span class="opacity-70">{{ \Carbon\Carbon::parse($grupo->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($grupo->hora_fin)->format('H:i') }}</span>
                                        @if($grupo->formato_especial)
                                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 text-[8px] font-black uppercase rounded border border-blue-200 dark:border-blue-900/30">Formato Especial</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $estatusColor = match($grupo->estado) {
                                        'activo' => 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-900/30',
                                        'concluido' => 'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-900/30',
                                        'cancelado' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30',
                                        default => 'bg-zinc-50 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase border {{ $estatusColor }}">
                                    {{ $grupo->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('grupos.show', $grupo->id) }}" />
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $grupo->id }})" wire:loading.attr="disabled" />
                                    <div x-data="{ openConfirm: false }" class="inline-block relative">
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="openConfirm = true" />
                                        
                                        <!-- Mini Delete Modal/Popover -->
                                        <div x-show="openConfirm" x-cloak class="absolute right-0 bottom-full mb-2 z-10 w-64 p-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl flex flex-col gap-3 items-end">
                                            <p class="text-[11px] text-left font-bold text-zinc-800 dark:text-white leading-tight">¿Eliminar permanentemente el grupo {{ $grupo->nombre }}?</p>
                                            <div class="flex gap-2 w-full justify-between mt-1">
                                                <flux:button variant="ghost" size="sm" x-on:click="openConfirm = false" class="text-[10px]">Cancelar</flux:button>
                                                <flux:button variant="danger" size="sm" wire:click="eliminar({{ $grupo->id }})" x-on:click="openConfirm = false" class="text-[10px]">Confirmar</flux:button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-24 text-center text-zinc-400">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                        <flux:icon name="academic-cap" class="w-8 h-8 opacity-40" />
                                    </div>
                                    <span class="italic text-sm text-zinc-400 font-medium">No se encontraron grupos académicos operando.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->grupos->hasPages())
            <div class="px-2">
                {{ $this->grupos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'modal-grupo') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'modal-grupo') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 lg:p-10 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-4xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-8 my-auto outline-none" x-on:click.away="open = false">
            <form wire:submit="guardar" wire:key="form-grupo-{{ $grupoId ?? 'new' }}" class="space-y-8">
                <div class="space-y-2 border-b border-zinc-100 dark:border-zinc-700 pb-4">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $grupoId ? 'Modificar Parámetros de Grupo' : 'Apertura de Nuevo Grupo' }}</h2>
                    <p class="text-[11px] text-zinc-500 font-bold uppercase tracking-tighter italic">Define identificadores, sedes, programas curriulares y vigencia académica general.</p>
                </div>

                <!-- Pestañas simuladas / Secciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <!-- Sección Izquierda: Identidad -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 text-zinc-400 uppercase font-black text-[10px] tracking-widest mb-2 border-b border-dashed pb-2">
                            <flux:icon name="finger-print" variant="mini"/> Identidad y Ubicación
                        </div>
                        
                        <flux:field>
                            <flux:label>Nombre del Grupo / Generación</flux:label>
                            <flux:input wire:model="nombre" placeholder="Ej: Generación 2024-B Alpha" class="font-bold uppercase" wire:key="g-nom-{{ $grupoId ?? 'new' }}" />
                            <flux:error name="nombre" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Programa Curricular / Curso</flux:label>
                            <flux:select wire:model="curso_id" placeholder="Seleccionar programa base..." searchable wire:key="g-cur-{{ $grupoId ?? 'new' }}">
                                @foreach($this->cursos as $c)
                                    <flux:select.option value="{{ $c->id }}">{{ $c->nombre }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="curso_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Sede Operativa (Plantel)</flux:label>
                            <flux:select wire:model="plantel_id" placeholder="Seleccionar instalaciones..." searchable wire:key="g-pla-{{ $grupoId ?? 'new' }}">
                                @foreach($this->planteles as $p)
                                    <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="plantel_id" />
                        </flux:field>

                        <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-2xl flex items-center justify-between">
                            <div class="space-y-0.5">
                                <flux:label class="font-black text-blue-700 dark:text-blue-400 text-xs text-wrap">FORMATO DE CALIFICACIÓN ESPECIAL</flux:label>
                                <p class="text-[10px] text-zinc-500 font-medium italic">Activar para usar modalidad "Diagnóstica y Final" (Tipo 40 hrs).</p>
                            </div>
                            <flux:switch wire:model.live="formato_especial" color="blue" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Ciclo / Periodo</flux:label>
                                <flux:input wire:model="periodo" placeholder="Ej: 2024-2" wire:key="g-per-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="periodo" />
                            </flux:field>
                            
                            <flux:field>
                                <flux:label>Estatus</flux:label>
                                <flux:select wire:model="estado" wire:key="g-est-{{ $grupoId ?? 'new' }}">
                                    <flux:select.option value="activo">Aperturado / Activo</flux:select.option>
                                    <flux:select.option value="concluido">Generación Graduada</flux:select.option>
                                    <flux:select.option value="cancelado">Suspendido / Cancelado</flux:select.option>
                                </flux:select>
                                <flux:error name="estado" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Sección Derecha: Vigencia y Horarios -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 text-zinc-400 uppercase font-black text-[10px] tracking-widest mb-2 border-b border-dashed pb-2">
                            <flux:icon name="calendar-days" variant="mini"/> Cronograma y Carga
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Fec. Inicio</flux:label>
                                <flux:input type="date" wire:model="fecha_inicio" wire:key="g-fini-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="fecha_inicio" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Fec. Término</flux:label>
                                <flux:input type="date" wire:model="fecha_fin" wire:key="g-ffin-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="fecha_fin" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>Hora Entrada</flux:label>
                                <flux:input type="time" wire:model="hora_inicio" wire:key="g-hin-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="hora_inicio" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Hora Salida</flux:label>
                                <flux:input type="time" wire:model="hora_fin" wire:key="g-hfin-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="hora_fin" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Total</flux:label>
                                <flux:input type="number" wire:model.live="total_horas" min="1" icon="clock" placeholder="Hrs" wire:key="g-thor-{{ $grupoId ?? 'new' }}" />
                                <flux:error name="total_horas" />
                            </flux:field>
                        </div>

                        <div class="pt-2">
                            <flux:label class="mb-3 font-bold">Días Hábiles de Cátedra</flux:label>
                            <div class="grid grid-cols-4 gap-3 p-4 bg-zinc-50 dark:bg-zinc-900/40 rounded-2xl border border-zinc-200 dark:border-zinc-800" wire:key="g-dias-container-{{ $grupoId ?? 'new' }}">
                                @foreach([1=>'Lun', 2=>'Mar', 3=>'Mié', 4=>'Jue', 5=>'Vie', 6=>'Sáb', 7=>'Dom'] as $val => $label)
                                    <label class="flex items-center justify-center p-2 rounded-xl cursor-pointer transition-all border {{ in_array($val, is_array($dias_clase) ? $dias_clase : []) ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800' : 'bg-white border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}">
                                        <input type="checkbox" wire:model="dias_clase" value="{{ $val }}" class="hidden">
                                        <span class="text-[10px] font-black uppercase {{ in_array($val, is_array($dias_clase) ? $dias_clase : []) ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-500' }}">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <flux:error name="dias_clase" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-6 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:button variant="ghost" x-on:click="open = false">Suspender Acción</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        {{ $grupoId ? 'Actualizar Ficha de Grupo' : 'Registrar y Aperturar' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
