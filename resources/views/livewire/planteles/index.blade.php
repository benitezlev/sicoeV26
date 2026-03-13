<?php

use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Plantel;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'name' => '',
    'direccion' => '',
    'tel' => '',
    'titular' => '',
    'editingPlantelId' => null,
    'showPlantelModal' => false,
    
    // Migración de datos
    'plantelToDelete' => null,
    'targetPlantelId' => '',
]);

$planteles = computed(function () {
    return Plantel::query()
        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('titular', 'like', '%' . $this->search . '%')
                  ->orWhere('tel', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(10);
});

$save = function () {
    $rules = [
        'name' => 'required|string|max:255',
        'direccion' => 'required|string|max:500',
        'tel' => 'required|string|max:20',
        'titular' => 'required|string|max:255',
    ];

    $this->validate($rules);

    if ($this->editingPlantelId) {
        $plantel = Plantel::find($this->editingPlantelId);
        $plantel->update([
            'name' => $this->name,
            'direccion' => $this->direccion,
            'tel' => $this->tel,
            'titular' => $this->titular,
        ]);
        $message = 'Plantel actualizado exitosamente.';
    } else {
        Plantel::create([
            'name' => $this->name,
            'direccion' => $this->direccion,
            'tel' => $this->tel,
            'titular' => $this->titular,
        ]);
        $message = 'Plantel creado exitosamente.';
    }

    $this->reset(['name', 'direccion', 'tel', 'titular', 'editingPlantelId']);
    unset($this->planteles);
    $this->dispatch('modal-hide', name: 'plantel-modal');

    Flux::toast(
        heading: 'Correcto',
        text: $message,
        variant: 'success',
    );
};

$edit = function ($id) {
    $plantel = Plantel::findOrFail($id);
    $this->resetErrorBag();
    
    $this->editingPlantelId = $plantel->id;
    $this->name = $plantel->name;
    $this->direccion = $plantel->direccion;
    $this->tel = $plantel->tel;
    $this->titular = $plantel->titular;

    $this->dispatch('modal-show', name: 'plantel-modal');
};

$delete = function (Plantel $plantel) {
    // Verificar dependencias
    $usersCount = $plantel->users()->count();
    $gruposCount = \App\Models\Grupo::where('plantel_id', $plantel->id)->count();

    if ($usersCount > 0 || $gruposCount > 0) {
        $this->plantelToDelete = $plantel;
        $this->targetPlantelId = '';
        $this->dispatch('modal-show', name: 'migration-modal');
        return;
    }

    try {
        $plantel->delete();
        unset($this->planteles);
        Flux::toast(heading: 'Plantel eliminado', text: 'El plantel ha sido borrado del sistema.');
    } catch (\Exception $e) {
        Flux::toast(heading: 'Error', text: 'No se pudo eliminar el plantel.', variant: 'danger');
    }
};

$migrateAndDelete = function () {
    $this->validate([
        'targetPlantelId' => 'required|exists:planteles,id|different:plantelToDelete.id',
    ]);

    $oldId = $this->plantelToDelete->id;
    $newId = $this->targetPlantelId;

    try {
        \Illuminate\Support\Facades\DB::transaction(function() use ($oldId, $newId) {
            // Migrar Usuarios
            \App\Models\User::where('plantel_id', $oldId)->update(['plantel_id' => $newId]);
            
            // Migrar Grupos
            \App\Models\Grupo::where('plantel_id', $oldId)->update(['plantel_id' => $newId]);

            // Eliminar Plantel
            Plantel::find($oldId)->delete();
        });

        $this->dispatch('modal-hide', name: 'migration-modal');
        unset($this->planteles);
        
        Flux::toast(
            heading: 'Migración exitosa',
            text: "Se trasladaron los elementos al nuevo plantel y se eliminó el anterior.",
            variant: 'success'
        );
    } catch (\Exception $e) {
        Flux::toast(heading: 'Error técnico', text: 'Ocurrió un error durante la migración.', variant: 'danger');
    }
};

$resetForm = function () {
    $this->resetErrorBag();
    $this->reset(['name', 'direccion', 'tel', 'titular', 'editingPlantelId']);
    $this->dispatch('modal-show', name: 'plantel-modal');
};

?>

<div>
    <x-slot name="header">Gestión de Planteles</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading size="xl">Planteles Registrados</flux:heading>
            
            <flux:button variant="primary" icon="plus" wire:click="resetForm">Nuevo Plantel</flux:button>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="flex gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, titular o teléfono..." icon="magnifying-glass" class="max-w-md" />
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="min-w-[250px]">Nombre</flux:table.column>
                    <flux:table.column class="min-w-[200px]">Titular</flux:table.column>
                    <flux:table.column align="center" class="w-32">Teléfono</flux:table.column>
                    <flux:table.column class="min-w-[250px]">Dirección</flux:table.column>
                    <flux:table.column align="center">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->planteles as $plantel)
                        <flux:table.row :key="$plantel->id">
                            <flux:table.cell>
                                <div class="flex items-start gap-2 whitespace-normal">
                                    <flux:icon name="building-office" variant="mini" class="text-zinc-400 mt-1 flex-shrink-0" />
                                    <span class="font-medium leading-tight">{{ $plantel->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="block whitespace-normal leading-tight">{{ $plantel->titular }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:badge size="sm" color="zinc" inset="top bottom">{{ $plantel->tel }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-xs text-zinc-500 block whitespace-normal leading-tight">{{ $plantel->direccion }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <div class="flex gap-2 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $plantel->id }})" wire:loading.attr="disabled" />
                                    
                                    <flux:modal.trigger name="confirm-delete-{{ $plantel->id }}">
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" />
                                    </flux:modal.trigger>
                                </div>

                                <flux:modal name="confirm-delete-{{ $plantel->id }}" class="max-w-md">
                                    <form wire:submit="delete({{ $plantel->id }})" class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">¿Eliminar Plantel?</flux:heading>
                                            <flux:subheading>
                                                Confirmas que deseas eliminar el plantel <b>{{ $plantel->name }}</b>. Esta acción es irreversible.
                                            </flux:subheading>
                                        </div>

                                        <div class="flex gap-2 justify-end">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancelar</flux:button>
                                            </flux:modal.close>
                                            <flux:button type="submit" variant="danger">Eliminar</flux:button>
                                        </div>
                                    </form>
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                                No se encontraron planteles con los criterios de búsqueda.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if($this->planteles->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->planteles->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal name="plantel-modal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6" wire:key="plantel-form-{{ $editingPlantelId ?? 'new' }}">
            <div>
                <flux:heading size="lg">{{ $editingPlantelId ? 'Editar Plantel' : 'Nuevo Plantel' }}</flux:heading>
                <flux:subheading>Completa la información oficial del plantel educativo.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nombre del Plantel</flux:label>
                    <flux:input wire:model="name" placeholder="Ej. Plantel Hermosillo I" icon="building-office-2" />
                    <flux:error name="name" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Titular / Director</flux:label>
                        <flux:input wire:model="titular" placeholder="Nombre completo" icon="user" />
                        <flux:error name="titular" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Teléfono</flux:label>
                        <flux:input wire:model="tel" placeholder="662..." icon="phone" />
                        <flux:error name="tel" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Dirección Completa</flux:label>
                    <flux:textarea wire:model="direccion" rows="3" placeholder="Calle, Número, Colonia, CP..." />
                    <flux:error name="direccion" />
                </flux:field>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingPlantelId ? 'Guardar Cambios' : 'Crear Plantel' }}</flux:button>
            </div>
        </form>
    </flux:modal>
    <!-- Modal de Migración -->
    <flux:modal name="migration-modal" class="max-w-md">
        <form wire:submit="migrateAndDelete" class="space-y-6 text-start">
            <div>
                <flux:heading size="lg">Migración de Datos Necesaria</flux:heading>
                <flux:subheading>
                    El plantel <b>{{ $plantelToDelete?->name }}</b> tiene usuarios o grupos activos. 
                    Selecciona a dónde deseas migrarlos para poder proceder con la eliminación.
                </flux:subheading>
            </div>

            <flux:field>
                <flux:label>Plantel de Destino</flux:label>
                <flux:select wire:model="targetPlantelId" placeholder="Selecciona el nuevo plantel...">
                    @foreach(\App\Models\Plantel::where('id', '!=', $plantelToDelete?->id)->get() as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="targetPlantelId" />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Migrar y Eliminar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
