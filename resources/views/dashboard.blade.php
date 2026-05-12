<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control') }}
    </x-slot>

    <div class="space-y-8">
        <!-- Estadísticas de matrícula, finanzas y metas de SICOE -->
        <livewire:dashboard.stats />
        
        <!-- Estado de Proyecto y Avisos Institucionales -->
        <div class="grid grid-cols-1 gap-8">
            <livewire:project-status-card />
        </div>
    </div>
</x-app-layout>
