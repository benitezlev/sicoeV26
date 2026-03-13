<?php

use function Livewire\Volt\{state, computed, layout};
use Spatie\Permission\Models\Role;
use Flux\Flux;

layout('layouts.app');

state([
    'roleId' => null,
    'roleName' => '',
]);

$roles = computed(fn() => Role::all());

$edit = function ($id) {
    $this->resetErrorBag();
    $role = Role::findOrFail($id);
    
    $this->roleId = $role->id;
    $this->roleName = $role->name;
    
    $this->dispatch('modal-show', name: 'role-modal');
};

$create = function () {
    $this->resetErrorBag();
    $this->roleId = null;
    $this->roleName = '';
    
    $this->dispatch('modal-show', name: 'role-modal');
};

$save = function () {
    $this->validate([
        'roleName' => 'required|min:3|unique:roles,name,' . $this->roleId,
    ]);

    if ($this->roleId) {
        $role = Role::findOrFail($this->roleId);
        $role->update(['name' => $this->roleName]);
        $msg = 'Rol actualizado';
    } else {
        Role::create(['name' => $this->roleName]);
        $msg = 'Rol creado';
    }

    $this->reset(['roleName', 'roleId']);
    unset($this->roles);
    $this->dispatch('modal-hide', name: 'role-modal');
    
    Flux::toast(
        heading: $msg,
        text: "La estructura de roles ha sido actualizada correctamente.",
        variant: 'success',
    );
};

$delete = function ($id) {
    $role = Role::findOrFail($id);
    if ($role->name === 'admin_ti') {
        Flux::toast(
            heading: 'Error',
            text: 'No se puede eliminar el rol de Super Administrador.',
            variant: 'danger',
        );
        return;
    }

    $role->delete();
    unset($this->roles);

    Flux::toast(
        heading: 'Rol eliminado',
        text: 'El rol ha sido removido del sistema.',
    );
};

?>

<div class="p-6">
    <x-slot name="header">Gestión de Roles</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Roles del Sistema</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Define los niveles de acceso y permisos para cada tipo de usuario.</p>
            </div>
            
            <flux:button variant="primary" icon="plus" size="sm" wire:click="create">Nuevo Rol</flux:button>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500">Nombre Operativo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Tipo de Guard</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->roles as $role)
                        <tr wire:key="role-row-{{ $role->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-xs">{{ $role->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-900 text-zinc-500 text-[10px] font-mono border border-zinc-200 dark:border-zinc-700">
                                    {{ $role->guard_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-1 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $role->id }})" wire:loading.attr="disabled" />
                                    
                                    @if($role->name !== 'admin_ti')
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-{{ $role->id }}' })" />
                                    @endif
                                </div>

                                <!-- Modal de eliminación -->
                                <div x-data="{ open: false }" 
                                     x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-{{ $role->id }}') open = true" 
                                     x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-{{ $role->id }}') open = false" 
                                     x-show="open" x-cloak 
                                     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                    <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                        <div class="space-y-2">
                                            <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Rol?</h3>
                                            <p class="text-sm text-zinc-500 leading-relaxed font-medium">
                                                Estás por eliminar el rol <span class="font-bold text-zinc-900 dark:text-white underline">{{ $role->name }}</span>. 
                                                Esta acción es irreversible y afectará de inmediato a los usuarios asignados.
                                            </p>
                                        </div>

                                        <div class="flex gap-3 justify-end pt-2">
                                            <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                            <flux:button type="button" variant="danger" wire:click="delete({{ $role->id }})" x-on:click="open = false" class="font-black uppercase tracking-widest text-[10px]">Confirmar Baja</flux:button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Formulario -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'role-modal') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'role-modal') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
            <form wire:submit="save" class="space-y-6" wire:key="role-form-{{ $roleId ?? 'new' }}">
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $roleId ? 'Editar Rol' : 'Nuevo Rol' }}</h2>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter italic">Identificador del rol en el sistema de permisos.</p>
                </div>

                <flux:field>
                    <flux:label>Nombre del Rol</flux:label>
                    <flux:input wire:model="roleName" 
                                placeholder="Ej. administrativo, docente..." 
                                wire:key="role-input-{{ $roleId ?? 'new' }}"
                                class="font-bold uppercase" />
                    <flux:error name="roleName" />
                </flux:field>

                <div class="flex gap-3 justify-end pt-2">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8 font-black uppercase tracking-widest text-[10px]">
                        {{ $roleId ? 'Guardar Cambios' : 'Generar Rol' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
