<?php

use function Livewire\Volt\{state, computed, layout};
use Spatie\Permission\Models\Role;
use Flux\Flux;

layout('layouts.app');

state(['name' => '']);

$roles = computed(fn() => Role::all());

$save = function () {
    $this->validate([
        'name' => 'required|min:3|unique:roles,name',
    ]);

    Role::create(['name' => $this->name]);

    $this->reset('name');
    unset($this->roles);
    $this->dispatch('modal-hide', name: 'create-role');
    $this->dispatch('role-created');

    Flux::toast(
        heading: 'Rol creado',
        text: "El rol {$this->name} ha sido creado exitosamente.",
        variant: 'success',
    );
};

$delete = function (Role $role) {
    if ($role->name === 'admin_ti') {
        Flux::toast(
            heading: 'Error',
            text: 'No se puede eliminar el rol de Super Administrador.',
            variant: 'danger',
        );
        return;
    }

    $role->delete();
    $this->dispatch('role-deleted');

    Flux::toast(
        heading: 'Rol eliminado',
        text: 'El rol ha sido eliminado correctamente.',
    );
};

?>

<div>
    <x-slot name="header">Gestión de Roles</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading size="xl">Roles del Sistema</flux:heading>
            
            <flux:modal.trigger name="create-role">
                <flux:button variant="primary" icon="plus">Nuevo Rol</flux:button>
            </flux:modal.trigger>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column align="center" class="px-3!">Nombre</flux:table.column>
                    <flux:table.column align="center">Guard</flux:table.column>
                    <flux:table.column align="center" class="px-3!">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->roles as $role)
                        <flux:table.row :key="$role->id">
                            <flux:table.cell align="center" class="px-3! font-medium">{{ $role->name }}</flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:badge size="sm" color="zinc" inset="top bottom">{{ $role->guard_name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="center" class="px-3!">
                                <div class="flex gap-2 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" />
                                    
                                    @if($role->name !== 'admin_ti')
                                        <flux:modal.trigger name="confirm-delete-{{ $role->id }}">
                                            <flux:button variant="ghost" size="sm" color="red" icon="trash" />
                                        </flux:modal.trigger>
                                    @endif
                                </div>

                                <flux:modal name="confirm-delete-{{ $role->id }}" class="max-w-md">
                                    <form wire:submit="delete({{ $role->id }})" class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">¿Eliminar Rol?</flux:heading>
                                            <flux:subheading>
                                                Esta acción no se puede deshacer. Los usuarios con este rol perderán sus permisos.
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
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Modal para crear rol -->
    <flux:modal name="create-role" class="max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Nuevo Rol</flux:heading>
                <flux:subheading>Define el nombre del nuevo rol para el sistema.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Nombre del Rol</flux:label>
                <flux:input wire:model="name" placeholder="Ej. coordinador, docente_ti, etc." />
                <flux:error name="name" />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Crear Rol</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
