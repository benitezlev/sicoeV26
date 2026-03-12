<?php

use function Livewire\Volt\{state, computed, layout, on};
use App\Models\Asistencia;
use App\Models\Grupo;
use App\Models\Plantel;
use Flux\Flux;

layout('layouts.app');

state([
    'search' => '',
    'filtroPlantel' => '',
    'filtroEstado' => '',
]);

$planteles = computed(fn() => Plantel::all());

$asistencias = computed(function () {
    return Asistencia::with(['grupo.curso', 'plantel', 'validador'])
        ->when($this->search, function ($query) {
            $query->whereHas('grupo', function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filtroPlantel, fn($q) => $q->where('plantel_id', $this->filtroPlantel))
        ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
        ->latest()
        ->paginate(10);
});

$validar = function ($id) {
    $asistencia = Asistencia::findOrFail($id);
    
    if (!$asistencia->dentroPeriodoValidacion() && $asistencia->estado === 'pendiente') {
        $asistencia->update(['estado' => 'expirado']);
        Flux::toast(heading: 'Error', text: 'El periodo de 3 horas ha expirado.', variant: 'danger');
        return;
    }

    $asistencia->update([
        'estado' => 'validado',
        'validado_at' => now(),
        'validado_por' => auth()->id(),
    ]);

    Flux::toast(heading: 'Asistencia validada', text: 'La lista ha sido marcada como oficial.', variant: 'success');
};

$eliminar = function ($id) {
    $asistencia = Asistencia::findOrFail($id);
    Storage::disk('public')->delete($asistencia->archivo);
    $asistencia->delete();
    Flux::toast(heading: 'Registro eliminado', variant: 'warning');
};

?>

<div class="space-y-6 px-4 md:px-8 pb-10">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Gestión de Asistencias</flux:heading>
            <flux:subheading>Validación de listas escaneadas y control de asistencia física.</flux:subheading>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-col md:flex-row gap-4 bg-white dark:bg-zinc-800 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por grupo..." />
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="filtroPlantel" placeholder="Todos los planteles" class="w-48">
                <flux:select.option value="">Todos los planteles</flux:select.option>
                @foreach($this->planteles as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filtroEstado" placeholder="Estado" class="w-40">
                <flux:select.option value="">Todos los estados</flux:select.option>
                <flux:select.option value="pendiente">Pendiente</flux:select.option>
                <flux:select.option value="validado">Validado</flux:select.option>
                <flux:select.option value="expirado">Expirado</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tabla de Asistencias -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="pl-6 min-w-[250px]">Grupo / Curso</flux:table.column>
                <flux:table.column class="min-w-[120px]">Plantel</flux:table.column>
                <flux:table.column class="min-w-[150px]">Fecha Subida</flux:table.column>
                <flux:table.column class="min-w-[100px]">Estado</flux:table.column>
                <flux:table.column class="min-w-[150px]">Validación</flux:table.column>
                <flux:table.column class="pr-6" align="right"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->asistencias as $asistencia)
                    <flux:table.row :key="$asistencia->id">
                        <flux:table.cell class="pl-6 py-4 whitespace-normal">
                            <div class="flex flex-col">
                                <span class="font-bold text-zinc-900 dark:text-white uppercase">{{ $asistencia->grupo->nombre }}</span>
                                <span class="text-[10px] text-zinc-500 leading-tight">{{ $asistencia->grupo->curso->nombre }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell class="py-4">
                            <flux:badge size="sm" variant="outline">{{ $asistencia->plantel->name }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="py-4">
                            <div class="flex flex-col">
                                <span class="text-xs">{{ \Carbon\Carbon::parse($asistencia->subido_at)->format('d/m/Y H:i') }}</span>
                                @if($asistencia->estado === 'pendiente')
                                    <span class="text-[9px] font-medium {{ $asistencia->dentroPeriodoValidacion() ? 'text-blue-500' : 'text-red-500 uppercase' }}">
                                        {{ $asistencia->dentroPeriodoValidacion() ? 'Dentro del lapso de 3h' : 'Expiró el periodo' }}
                                    </span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="py-4">
                            <flux:badge :color="match($asistencia->estado) {
                                'validado' => 'green',
                                'pendiente' => 'blue',
                                'expirado' => 'red',
                                default => 'zinc'
                            }" size="sm">
                                {{ strtoupper($asistencia->estado) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="py-4 whitespace-normal">
                            @if($asistencia->estado === 'validado')
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold uppercase">{{ $asistencia->validador->name }}</span>
                                    <span class="text-[10px] text-zinc-500">{{ $asistencia->validado_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @elseif($asistencia->estado === 'pendiente' && $asistencia->dentroPeriodoValidacion())
                                <flux:button size="xs" variant="primary" icon="check" wire:click="validar({{ $asistencia->id }})">Válidar ahora</flux:button>
                            @else
                                <span class="text-xs text-zinc-400 italic">No disponible</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="pr-6 py-4" align="right">
                            <div class="flex justify-end gap-2">
                                <flux:button icon="arrow-down-tray" variant="ghost" size="sm" :href="Storage::url($asistencia->archivo)" target="_blank" />
                                <flux:button icon="trash" variant="ghost" color="red" size="sm" wire:confirm="¿Seguro que deseas eliminar este registro?" wire:click="eliminar({{ $asistencia->id }})" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center" class="py-12 text-zinc-400 italic">No hay registros de asistencias cargados.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $this->asistencias->links() }}
        </div>
    </div>
</div>
