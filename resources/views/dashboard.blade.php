<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <div class="space-y-8">
        <livewire:dashboard.stats />
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <livewire:dashboard.copiloto />
            </div>
            <div class="lg:col-span-1">
                <livewire:project-status-card />
            </div>
        </div>
    </div>
</x-app-layout>
