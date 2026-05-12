<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Recurso;
use App\Models\MetaCapacitacion;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    // Estados de Recursos
    'search' => '',
    'recursoId' => null,
    'nombre' => '',
    'clave' => '',
    'descripcion' => '',
    'activo' => true,

    // Estados de Metas de Capacitación
    'metaId' => null,
    'metaAnio' => '',
    'metaValor' => '',
]);

// COMPUTED DE RECURSOS
$recursos = computed(function () {
    return Recurso::query()
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('clave', 'like', '%' . $this->search . '%');
        })
        ->orderBy('nombre')
        ->paginate(10);
});

// COMPUTED DE METAS ANUALES
$metas = computed(function () {
    return MetaCapacitacion::query()
        ->orderBy('anio', 'desc')
        ->get();
});

// ACCIONES DE RECURSOS
$create = function () {
    $this->resetErrorBag();
    $this->reset(['recursoId', 'nombre', 'clave', 'descripcion', 'activo']);
    $this->activo = true;
    $this->dispatch('modal-show', name: 'recurso-modal');
};

$edit = function ($id) {
    $this->resetErrorBag();
    $recurso = Recurso::findOrFail($id);
    
    $this->recursoId = $recurso->id;
    $this->nombre = $recurso->nombre;
    $this->clave = $recurso->clave ?? '';
    $this->descripcion = $recurso->descripcion ?? '';
    $this->activo = (bool) $recurso->activo;
    
    $this->dispatch('modal-show', name: 'recurso-modal');
};

$save = function () {
    $rules = [
        'nombre' => 'required|string|max:100|unique:recursos,nombre,' . $this->recursoId,
        'clave' => 'nullable|string|max:50',
        'descripcion' => 'nullable|string|max:500',
        'activo' => 'boolean',
    ];

    $datos = $this->validate($rules);

    if ($this->recursoId) {
        $recurso = Recurso::findOrFail($this->recursoId);
        $recurso->update($datos);
        $msg = 'Recurso actualizado';
    } else {
        Recurso::create($datos);
        $msg = 'Recurso registrado';
    }

    $this->reset(['recursoId', 'nombre', 'clave', 'descripcion', 'activo']);
    unset($this->recursos);
    $this->dispatch('modal-hide', name: 'recurso-modal');
    
    Flux::toast(
        heading: $msg,
        text: 'El catálogo de fuentes de financiamiento ha sido modificado.',
        variant: 'success'
    );
};

$toggleActivo = function ($id) {
    $recurso = Recurso::findOrFail($id);
    $recurso->activo = !$recurso->activo;
    $recurso->save();
    
    unset($this->recursos);
    
    Flux::toast(
        heading: $recurso->activo ? 'Recurso Activado' : 'Recurso Desactivado',
        text: "El fondo '{$recurso->nombre}' cambió su estado de disponibilidad.",
        variant: 'info'
    );
};

$delete = function ($id) {
    $recurso = Recurso::findOrFail($id);
    $cantidadGrupos = $recurso->grupos()->count();
    
    $recurso->delete();
    unset($this->recursos);

    Flux::toast(
        heading: 'Recurso eliminado',
        text: $cantidadGrupos > 0 
            ? "El recurso fue borrado. {$cantidadGrupos} grupo(s) asociados ahora están sin financiamiento definido."
            : "El recurso ha sido removido del catálogo correctamente.",
        variant: 'success'
    );
};

// ACCIONES DE METAS ANUALES
$createMeta = function () {
    $this->resetErrorBag();
    $this->reset(['metaId', 'metaAnio', 'metaValor']);
    $this->metaAnio = (int) date('Y');
    $this->dispatch('modal-show', name: 'meta-modal');
};

$editMeta = function ($id) {
    $this->resetErrorBag();
    $meta = MetaCapacitacion::findOrFail($id);
    
    $this->metaId = $meta->id;
    $this->metaAnio = $meta->anio;
    $this->metaValor = $meta->meta;
    
    $this->dispatch('modal-show', name: 'meta-modal');
};

$saveMeta = function () {
    $rules = [
        'metaAnio' => 'required|integer|min:2020|max:2100|unique:metas_capacitacion,anio,' . $this->metaId,
        'metaValor' => 'required|integer|min:1',
    ];

    $datos = $this->validate($rules, [
        'metaAnio.unique' => 'Ya existe una meta de capacitación registrada para este año.',
        'metaAnio.required' => 'El año es obligatorio.',
        'metaValor.required' => 'La cantidad meta de alumnos es obligatoria.',
    ]);

    if ($this->metaId) {
        $meta = MetaCapacitacion::findOrFail($this->metaId);
        $meta->update([
            'anio' => $this->metaAnio,
            'meta' => $this->metaValor,
        ]);
        $msg = 'Meta actualizada';
    } else {
        MetaCapacitacion::create([
            'anio' => $this->metaAnio,
            'meta' => $this->metaValor,
        ]);
        $msg = 'Meta registrada';
    }

    $this->reset(['metaId', 'metaAnio', 'metaValor']);
    unset($this->metas);
    $this->dispatch('modal-hide', name: 'meta-modal');
    
    Flux::toast(
        heading: $msg,
        text: 'La meta de capacitación anual se ha guardado con éxito.',
        variant: 'success'
    );
};

$deleteMeta = function ($id) {
    $meta = MetaCapacitacion::findOrFail($id);
    $meta->delete();
    
    unset($this->metas);

    Flux::toast(
        heading: 'Meta eliminada',
        text: 'La meta anual de capacitación ha sido eliminada del sistema.',
        variant: 'success'
    );
};

?>

<div class="p-6">
    <x-slot name="header">Configuración de Recursos y Metas</x-slot>

    <!-- Layout Dual de Configuración (Grideado de 3 columnas en LG) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- ================= COLUMNA IZQUIERDA: RECURSOS (2/3) ================= -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Encabezado de Sección -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase flex items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="text-emerald-500" /> Catálogo de Recursos
                    </h1>
                    <p class="text-xs text-zinc-500 font-medium italic">Administra los fondos presupuestales vinculados a la capacitación.</p>
                </div>
                
                <flux:button variant="primary" icon="plus" size="sm" wire:click="create" class="md:self-center">Nuevo Recurso</flux:button>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <div class="w-full relative">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por denominación o clave del recurso..." icon="magnifying-glass" />
                </div>
            </div>

            <!-- Directorio de Recursos (Tabla Responsiva Premium) -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Denominación del Recurso</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Clave Presupuestal</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Descripción / Detalles</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Estado de Uso</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->recursos as $recurso)
                            <tr wire:key="recurso-row-{{ $recurso->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ $recurso->nombre }}</span>
                                        @if ($recurso->grupos()->exists())
                                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 text-[8px] font-black uppercase rounded border border-blue-200 dark:border-blue-900/20">
                                                En Uso ({{ $recurso->grupos()->count() }} grupos)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 text-[10px] font-mono font-bold border border-zinc-200 dark:border-zinc-700">
                                        {{ $recurso->clave ?: 'S/C' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-zinc-500 max-w-sm truncate block leading-relaxed font-medium">
                                        {{ $recurso->descripcion ?: 'Sin descripción detallada registrada.' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" wire:click="toggleActivo({{ $recurso->id }})" class="outline-none">
                                        @if ($recurso->activo)
                                            <span class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-200 dark:border-emerald-900/20">
                                                <span class="w-1.5 h-1.5 bg-emerald-600 dark:bg-emerald-400 rounded-full mr-1.5 animate-pulse"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400 text-[10px] font-bold rounded-full border border-red-200 dark:border-red-900/20">
                                                <span class="w-1.5 h-1.5 bg-red-600 dark:bg-red-400 rounded-full mr-1.5"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-1 justify-end">
                                        <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $recurso->id }})" wire:loading.attr="disabled" />
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-{{ $recurso->id }}' })" />
                                    </div>

                                    <!-- Modal de eliminación -->
                                    <div x-data="{ open: false }" 
                                         x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-{{ $recurso->id }}') open = true" 
                                         x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-{{ $recurso->id }}') open = false" 
                                         x-show="open" x-cloak 
                                         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                        <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                            <div class="space-y-2">
                                                <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Recurso?</h3>
                                                <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                                    Estás por eliminar el recurso <span class="font-bold text-zinc-900 dark:text-white underline">{{ $recurso->nombre }}</span>.
                                                    @if ($recurso->grupos()->exists())
                                                        <br><br>
                                                        <strong class="text-red-600 dark:text-red-400 font-bold uppercase text-[11px] block border border-dashed border-red-200 p-3 rounded-2xl bg-red-50/50 dark:bg-red-950/10">
                                                            ⚠️ ¡Cuidado! Hay {{ $recurso->grupos()->count() }} grupo(s) activos vinculados a este fondo presupuestal. Sus datos de financiamiento quedarán vacíos (No Definido) de forma inmediata.
                                                        </strong>
                                                    @else
                                                        Esta acción es irreversible y removerá la denominación presupuestaria de forma definitiva.
                                                    @endif
                                                </p>
                                            </div>

                                            <div class="flex gap-3 justify-end pt-2">
                                                <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                                <flux:button type="button" variant="danger" wire:click="delete({{ $recurso->id }})" x-on:click="open = false" class="font-black uppercase tracking-widest text-[10px]">Confirmar Baja</flux:button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm font-bold uppercase tracking-tighter text-zinc-400 italic">
                                    No se encontraron fuentes de financiamiento que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->recursos->hasPages())
                <div class="px-2">
                    {{ $this->recursos->links() }}
                </div>
            @endif
        </div>

        <!-- ================= COLUMNA DERECHA: METAS ANUALES (1/3) ================= -->
        <div class="lg:col-span-1 space-y-6 border-t lg:border-t-0 lg:border-l border-zinc-200 dark:border-zinc-700 pt-6 lg:pt-0 lg:pl-8">
            <!-- Encabezado de Sección Metas -->
            <div class="flex justify-between items-center">
                <div class="space-y-1">
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight uppercase flex items-center gap-2">
                        <flux:icon name="chart-bar" variant="mini" class="text-indigo-500" /> Metas Anuales
                    </h2>
                    <p class="text-[10px] text-zinc-500 font-medium italic">Matrícula programada por ciclo fiscal.</p>
                </div>
                
                <flux:button variant="ghost" icon="plus" size="xs" wire:click="createMeta" class="text-indigo-600 dark:text-indigo-400">Agregar Meta</flux:button>
            </div>

            <!-- Listado de Metas de Capacitación -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-zinc-500">Ciclo Fiscal</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Meta Alumnos</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->metas as $meta)
                            <tr wire:key="meta-row-{{ $meta->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">Ciclo {{ $meta->anio }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 text-xs font-black border border-indigo-200 dark:border-indigo-900/20">
                                        {{ number_format($meta->meta) }} elem.
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex gap-1 justify-end">
                                        <flux:button variant="ghost" size="xs" icon="pencil-square" wire:click="editMeta({{ $meta->id }})" />
                                        <flux:button variant="ghost" size="xs" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-meta-{{ $meta->id }}' })" />
                                    </div>

                                    <!-- Modal de eliminación de Meta -->
                                    <div x-data="{ open: false }" 
                                         x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-meta-{{ $meta->id }}') open = true" 
                                         x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-meta-{{ $meta->id }}') open = false" 
                                         x-show="open" x-cloak 
                                         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                        <div class="bg-white dark:bg-zinc-800 w-full max-w-sm rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                            <div class="space-y-2">
                                                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Meta Anual?</h3>
                                                <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                                    Estás por remover la meta programada de <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($meta->meta) }} elementos</span> del ciclo <span class="font-bold text-zinc-900 dark:text-white">{{ $meta->anio }}</span>.
                                                    <br><br>
                                                    Esto afectará la comparativa y gráficos del Dashboard de forma inmediata.
                                                </p>
                                            </div>

                                            <div class="flex gap-3 justify-end pt-2">
                                                <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                                <flux:button type="button" variant="danger" wire:click="deleteMeta({{ $meta->id }})" x-on:click="open = false" class="font-black uppercase tracking-widest text-[10px]">Eliminar Meta</flux:button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-[10px] font-bold uppercase tracking-tighter text-zinc-400 italic">
                                    No hay metas anuales registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================= MODAL FORMULARIO: RECURSO ================= -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'recurso-modal') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'recurso-modal') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-lg rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="save" class="space-y-6" wire:key="recurso-form-{{ $recursoId ?? 'new' }}">
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $recursoId ? 'Editar Parámetros de Recurso' : 'Nuevo Recurso o Fondo' }}</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Define identificadores presupuestarios de origen federal o propio.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Denominación Oficial</flux:label>
                            <flux:input wire:model="nombre" placeholder="Ej: FORTAMUN" class="font-bold uppercase" />
                            <flux:error name="nombre" />
                        </flux:field>
                    </div>
                    <div>
                        <flux:field>
                            <flux:label>Clave</flux:label>
                            <flux:input wire:model="clave" placeholder="Ej: FTMN-2026" class="font-mono font-bold uppercase" />
                            <flux:error name="clave" />
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Descripción / Reglas de Operación</flux:label>
                    <flux:textarea wire:model="descripcion" placeholder="Opcional. Describe brevemente el destino presupuestal o normativo..." rows="3" />
                    <flux:error name="descripcion" />
                </flux:field>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-100 dark:border-zinc-700 rounded-2xl flex items-center justify-between">
                    <div class="space-y-0.5">
                        <flux:label class="font-bold text-xs uppercase">Estatus de Disponibilidad</flux:label>
                        <p class="text-[10px] text-zinc-500 font-medium italic">Permite que el fondo esté disponible para asignación en nuevos grupos.</p>
                    </div>
                    <flux:switch wire:model="activo" color="emerald" />
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        {{ $recursoId ? 'Guardar Cambios' : 'Registrar Fondo' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL FORMULARIO: META ANUAL ================= -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'meta-modal') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'meta-modal') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="saveMeta" class="space-y-6" wire:key="meta-form-{{ $metaId ?? 'new' }}">
                <div class="space-y-2">
                    <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $metaId ? 'Editar Meta Anual' : 'Registrar Meta Anual' }}</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Establece la meta de elementos capacitados para un ciclo fiscal específico.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Año Fiscal</flux:label>
                        <flux:input type="number" wire:model="metaAnio" placeholder="Ej: 2026" class="font-bold text-center" :disabled="!!$metaId" />
                        <flux:error name="metaAnio" />
                    </flux:field>
                    
                    <flux:field>
                        <flux:label>Meta de Elementos</flux:label>
                        <flux:input type="number" wire:model="metaValor" placeholder="Ej: 3000" class="font-bold text-center" />
                        <flux:error name="metaValor" />
                    </flux:field>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-6 font-black uppercase tracking-widest text-[10px]">
                        {{ $metaId ? 'Actualizar Meta' : 'Guardar Meta' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>

</div>
