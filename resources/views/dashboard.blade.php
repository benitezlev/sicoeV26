<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <div class="space-y-8">
        <livewire:dashboard.stats />
        
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <flux:heading size="lg" class="mb-4">Historial Reciente</flux:heading>
            <x-welcome />
        </div>
    </div>
</x-app-layout>
