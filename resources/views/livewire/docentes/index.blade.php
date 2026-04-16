<?php

use function Livewire\Volt\{state, computed, layout, updated};
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
            'per_page' => 100, // Traemos más para filtrar localmente si es necesario
        ]);

    $data = $response->json();
    
    // Si hay búsqueda, aplicamos un filtro manual extra por si la API no lo hace
    if (!empty($this->search) && isset($data['data'])) {
        $filtered = array_values(array_filter($data['data'], function($doc) {
            $s = strtolower($this->search);
            return str_contains(strtolower($doc['name']), $s) || 
                   str_contains(strtolower($doc['curp']), $s) || 
                   str_contains(strtolower($doc['email']), $s);
        }));
        $data['data'] = $filtered;
    }

    return $data;
});

// Resetear página al buscar
updated(['search' => function() { $this->page = 1; }]);

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

<div class="space-y-8 p-1">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="space-y-1">
            <flux:heading size="xl" class="font-black uppercase tracking-tight">Directorio Institucional (SAD)</flux:heading>
            <flux:subheading class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Consulta y sincronización de personal docente validado</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button icon="arrow-path" variant="ghost" wire:click="$refresh" class="text-xs font-bold uppercase tracking-widest">Actualizar Base</flux:button>
        </div>
    </div>

    <!-- Filtros de búsqueda premium -->
    <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <div class="relative w-full max-w-xl">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Filtrar por nombre, CURP o correo electrónico..." 
                    icon="magnifying-glass" 
                    class="h-12 shadow-inner !bg-zinc-50 dark:!bg-zinc-900/50" 
                />
            </div>
            <div class="hidden md:block flex-1"></div>
            <div class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] px-4 py-2 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800">
                Total API: {{ count($this->docentes['data'] ?? []) }} registros
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-xl">
        <div wire:loading.class="opacity-50" class="transition-opacity duration-300">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="!px-6 !py-4 font-black uppercase text-[10px] tracking-widest text-zinc-400">Docente / Contacto</flux:table.column>
                    <flux:table.column class="!px-6 !py-4 font-black uppercase text-[10px] tracking-widest text-zinc-400">Identificación (CURP)</flux:table.column>
                    <flux:table.column class="!px-6 !py-4 font-black uppercase text-[10px] tracking-widest text-zinc-400">Adscripción Actual</flux:table.column>
                    <flux:table.column class="!px-6 !py-4 font-black uppercase text-[10px] tracking-widest text-zinc-400">Clasificación</flux:table.column>
                    <flux:table.column align="center" class="!px-6 !py-4 font-black uppercase text-[10px] tracking-widest text-zinc-400">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->docentes['data'] ?? [] as $docente)
                        <flux:table.row :key="$docente['id']" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors group">
                            <flux:table.cell class="!px-6 !py-4">
                                <div class="flex items-center gap-4 min-w-[300px]">
                                    <div class="size-10 rounded-xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center font-black text-zinc-600 dark:text-zinc-300 shadow-sm border border-zinc-200 dark:border-zinc-600 group-hover:from-blue-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-300">
                                        {{ substr($docente['name'], 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-zinc-900 dark:text-white leading-tight group-hover:text-blue-600 transition-colors">
                                            {{ $docente['name'] }}
                                        </span>
                                        <span class="text-[10px] text-zinc-500 font-mono mt-0.5">{{ $docente['email'] }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="!px-6 !py-4">
                                <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 font-mono text-[11px] font-bold rounded border border-zinc-200 dark:border-zinc-700 tracking-tighter">{{ $docente['curp'] }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="!px-6 !py-4">
                                <div class="flex flex-col min-w-[200px] text-wrap">
                                    <span class="text-xs font-black text-zinc-800 dark:text-zinc-200 uppercase tracking-tight">{{ $docente['plantel'] }}</span>
                                    <span class="text-[10px] text-zinc-500 font-medium leading-tight mt-1">{{ $docente['adscrip'] ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="!px-6 !py-4">
                                <flux:badge size="sm" color="zinc" variant="outline" class="font-black uppercase text-[9px] tracking-widest px-3 border-zinc-200 dark:border-zinc-700 bg-white/50 dark:bg-black/20">{{ $docente['cargo'] }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="center" class="!px-6 !py-4">
                                <div class="flex justify-center gap-2">
                                    <flux:button variant="ghost" size="sm" icon="eye" wire:click="verDetalle({{ $docente['id'] }})" title="Ver Perfil" />
                                    <flux:button variant="ghost" size="sm" icon="arrow-down-tray" wire:click="sincronizar({{ $docente['id'] }})" title="Sincronizar" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center" class="py-24 text-zinc-400">
                                <div class="flex flex-col items-center gap-4">
                                    <flux:icon name="magnifying-glass-circle" class="size-16 opacity-10" />
                                    <div class="space-y-1">
                                        <p class="text-lg font-bold text-zinc-500 italic">No se encontraron docentes</p>
                                        <p class="text-xs text-zinc-400">La consulta a la plataforma SAD no devolvió registros con ese criterio.</p>
                                    </div>
                                </div>
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

    <!-- Modal Detalle Docente (Refined Profile View) -->
    <flux:modal name="modal-detalle" class="max-w-xl">
        @if ($selectedDocente)
            <div class="space-y-8">
                <!-- Profile Header -->
                <div class="relative overflow-hidden p-6 -m-6 rounded-t-2xl bg-gradient-to-br from-blue-700 to-indigo-900 text-white">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    <div class="relative z-10 flex flex-col items-center gap-4 py-4">
                        <div class="size-24 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 p-1 shadow-2xl">
                            <div class="w-full h-full bg-white dark:bg-zinc-800 rounded-2xl flex items-center justify-center text-blue-700 dark:text-blue-400 font-black text-3xl shadow-inner">
                                {{ substr($selectedDocente['name'], 0, 1) }}
                            </div>
                        </div>
                        <div class="text-center space-y-1">
                            <h2 class="text-2xl font-black uppercase tracking-tight leading-tight">{{ $selectedDocente['name'] }}</h2>
                            <p class="text-[10px] font-bold text-blue-200 uppercase tracking-[0.2em] opacity-80 italic">{{ $selectedDocente['cargo'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile Data Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <div class="space-y-1.5 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <span class="block text-[9px] font-black text-zinc-400 uppercase tracking-widest">Identificación (CURP)</span>
                        <div class="text-sm font-mono font-bold text-zinc-900 dark:text-white uppercase">
                            {{ $selectedDocente['curp'] }}
                        </div>
                    </div>

                    <div class="space-y-1.5 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <span class="block text-[9px] font-black text-zinc-400 uppercase tracking-widest">Correo Institucional</span>
                        <div class="text-sm font-bold text-zinc-900 dark:text-white truncate">
                            {{ $selectedDocente['email'] }}
                        </div>
                    </div>

                    <div class="space-y-1.5 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 md:col-span-2">
                        <span class="block text-[9px] font-black text-zinc-400 uppercase tracking-widest">Plantel de Adscripción</span>
                        <div class="text-sm font-black text-blue-600 dark:text-blue-400 uppercase tracking-tight">
                            {{ $selectedDocente['plantel'] }}
                        </div>
                    </div>

                    <div class="space-y-1.5 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 md:col-span-2">
                        <span class="block text-[9px] font-black text-zinc-400 uppercase tracking-widest">Puesto en Estructura</span>
                        <div class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            {{ $selectedDocente['puesto'] }}
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="text-xs font-bold uppercase tracking-widest px-6">Cerrar</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" icon="arrow-down-tray" wire:click="sincronizar({{ $selectedDocente['id'] }})" class="text-xs font-black uppercase tracking-widest px-8">Importar a SICOE</flux:button>
                </div>
            </div>
        @else
            <div class="p-20 flex flex-col items-center justify-center gap-4 text-zinc-400">
                <flux:icon name="arrow-path" class="size-10 animate-spin" />
                <span class="text-sm font-black uppercase tracking-widest">Sincronizando con SAD...</span>
            </div>
        @endif
    </flux:modal>
</div>
