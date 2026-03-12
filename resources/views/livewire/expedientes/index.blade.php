<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Expediente;
use App\Models\User;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'estatusFilter' => '',
]);

$expedientes = computed(function () {
    return Expediente::query()
        ->with(['user', 'documentos'])
        ->whereHas('user', function($q) {
            $q->where('nombre', 'like', '%' . $this->search . '%')
              ->orWhere('paterno', 'like', '%' . $this->search . '%')
              ->orWhere('curp', 'like', '%' . $this->search . '%');
        })
        ->when($this->estatusFilter, fn($q) => $q->where('estatus', $this->estatusFilter))
        ->latest('fecha_apertura')
        ->paginate(10);
});

?>

<div>
    <x-slot name="header">Panel de Expedientes</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading size="xl">Expedientes de Alumnos</flux:heading>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 items-end">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o CURP..." icon="magnifying-glass" class="max-w-md w-full" />
            
            <flux:select wire:model.live="estatusFilter" placeholder="Filtrar por estatus" class="max-w-xs">
                <flux:select.option value="">Todos los estados</flux:select.option>
                <flux:select.option value="completo">Completo</flux:select.option>
                <flux:select.option value="incompleto">Incompleto</flux:select.option>
                <flux:select.option value="observado">Observado</flux:select.option>
            </flux:select>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="min-w-[250px]">Alumno</flux:table.column>
                    <flux:table.column>Folio / Apertura</flux:table.column>
                    <flux:table.column align="center">Documentos</flux:table.column>
                    <flux:table.column align="center">Estatus</flux:table.column>
                    <flux:table.column align="center">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->expedientes as $expediente)
                        <flux:table.row :key="$expediente->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar src="{{ $expediente->user->profile_photo_url }}" :name="$expediente->user->nombre" size="sm" />
                                    <div class="flex flex-col">
                                        <span class="font-medium text-zinc-800 dark:text-white leading-tight">
                                            {{ $expediente->user->nombre }} {{ $expediente->user->paterno }}
                                        </span>
                                        <span class="text-xs text-zinc-400 font-mono">{{ $expediente->user->curp }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $expediente->folio }}</span>
                                    <span class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($expediente->fecha_apertura)->format('d/m/Y') }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                <div class="flex justify-center -space-x-2">
                                    @php
                                        $validados = $expediente->documentos->where('estatus', 'validado')->count();
                                        $total = $expediente->documentos->count();
                                    @endphp
                                    <flux:badge size="sm" :color="$validados == $total && $total > 0 ? 'green' : 'zinc'" variant="pill">
                                        {{ $validados }} / {{ $total }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                <flux:badge size="sm" :color="match($expediente->estatus) {
                                    'completo' => 'green',
                                    'observado' => 'red',
                                    default => 'amber'
                                }" variant="inset">
                                    {{ ucfirst($expediente->estatus) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('expedientes.show', $expediente->id) }}" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                                No se encontraron expedientes.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if($this->expedientes->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->expedientes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
