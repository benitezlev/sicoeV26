<?php

use function Livewire\Volt\{state, computed, layout, usesPagination, on};
use App\Models\User;
use App\Models\Municipio;
use App\Models\Plantel;
use App\Models\Expediente;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
    'tipo' => 'alumno',
    'nivel' => 'estatal',
    'plantel_id' => '',
    'municipio_id' => '',
    'dependencia' => '',
    'adscripcion' => '',
    'area_especializada' => '',
    'sexo' => 'H',
    'perfil_academico' => 'aspirante', // aspirante | activo
    'cuip' => '',
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
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->roleFilter, function ($query) {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->roleFilter));
        })
        ->latest()
        ->paginate(10);
});

$roles = computed(fn() => Role::all());
$municipios = computed(fn() => Municipio::orderBy('nombre')->get());
$planteles = computed(fn() => Plantel::all());

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
    $this->nivel = $user->nivel ?? 'estatal';
    $this->plantel_id = $user->plantel_id;
    $this->municipio_id = $user->municipio_id;
    $this->sexo = $user->sexo ?? 'H';
    $this->cuip = $user->cuip ?? '';
    $this->perfil_academico = $this->cuip ? 'activo' : 'aspirante';
    
    // Desglosar perfil_data
    $perfil = $user->perfil_data ?? [];
    $this->dependencia = $perfil['dependencia'] ?? '';
    $this->adscripcion = $perfil['adscripcion'] ?? '';
    $this->area_especializada = $perfil['area_especializada'] ?? '';

    $this->password = '';

    $this->dispatch('modal-show', name: 'user-modal');
};

$create = function () {
    $this->resetErrorBag();
    $this->userId = null;
    $this->nombre = '';
    $this->paterno = '';
    $this->materno = '';
    $this->email = '';
    $this->curp = '';
    $this->password = '';
    $this->tipo = 'alumno';
    $this->nivel = 'estatal';
    $this->plantel_id = '';
    $this->perfil_academico = 'aspirante';
    $this->cuip = '';
    $this->municipio_id = '';
    $this->dependencia = '';
    $this->adscripcion = '';
    $this->area_especializada = '';
    $this->sexo = 'H';

    $this->dispatch('modal-show', name: 'user-modal');
};

$save = function () {
    $rules = [
        'nombre' => 'required|string|max:255',
        'paterno' => 'required|string|max:255',
        'materno' => 'nullable|string|max:255',
        'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
        'curp' => 'nullable|string|size:18',
        'tipo' => 'required|string',
        'nivel' => 'required|string',
        'plantel_id' => 'nullable|exists:planteles,id',
        'sexo' => 'required|in:H,M',
    ];

    if (!$this->userId) {
        $rules['password'] = 'required|min:8';
    }

    $data = $this->validate($rules);

    // Forzar jurisdicción si no es super admin
    if (!auth()->user()->hasRole('admin_ti')) {
        if (auth()->user()->plantel_id) {
            $data['plantel_id'] = auth()->user()->plantel_id;
        }
        if (auth()->user()->municipio_id) {
            $data['municipio_id'] = auth()->user()->municipio_id;
        }
        if (auth()->user()->nivel) {
            $data['nivel'] = auth()->user()->nivel;
        }
    } else {
        // Si es super admin, tomar el municipio_id del selector
        $data['municipio_id'] = $this->municipio_id ?: null;
    }

    // Limpiar IDs para evitar errores de sintaxis en Postgres (bigint)
    $data['plantel_id'] = ($data['plantel_id'] ?? null) ?: null;
    $data['municipio_id'] = ($data['municipio_id'] ?? null) ?: null;

    if ($this->password) {
        $data['password'] = Hash::make($this->password);
    }

    $data['username'] = $this->curp ?: $this->email;
    
    // Empaquetar perfil_data
    $data['perfil_data'] = [
        'municipio_id' => $this->municipio_id ?: null,
        'dependencia' => $this->dependencia,
        'adscripcion' => $this->adscripcion,
        'area_especializada' => $this->area_especializada,
    ];

    if ($this->userId) {
        $user = User::findOrFail($this->userId);
        
        // Detectar cambios antes de actualizar
        $originalNivel = $user->nivel;
        $originalPlantel = $user->plantel_id;
        $originalPerfil = $user->perfil_data;

        $user->update($data);

        // Registrar movimiento si hubo cambios críticos
        if ($originalNivel != $data['nivel'] || $originalPlantel != $data['plantel_id'] || $originalPerfil != $data['perfil_data']) {
            \App\Models\UsuarioMovimiento::create([
                'user_id' => $user->id,
                'tipo_movimiento' => 'cambio_adscripcion',
                'nivel_anterior' => $originalNivel,
                'perfil_data_anterior' => $originalPerfil,
                'plantel_id_anterior' => $originalPlantel,
                'nivel_nuevo' => $user->nivel,
                'perfil_data_nuevo' => $user->perfil_data,
                'plantel_id_nuevo' => $user->plantel_id,
                'registrado_por' => auth()->id(),
                'motivo' => 'Actualización manual desde panel administrativo',
            ]);
        }
    } else {
        $user = User::create($data);

        // Crear expediente automático para el nuevo usuario
        $prefix = match($user->nivel) {
            'municipal' => 'MUN',
            'fiscalia' => 'FIS',
            default => 'EST'
        };

        Expediente::create([
            'user_id' => $user->id,
            'folio' => "{$prefix}-" . date('Y') . "-" . str_pad($user->id, 5, '0', STR_PAD_LEFT),
            'estatus' => 'incompleto',
            'fecha_apertura' => now(),
        ]);
    }

    // Sincronizar rol automáticamente según el tipo de usuario
    $roleName = match($this->tipo) {
        'admin' => 'admin_ti',
        'control' => 'control_escolar',
        'operador' => 'operador',
        'docente' => 'docente',
        'alumno' => 'alumno',
        default => 'alumno'
    };
    $user->syncRoles([$roleName]);

    $this->dispatch('modal-hide', name: 'user-modal');
    
    Flux::toast(
        text: $this->userId ? 'El usuario ha sido actualizado con éxito.' : 'El usuario ha sido registrado con éxito.',
        heading: $this->userId ? 'Actualizado' : 'Registrado',
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

<div class="p-6">
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Directorio de Usuarios</h1>
                <p class="text-xs text-zinc-500 font-medium">Administra el personal, sus roles y accesos al sistema.</p>
            </div>
            
            <div class="flex gap-2">
                @can('gestionar alumnos')
                <flux:button href="{{ route('usuarios.carga-masiva') }}" variant="ghost" icon="cloud-arrow-up" size="sm">Documentación Masiva</flux:button>
                <flux:button href="{{ route('alumnos.importar') }}" variant="ghost" icon="document-plus" size="sm">Importar CSV</flux:button>
                <flux:button wire:click="create" variant="primary" icon="plus" size="sm">Nuevo Usuario</flux:button>
                @endcan
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 items-end bg-white dark:bg-zinc-800 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="flex-1 min-w-[300px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, CURP o correo..." icon="magnifying-glass" />
            </div>
            
            <div class="w-full md:w-64">
                <flux:select wire:model.live="roleFilter" placeholder="Filtrar por rol">
                    <flux:select.option value="">Todos los roles</flux:select.option>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 min-w-[300px]">Usuario</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 min-w-[200px]">Identificación</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 min-w-[150px]">Roles</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 text-center">Expediente</th>
                        <th class="px-4 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3 whitespace-normal">
                                    <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if($user->profile_photo_url)
                                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->nombre }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-sm font-black text-zinc-400 uppercase">{{ substr($user->nombre, 0, 1) }}{{ substr($user->paterno, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-zinc-800 dark:text-white leading-tight break-words">
                                            {{ $user->nombre }} {{ $user->paterno }} {{ $user->materno }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400 font-mono tracking-tighter break-all">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-col text-xs gap-1 whitespace-normal">
                                    <div class="flex items-center gap-1">
                                        <span class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 text-[9px] font-bold uppercase border border-zinc-200 dark:border-zinc-700">
                                            {{ $user->nivel }}
                                        </span>
                                        <span class="font-mono text-zinc-500 dark:text-zinc-400 text-[10px] break-all">{{ $user->curp ?? $user->username }}</span>
                                    </div>
                                    <span class="text-[10px] text-zinc-400 italic leading-tight">
                                        @if($user->nivel === 'municipal' && isset($user->perfil_data['municipio_id']))
                                            {{ \App\Models\Municipio::find($user->perfil_data['municipio_id'])->nombre ?? 'Mun. Desconocido' }}
                                        @elseif($user->nivel === 'fiscalia' && isset($user->perfil_data['area_especializada']))
                                            Fiscalía - {{ $user->perfil_data['area_especializada'] }}
                                        @else
                                            {{ $user->perfil_data['dependencia'] ?? 'Sin dependencia' }}
                                        @endif
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <span class="px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[9px] font-bold border border-blue-100 dark:border-blue-800/30">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if($user->expediente)
                                    <a href="{{ route('expedientes.show', $user->expediente) }}" class="inline-block transition-transform hover:scale-105">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            {{ $user->expediente->estatus === 'completo' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 
                                               ($user->expediente->estatus === 'observado' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700 border border-amber-200') }}">
                                            {{ $user->expediente->estatus }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-[10px] text-zinc-400 italic">No generado</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center">
                                <div class="flex gap-1 justify-center">
                                    @if($user->expediente)
                                        <flux:button variant="ghost" size="sm" icon="folder-open" :href="route('expedientes.show', $user->expediente)" />
                                    @endif
                                    
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $user->id }})" />
                                    
                                    @if($user->id !== auth()->id())
                                        <flux:button variant="ghost" size="sm" color="red" icon="trash" x-on:click="$dispatch('modal-show', { name: 'confirm-delete-{{ $user->id }}' })" />
                                    @endif
                                </div>

                                <!-- Modal de eliminación -->
                                <div x-data="{ open: false }" 
                                     x-on:modal-show.window="if ($event.detail.name === 'confirm-delete-{{ $user->id }}') open = true" 
                                     x-on:modal-hide.window="if ($event.detail.name === 'confirm-delete-{{ $user->id }}') open = false" 
                                     x-show="open" x-cloak 
                                     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
                                    <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 text-left" x-on:click.away="open = false">
                                        <div class="space-y-2">
                                            <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">¿Eliminar Usuario?</h3>
                                            <p class="text-sm text-zinc-500 leading-relaxed">
                                                Estás por eliminar a <span class="font-bold text-zinc-900 dark:text-white">{{ $user->nombre }}</span>. 
                                                Esta acción borrará el acceso pero conservará registros históricos.
                                            </p>
                                        </div>

                                        <div class="flex gap-3 justify-end pt-2">
                                            <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                                            <flux:button type="submit" variant="danger" wire:click="delete({{ $user->id }})" x-on:click="open = false">Eliminar Usuario</flux:button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-zinc-400">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon name="magnifying-glass" class="w-8 h-8 opacity-20" />
                                    <span class="italic text-sm text-zinc-400">No se encontraron usuarios que coincidan con la búsqueda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->users->hasPages())
            <div class="px-2">
                {{ $this->users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Usuario (Crear/Editar) -->
    <div x-data="{ open: false }" 
         x-on:modal-show.window="if ($event.detail.name === 'user-modal') open = true" 
         x-on:modal-hide.window="if ($event.detail.name === 'user-modal') open = false" 
         x-show="open" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6 overflow-y-auto max-h-[90vh]" x-on:click.away="open = false">
            <form wire:submit="save" class="space-y-8">
                <div class="space-y-1">
                    <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">{{ $userId ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
                    <p class="text-xs text-zinc-500 font-medium">Define los datos personales y de adscripción del elemento.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <flux:input wire:model="nombre" label="Nombre(s)" placeholder="Ej. Juan" />
                    <flux:input wire:model="paterno" label="Apellido Paterno" placeholder="Ej. Pérez" />
                    <flux:input wire:model="materno" label="Apellido Materno" placeholder="Ej. López" />
                    <div class="md:col-span-2 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-2xl border border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row gap-6">
                        <div class="flex-1">
                            <flux:label>Estatus Académico</flux:label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="perfil_academico" value="aspirante" class="size-4 text-blue-600 border-zinc-300 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-zinc-600 group-hover:text-zinc-900 tracking-tight uppercase">Aspirante</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="perfil_academico" value="activo" class="size-4 text-blue-600 border-zinc-300 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-zinc-600 group-hover:text-zinc-900 tracking-tight uppercase">Elemento Activo</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex-1 space-y-4">
                            <flux:input wire:model="curp" label="CURP" placeholder="ABCD123456..." maxlength="18" />
                            @if($perfil_academico === 'activo')
                                <flux:input wire:model="cuip" label="CUIP (Solo Activos)" placeholder="Identificador policial..." />
                            @endif
                        </div>
                    </div>
                    <flux:input wire:model="email" label="Correo Electrónico" placeholder="element@sicoe.gob.mx" />
                    <flux:input wire:model="password" type="password" label="Contraseña" :placeholder="$userId ? 'Dejar en blanco para no cambiar' : 'Mínimo 8 caracteres'" />
                    
                    <flux:select wire:model.live="sexo" label="Sexo" placeholder="Selecciona sexo...">
                        <flux:select.option value="H">Hombre</flux:select.option>
                        <flux:select.option value="M">Mujer</flux:select.option>
                    </flux:select>

                    <flux:select wire:model.live="tipo" label="Tipo de Usuario" placeholder="Selecciona tipo...">
                        <flux:select.option value="alumno">Alumno / Cadete</flux:select.option>
                        <flux:select.option value="docente">Docente / Instructor</flux:select.option>
                        <flux:select.option value="operador">Operador de Grupo</flux:select.option>
                        <flux:select.option value="control">Control Escolar</flux:select.option>
                        <flux:select.option value="admin">Administrador TI</flux:select.option>
                    </flux:select>

                    @if(auth()->user()->hasRole('admin_ti'))
                        <flux:select wire:model.live="nivel" label="Nivel / Adscripción">
                            <flux:select.option value="estatal">Seguridad Estatal</flux:select.option>
                            <flux:select.option value="municipal">Seguridad Municipal</flux:select.option>
                            <flux:select.option value="fiscalia">Fiscalía</flux:select.option>
                            <flux:select.option value="administrativo">Administrativo / UMS</flux:select.option>
                        </flux:select>
                        
                        <flux:select wire:model.live="plantel_id" label="Plantel Asignado" placeholder="Sin plantel específico">
                            <flux:select.option value="">Sin plantel específico</flux:select.option>
                            @foreach($this->planteles as $plantel)
                                <flux:select.option value="{{ $plantel->id }}">{{ $plantel->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif

                    @if($nivel === 'municipal')
                        <flux:select wire:model.live="municipio_id" label="Municipio" placeholder="Selecciona municipio...">
                            <flux:select.option value="">Selecciona municipio</flux:select.option>
                            @foreach($this->municipios as $mun)
                                <flux:select.option value="{{ $mun->id }}">{{ $mun->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif

                    @if($nivel === 'fiscalia')
                        <flux:input wire:model="area_especializada" label="Área Especializada" placeholder="Ej. Homicidios, Robo..." />
                    @endif

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <flux:input wire:model="dependencia" label="Dependencia/Corporación" placeholder="Ej. Policía Estatal, DGSC..." />
                        <flux:input wire:model="adscripcion" label="Unidad de Adscripción" placeholder="Ej. Región I, Comandancia..." />
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <flux:button variant="ghost" x-on:click="open = false">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-8">Guardar Usuario</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
