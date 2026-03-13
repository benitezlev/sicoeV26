<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <div class="space-y-8">
        <livewire:dashboard.stats />
        


        <livewire:project-status-card />
    </div>
</x-app-layout>
