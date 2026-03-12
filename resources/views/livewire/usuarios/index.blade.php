<?php

use function Livewire\Volt\{state, computed, layout, usesPagination, on};
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

usesPagination();
layout('layouts.app');

state([
    'search' => '',
    'roleFilter' => '',
    'userId' => null,
    'nombre' => '',
    'paterno' => '',
    'materno' => '',
    'email' => '',
    'curp' => '',
    'password' => '',
    'selectedRoles' => [],
    'tipo' => 'alumno',
    'showUserModal' => false,
]);

$users = computed(function () {
    return User::query()
        ->with(['roles', 'expediente'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('paterno', 'like', '%' . $this->search . '%')
                  ->orWhere('materno', 'like', '%' . $this->search . '%')
                  ->orWhere('curp', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->roleFilter, function ($query) {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->roleFilter));
        })
        ->latest()
        ->paginate(10);
});

$roles = computed(fn() => Role::all());

$edit = function ($id) {
    $user = User::with('roles')->findOrFail($id);
    
    $this->resetErrorBag();
    
    $this->userId = $user->id;
    $this->nombre = $user->nombre;
    $this->paterno = $user->paterno;
    $this->materno = $user->materno;
    $this->email = $user->email;
    $this->curp = $user->curp;
    $this->tipo = $user->tipo ?? 'alumno';
    $this->selectedRoles = $user->roles->pluck('name')->toArray();
    $this->password = '';

    $this->dispatch('modal-show', name: 'user-modal');
};

$create = function () {
    $this->userId = null;
    $this->nombre = '';
    $this->paterno = '';
    $this->materno = '';
    $this->email = '';
    $this->curp = '';
    $this->password = '';
    $this->tipo = 'alumno';
    $this->selectedRoles = [];

    $this->dispatch('modal-show', name: 'user-modal');
};

$save = function () {
    $rules = [
        'nombre' => 'required|string|max:255',
        'paterno' => 'required|string|max:255',
        'materno' => 'nullable|string|max:255',
        'email' => 'required|email|unique:users,email,' . $this->userId,
        'curp' => 'nullable|string|size:18',
        'tipo' => 'required|string',
    ];

    if (!$this->userId) {
        $rules['password'] = 'required|min:8';
    }

    $data = $this->validate($rules);

    if ($this->password) {
        $data['password'] = Hash::make($this->password);
    }

    $data['name'] = trim($this->nombre . ' ' . $this->paterno . ' ' . ($this->materno ?? ''));
    $data['username'] = $this->email;

    if ($this->userId) {
        $user = User::findOrFail($this->userId);
        $user->update($data);
    } else {
        $user = User::create($data);
    }

    $user->syncRoles($this->selectedRoles);

    $this->dispatch('modal-hide', name: 'user-modal');
    
    Flux::toast(
        heading: $this->userId ? 'Usuario actualizado' : 'Usuario creado',
        variant: 'success',
    );
};

$delete = function ($id) {
    $user = User::findOrFail($id);
    if ($user->id === auth()->id()) {
        Flux::toast(
            heading: 'Error',
            text: 'No puedes eliminarte a ti mismo.',
            variant: 'danger',
        );
        return;
    }

    $user->delete();
    
    Flux::toast(
        heading: 'Usuario eliminado',
        text: 'El usuario ha sido borrado del sistema.',
        variant: 'success',
    );
};

?>

<div>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading size="xl">Directorio de Usuarios</flux:heading>
            
            <div class="flex gap-2">
                @can('gestionar alumnos')
                <flux:button href="{{ route('alumnos.importar') }}" icon="arrow-up-tray" variant="outline">Importar Alumnos</flux:button>
                <flux:button variant="primary" icon="plus" wire:click="create" wire:loading.attr="disabled">Nuevo Usuario</flux:button>
                @endcan
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 items-end">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, CURP o correo..." icon="magnifying-glass" class="max-w-md w-full" />
            
            <flux:select wire:model.live="roleFilter" placeholder="Filtrar por rol" class="max-w-xs">
                <flux:select.option value="">Todos los roles</flux:select.option>
                @foreach ($this->roles as $role)
                    <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="min-w-[300px]">Usuario</flux:table.column>
                    <flux:table.column class="min-w-[200px]">Identificación</flux:table.column>
                    <flux:table.column class="min-w-[150px]">Roles</flux:table.column>
                    <flux:table.column align="center">Expediente</flux:table.column>
                    <flux:table.column align="center">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3 whitespace-normal">
                                    <flux:avatar src="{{ $user->profile_photo_url }}" :name="$user->nombre" size="sm" class="flex-shrink-0" />
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-medium text-zinc-800 dark:text-white leading-tight break-words">
                                            {{ $user->nombre }} {{ $user->paterno }} {{ $user->materno }}
                                        </span>
                                        <span class="text-xs text-zinc-500 break-all">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col text-xs whitespace-normal">
                                    <span class="text-zinc-400 uppercase tracking-tighter">CURP</span>
                                    <span class="font-mono text-zinc-600 dark:text-zinc-300 break-all">{{ $user->curp ?? 'N/A' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ $role->name }}</flux:badge>
                                    @endforeach
                                </div>
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                @if($user->expediente)
                                    <flux:badge size="sm" :color="$user->expediente->estatus === 'completo' ? 'green' : 'amber'" variant="pill">
                                        {{ ucfirst($user->expediente->estatus) }}
                                    </flux:badge>
                                @else
                                    <span class="text-xs text-zinc-400 italic">No generado</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="center">
                                <div class="flex gap-2 justify-center">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $user->id }})" wire:loading.attr="disabled" />
                                    
                                    @if($user->id !== auth()->id())
                                        <flux:modal.trigger name="confirm-delete-{{ $user->id }}">
                                            <flux:button variant="ghost" size="sm" color="red" icon="trash" />
                                        </flux:modal.trigger>
                                    @endif
                                </div>

                                <flux:modal name="confirm-delete-{{ $user->id }}" class="max-w-md">
                                    <form wire:submit="delete({{ $user->id }})" class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">¿Eliminar Usuario?</flux:heading>
                                            <flux:subheading>
                                                Estás por eliminar a <b>{{ $user->nombre }}</b>. Esta acción borrará su acceso al sistema pero conservará sus registros históricos si existen dependencias.
                                            </flux:subheading>
                                        </div>

                                        <div class="flex gap-2 justify-end">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancelar</flux:button>
                                            </flux:modal.close>
                                            <flux:button type="submit" variant="danger">Eliminar Usuario</flux:button>
                                        </div>
                                    </form>
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                                No se encontraron usuarios que coincidan con la búsqueda.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if($this->users->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Usuario (Crear/Editar) -->
    <flux:modal name="user-modal" class="max-w-xl">
        <form wire:submit="save" class="space-y-6">
            <div wire:key="user-form-header-{{ $userId }}">
                <flux:heading size="lg">{{ $userId ? 'Editar Usuario: '.$nombre : 'Nuevo Usuario' }}</flux:heading>
                <flux:subheading>Completa la información del usuario en el sistema.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" wire:key="user-form-fields-{{ $userId }}">
                <flux:input wire:model="nombre" label="Nombre(s)" placeholder="Ej. Juan" />
                <flux:input wire:model="paterno" label="Apellido Paterno" placeholder="Ej. Pérez" />
                <flux:input wire:model="materno" label="Apellido Materno" placeholder="Ej. López" />
                <flux:input wire:model="curp" label="CURP" placeholder="Ej. ABCD123456..." maxlength="18" />
                
                <flux:input wire:model="email" label="Correo Electrónico" placeholder="gmail@ejemplo.com" />
                <flux:input wire:model="password" type="password" label="Contraseña" :placeholder="$userId ? 'Dejar en blanco para no cambiar' : 'Mínimo 8 caracteres'" />
                
                <flux:select wire:model="tipo" label="Tipo de Usuario">
                    <flux:select.option value="alumno">Alumno</flux:select.option>
                    <flux:select.option value="docente">Docente</flux:select.option>
                    <flux:select.option value="staff">Staff/Administrativo</flux:select.option>
                </flux:select>

                <flux:fieldset label="Asignar Roles">
                    <div class="flex flex-wrap gap-4 mt-2">
                        @foreach($this->roles as $role)
                            <flux:checkbox wire:model="selectedRoles" :value="$role->name" :label="$role->name" />
                        @endforeach
                    </div>
                </flux:fieldset>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Usuario</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
