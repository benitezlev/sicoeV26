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
            <flux:button href="{{ route('expedientes.import-zip') }}" variant="primary" icon="folder" size="sm">Carga Masiva ZIP</flux:button>
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
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-500 min-w-[250px]">Alumno</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-500">Folio / Apertura</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-500 text-center">Documentos</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-500 text-center">Estatus</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->expedientes as $expediente)
                            <tr wire:key="{{ $expediente->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                            @if($expediente->user->profile_photo_url)
                                                <img src="{{ $expediente->user->profile_photo_url }}" alt="{{ $expediente->user->nombre }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xs font-bold text-zinc-500">{{ substr($expediente->user->nombre, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-800 dark:text-white leading-tight">
                                                {{ $expediente->user->nombre }} {{ $expediente->user->paterno }}
                                            </span>
                                            <span class="text-xs text-zinc-400 font-mono">{{ $expediente->user->curp }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $expediente->folio }}</span>
                                        <span class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($expediente->fecha_apertura)->format('d/m/Y') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <div class="flex justify-center">
                                        @php
                                            $validados = $expediente->documentos->where('estatus', 'validado')->count();
                                            $total = $expediente->documentos->count();
                                        @endphp
                                        <flux:badge size="sm" :color="$validados == $total && $total > 0 ? 'green' : 'zinc'" variant="pill">
                                            {{ $validados }} / {{ $total }}
                                        </flux:badge>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <flux:badge size="sm" :color="match($expediente->estatus) {
                                        'completo' => 'green',
                                        'observado' => 'red',
                                        default => 'amber'
                                    }" variant="inset">
                                        {{ ucfirst($expediente->estatus) }}
                                    </flux:badge>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('expedientes.show', $expediente->id) }}" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-zinc-400">
                                    No se encontraron expedientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->expedientes->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->expedientes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
