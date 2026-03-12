<?php

use function Livewire\Volt\{state, computed, layout};
use Illuminate\Support\Facades\Http;
use Flux\Flux;

layout('layouts.app');

state([
    'page' => 1,
    'search' => '',
    'selectedDocente' => null,
]);

$docentes = computed(function () {
    $response = Http::withToken(config('services.sad.token'))
        ->get(config('services.sad.url').'/docentes', [
            'page' => $this->page,
            'search' => $this->search,
            'per_page' => 15,
        ]);

    return $response->json();
});

$gotoPage = function ($page) {
    if ($page > 0) {
        $this->page = $page;
    }
};

$verDetalle = function ($id) {
    $response = Http::withToken(config('services.sad.token'))
        ->get(config('services.sad.url') . '/docentes/' . $id);

    if ($response->successful()) {
        $res = $response->json();
        $this->selectedDocente = $res['data'] ?? $res;
        $this->dispatch('modal-show', name: 'modal-detalle');
    } else {
        Flux::toast(heading: 'Error', text: 'No se pudo obtener el detalle del docente.', variant: 'danger');
    }
};

$sincronizar = function ($id) {
    Flux::toast(
        heading: 'Sincronización',
        text: "Docente ID {$id} sincronizado exitosamente (Simulado).",
        variant: 'success'
    );
};

?>

<div class="space-y-6">
    <x-slot name="header">Directorio de Docentes</x-slot>

    <div class="flex justify-between items-center">
        <flux:heading size="xl">Directorio Institucional (SAD)</flux:heading>
        <flux:button icon="arrow-path" variant="ghost" wire:click="$refresh">Refrescar</flux:button>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-4 items-center">
        <flux:input wire:model.live.debounce.500ms="search" placeholder="Buscar por nombre, CURP o email..." icon="magnifying-glass" class="max-w-md w-full" />
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <div wire:loading.class="opacity-50" class="transition-opacity">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Docente / Contacto</flux:table.column>
                    <flux:table.column>CURP</flux:table.column>
                    <flux:table.column>Plantel / Dependencia</flux:table.column>
                    <flux:table.column>Cargo</flux:table.column>
                    <flux:table.column align="center">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->docentes['data'] ?? [] as $docente)
                        <flux:table.row :key="$docente['id']">
                            <flux:table.cell>
                                <div class="flex items-center gap-3 min-w-64 text-wrap">
                                    <flux:avatar :name="$docente['name']" size="sm" color="zinc" />
                                    <div class="flex flex-col">
                                        <span class="font-bold text-zinc-800 dark:text-white leading-tight">
                                            {{ $docente['name'] }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400 mt-0.5">{{ $docente['email'] }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="text-xs font-mono text-zinc-500">{{ $docente['curp'] }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col min-w-48 text-wrap">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $docente['plantel'] }}</span>
                                    <span class="text-[10px] text-zinc-400">{{ $docente['adscrip'] ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc" variant="inset">{{ $docente['cargo'] }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                <div class="flex justify-center gap-1">
                                    <flux:button variant="ghost" size="sm" icon="eye" wire:click="verDetalle({{ $docente['id'] }})" />
                                    <flux:button variant="ghost" size="sm" icon="arrow-down-tray" wire:click="sincronizar({{ $docente['id'] }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                                @if(isset($this->docentes['data']))
                                    No se encontraron registros.
                                @else
                                    Error de conexión SAD.
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if (isset($this->docentes['meta']['links']) && count($this->docentes['meta']['links']) > 3)
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 flex flex-col md:flex-row justify-between items-center bg-zinc-50/50 dark:bg-white/5 gap-4">
                <div class="text-xs text-zinc-500">
                    Página <strong>{{ $this->docentes['meta']['current_page'][0] ?? $this->page }}</strong> de <strong>{{ $this->docentes['meta']['last_page'][0] ?? '?' }}</strong>
                </div>
                
                <div class="flex flex-wrap gap-1 justify-center">
                    @foreach ($this->docentes['meta']['links'] as $link)
                        @if ($link['url'])
                            @php
                                $queryParams = parse_url($link['url'], PHP_URL_QUERY);
                                parse_str($queryParams, $query);
                                $p = $query['page'] ?? 1;
                                $label = $link['label'];
                                $isNext = str_contains($label, 'Next') || str_contains($label, '&raquo;');
                                $isPrev = str_contains($label, 'Previous') || str_contains($label, '&laquo;');
                            @endphp
                            <flux:button 
                                size="xs" 
                                :variant="$link['active'] ? 'primary' : 'ghost'"
                                wire:click="gotoPage({{ $p }})"
                                :icon="$isPrev ? 'chevron-left' : ($isNext ? 'chevron-right' : null)"
                            >
                                @if(!$isPrev && !$isNext) {!! $label !!} @endif
                            </flux:button>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Detalle Docente -->
    <flux:modal name="modal-detalle" class="max-w-2xl">
        @if ($selectedDocente)
            <div class="space-y-6">
                <div class="flex items-center gap-4 border-b border-zinc-100 dark:border-zinc-700 pb-6">
                    <flux:avatar :name="$selectedDocente['name']" size="xl" color="zinc" />
                    <div>
                        <flux:heading size="xl">{{ $selectedDocente['name'] }}</flux:heading>
                        <flux:subheading>{{ $selectedDocente['email'] }}</flux:subheading>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div class="space-y-1">
                        <span class="text-xs text-zinc-400 uppercase font-bold tracking-tighter text-zinc-500">CURP</span>
                        <div class="text-sm font-mono p-2 bg-zinc-50 dark:bg-zinc-900 rounded border border-zinc-100 dark:border-zinc-800">
                            {{ $selectedDocente['curp'] }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs text-zinc-400 uppercase font-bold tracking-tighter text-zinc-500">Plantel</span>
                        <div class="text-sm font-medium p-2 bg-zinc-50 dark:bg-zinc-900 rounded border border-zinc-100 dark:border-zinc-800">
                            {{ $selectedDocente['plantel'] }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs text-zinc-400 uppercase font-bold tracking-tighter text-zinc-500">Cargo</span>
                        <div class="text-sm p-2 bg-zinc-50 dark:bg-zinc-900 rounded border border-zinc-100 dark:border-zinc-800">
                            {{ $selectedDocente['cargo'] }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs text-zinc-400 uppercase font-bold tracking-tighter text-zinc-500">Puesto</span>
                        <div class="text-sm p-2 bg-zinc-50 dark:bg-zinc-900 rounded border border-zinc-100 dark:border-zinc-800">
                            {{ $selectedDocente['puesto'] }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cerrar</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" icon="arrow-down-tray" wire:click="sincronizar({{ $selectedDocente['id'] }})">Importar</flux:button>
                </div>
            </div>
        @else
            <div class="p-12 flex justify-center text-zinc-400">
                <span class="animate-pulse">Cargando información...</span>
            </div>
        @endif
    </flux:modal>
</div>
