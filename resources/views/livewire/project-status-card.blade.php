<?php

use function Livewire\Volt\{state};
use App\Models\User;
use App\Models\Grupo;

state([
    'totalUsuarios' => fn() => User::count(),
    'totalGrupos' => fn() => Grupo::count(),
    'ultimaActualizacion' => now()->format('H:i:s'),
]);

$refreshStats = function () {
    $this->ultimaActualizacion = now()->format('H:i:s');
};

?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Usuarios</flux:heading>
                <flux:badge color="teal" variant="pill">En línea</flux:badge>
            </div>
            <div class="flex items-baseline gap-2">
                <div class="text-3xl font-bold dark:text-white">{{ $totalUsuarios }}</div>
                <div class="text-xs text-zinc-500">Registrados</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Grupos</flux:heading>
                <flux:badge color="indigo" variant="pill">Activos</flux:badge>
            </div>
            <div class="flex items-baseline gap-2">
                <div class="text-3xl font-bold dark:text-white">{{ $totalGrupos }}</div>
                <div class="text-xs text-zinc-500">Creados</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Actualización</flux:heading>
                <flux:button wire:click="refreshStats" variant="ghost" icon="arrow-path" size="sm" />
            </div>
            <div class="flex items-baseline gap-2">
                <div class="text-xl font-medium dark:text-white truncate">{{ $ultimaActualizacion }}</div>
                <div class="text-xs text-zinc-500">Sincronizado</div>
            </div>
        </div>
    </div>
</div>
