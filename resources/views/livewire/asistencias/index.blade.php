<?php

use function Livewire\Volt\{state, computed, layout, on};
use App\Models\Asistencia;
use App\Models\Grupo;
use App\Models\Plantel;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

layout('layouts.app');

state([
    'search' => '',
    'filtroPlantel' => '',
    'filtroEstado' => '',
]);

$planteles = computed(fn() => Plantel::orderBy('name')->get());

$asistencias = computed(function () {
    return Asistencia::with(['grupo.curso', 'plantel', 'validador'])
        ->when($this->search, function ($query) {
            $query->whereHas('grupo', function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhereHas('curso', fn($c) => $c->where('nombre', 'like', '%' . $this->search . '%'));
            });
        })
        ->when($this->filtroPlantel, fn($q) => $q->where('plantel_id', $this->filtroPlantel))
        ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
        ->latest()
        ->paginate(15);
});

$validar = function ($id) {
    $asistencia = Asistencia::findOrFail($id);
    
    if (!$asistencia->dentroPeriodoValidacion() && $asistencia->estado === 'pendiente') {
        $asistencia->update(['estado' => 'expirado']);
        Flux::toast(heading: 'Plazo Vencido', text: 'El periodo de gracia de 3 horas ha caducado.', variant: 'danger');
        return;
    }

    $asistencia->update([
        'estado' => 'validado',
        'validado_at' => now(),
        'validado_por' => auth()->id(),
    ]);

    Flux::toast(heading: 'Documento Certificado', text: 'La lista de asistencia física ha sido validada oficialmente.', variant: 'success');
};

$eliminar = function ($id) {
    $asistencia = Asistencia::findOrFail($id);
    if ($asistencia->archivo) {
        Storage::disk('public')->delete($asistencia->archivo);
    }
    $asistencia->delete();
    Flux::toast(heading: 'Expediente Eliminado', text: 'El documento fue expurgado del registro.', variant: 'warning');
};

?>

<div class="p-6">
    <x-slot name="header">Validación de Asistencias</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Auditoría de Asistencias</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Control, validación y archivo de listas escaneadas de las sedes operativas.</p>
            </div>
            
            <div class="flex bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-2 text-[10px] text-blue-700 dark:text-blue-300 font-bold uppercase tracking-widest gap-2 items-center">
                <flux:icon name="clock" variant="mini" class="text-blue-500" /> Max 3 Hrs para Certificar
            </div>
        </div>

        <!-- Filtros Analíticos -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full relative">
                <flux:input wire:model.live.debounce.500ms="search" placeholder="Rastrear por nomenclatura de grupo o programa..." icon="magnifying-glass" />
            </div>
            <div class="w-full md:w-56">
                <flux:select wire:model.live="filtroPlantel" placeholder="Jurisdicción Operativa">
                    <flux:select.option value="">Todas las Sedes / Planteles</flux:select.option>
                    @foreach($this->planteles as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full md:w-48">
                <flux:select wire:model.live="filtroEstado" placeholder="Estatus del Trámite">
                    <flux:select.option value="">Acervo Global</flux:select.option>
                    <flux:select.option value="pendiente">En Espera (Pendiente)</flux:select.option>
                    <flux:select.option value="validado">Expedientes Validados</flux:select.option>
                    <flux:select.option value="expirado">Plazo Caducado / Rechazo</flux:select.option>
                </flux:select>
            </div>
        </div>

        <!-- Tabla Estándar Receptiva CSS -->
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Origen / Ficha de Grupo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Sede Facultada</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Registro Temporal</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Dictamen</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Auditor</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-right">Ejecución</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->asistencias as $asistencia)
                        <tr wire:key="asist-{{ $asistencia->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-xl {{ match($asistencia->estado) { 'validado' => 'bg-green-100 dark:bg-green-900/30 text-green-600', 'pendiente' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600', 'expirado' => 'bg-red-100 dark:bg-red-900/30 text-red-600', default => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500' } }} flex items-center justify-center font-black">
                                        <flux:icon name="document-text" class="size-5" />
                                    </div>
                                    <div class="flex flex-col min-w-48 text-wrap leading-tight">
                                        <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-sm">{{ optional($asistencia->grupo)->nombre ?? 'Grupo Desconocido' }}</span>
                                        <span class="text-[10px] text-zinc-500 italic mt-0.5 truncate max-w-[200px]">{{ optional(optional($asistencia->grupo)->curso)->nombre ?? 'Sin programa asignado' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold uppercase tracking-widest text-[9px] px-2 py-1 rounded border border-zinc-200 dark:border-zinc-700">
                                    {{ optional($asistencia->plantel)->name ?? 'Sede N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center leading-tight">
                                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ \Carbon\Carbon::parse($asistencia->subido_at)->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-zinc-500 font-mono mt-0.5">{{ \Carbon\Carbon::parse($asistencia->subido_at)->format('H:i') }}</span>
                                    
                                    @if($asistencia->estado === 'pendiente')
                                        @if($asistencia->dentroPeriodoValidacion())
                                            <span class="mt-1 px-2 py-0.5 bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 text-[8px] font-black uppercase rounded border border-blue-200 dark:border-blue-900/30">En Tiempo</span>
                                        @else
                                            <span class="mt-1 px-2 py-0.5 bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 text-[8px] font-black uppercase rounded border border-red-200 dark:border-red-900/30">Vencimiento Limit.</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $estatusColor = match($asistencia->estado) {
                                        'validado' => 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-900/30',
                                        'pendiente' => 'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-900/30',
                                        'expirado' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30',
                                        default => 'bg-zinc-50 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400',
                                    };
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase border tracking-widest {{ $estatusColor }}">
                                    {{ $asistencia->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($asistencia->estado === 'validado' && $asistencia->validador)
                                    <div class="flex flex-col items-center text-wrap max-w-32 leading-tight">
                                        <span class="text-[10px] font-bold text-zinc-700 dark:text-zinc-300 uppercase">{{ $asistencia->validador->name }}</span>
                                        <span class="text-[9px] text-zinc-400 font-mono mt-0.5">{{ optional($asistencia->validado_at)->format('H:i - d/M') }}</span>
                                    </div>
                                @elseif($asistencia->estado === 'pendiente')
                                    @if($asistencia->dentroPeriodoValidacion())
                                        <flux:button size="xs" variant="primary" icon="check-badge" wire:click="validar({{ $asistencia->id }})" class="uppercase tracking-widest text-[9px] font-black">Certificar</flux:button>
                                    @else
                                        <!-- Botón para forzar actualización de estado visual -->
                                        <flux:button size="xs" variant="danger" icon="exclamation-triangle" wire:click="validar({{ $asistencia->id }})" class="uppercase tracking-widest text-[9px] font-black">Purgar</flux:button>
                                    @endif
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600 block w-4 h-0.5 bg-current mx-auto rounded-full"></span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ Storage::url($asistencia->archivo) }}" target="_blank" class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Descargar Evidencia Fiel">
                                        <flux:icon name="arrow-down-tray" class="size-4" />
                                    </a>
                                    
                                    <div x-data="{ openConfirm: false }" class="inline-block relative">
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="openConfirm = true" />
                                        
                                        <!-- Mini Delete Modal/Popover -->
                                        <div x-show="openConfirm" x-cloak class="absolute right-0 bottom-full mb-2 z-10 w-64 p-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl flex flex-col gap-3 items-end">
                                            <p class="text-[11px] text-left font-bold text-zinc-800 dark:text-white leading-tight">¿Desechar permanentemente este reporte de asistencia?</p>
                                            <div class="flex gap-2 w-full justify-between mt-1">
                                                <flux:button variant="ghost" size="sm" x-on:click="openConfirm = false" class="text-[10px]">Cancelar</flux:button>
                                                <flux:button variant="danger" size="sm" wire:click="eliminar({{ $asistencia->id }})" x-on:click="openConfirm = false" class="text-[10px]">Eliminar</flux:button>
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
                                    <div class="p-5 bg-zinc-100 dark:bg-zinc-800 rounded-full">
                                        <flux:icon name="document-magnifying-glass" class="w-10 h-10 opacity-40 text-blue-500" />
                                    </div>
                                    <span class="font-bold text-zinc-600 dark:text-zinc-400 text-sm">Bandeja de Auditoría Vacía</span>
                                    <span class="italic text-[11px] text-zinc-400 max-w-sm">No existen reportes físicos o escaneados remitidos por las sedes bajo los criterios actuales de búsqueda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->asistencias->hasPages())
            <div class="px-2">
                {{ $this->asistencias->links() }}
            </div>
        @endif
    </div>
</div>
