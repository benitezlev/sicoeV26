<?php

use function Livewire\Volt\{state, usesFileUploads, on, layout};
use App\Models\ConfiguracionInstitucional;
use Illuminate\Support\Facades\Storage;

usesFileUploads();

layout('layouts.app');

state([
    'config' => fn() => ConfiguracionInstitucional::firstOrNew(),
    'logo' => null,
    'mensaje' => '',
]);

$save = function () {
    $this->validate([
        'config.nombre_institucion' => 'required|string|max:255',
        'config.siglas' => 'nullable|string|max:20',
        'config.rfc' => 'nullable|string|max:13',
        'config.domicilio_fiscal' => 'nullable|string|max:500',
        'config.telefono_contacto' => 'nullable|string|max:20',
        'config.correo_contacto' => 'nullable|email|max:255',
        'config.pagina_web' => 'nullable|url|max:255',
        'config.leyenda_documentos' => 'nullable|string|max:1000',
        'logo' => 'nullable|image|max:2048',
    ]);

    if ($this->logo) {
        // Eliminar logo anterior si existe
        if ($this->config->logo_path) {
            Storage::disk('public')->delete('logos/' . $this->config->logo_path);
        }
        
        $path = $this->logo->store('logos', 'public');
        $this->config->logo_path = basename($path);
    }

    $this->config->updated_by = auth()->id();
    $this->config->save();

    $this->mensaje = 'Configuración guardada exitosamente.';
    
    // Auto-limpiar mensaje después de 3 segundos
    $this->dispatch('config-updated');
};

?>

<div class="max-w-4xl">
    <x-slot name="header">Configuración Institucional</x-slot>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-8 shadow-sm">
        <form wire:submit="save" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información Básica -->
                <div class="space-y-6">
                    <flux:heading size="lg">Información General</flux:heading>
                    
                    <flux:field>
                        <flux:label>Nombre de la Institución</flux:label>
                        <flux:input wire:model="config.nombre_institucion" placeholder="Ej. Universidad Mundial de Sonora" />
                        <flux:error name="config.nombre_institucion" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Siglas</flux:label>
                        <flux:input wire:model="config.siglas" placeholder="Ej. UMS" />
                        <flux:error name="config.siglas" />
                    </flux:field>

                    <flux:field>
                        <flux:label>RFC</flux:label>
                        <flux:input wire:model="config.rfc" placeholder="AAXX000000XXX" />
                        <flux:error name="config.rfc" />
                    </flux:field>
                </div>

                <!-- Logo y Contacto -->
                <div class="space-y-6">
                    <flux:heading size="lg">Logo e Identidad</flux:heading>
                    
                    <flux:field>
                        <flux:label>Logo Institucional</flux:label>
                        <div class="flex items-center gap-4">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="size-20 rounded-lg object-cover border border-zinc-200 shadow-sm">
                            @elseif ($config->logo_path)
                                <img src="{{ asset('storage/logos/' . $config->logo_path) }}" class="size-20 rounded-lg object-cover border border-zinc-200 shadow-sm">
                            @else
                                <div class="size-20 rounded-lg bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center border border-dashed border-zinc-300">
                                    <flux:icon name="photo" class="text-zinc-400" />
                                </div>
                            @endif
                            <flux:input type="file" wire:model="logo" />
                        </div>
                        <flux:error name="logo" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Correo de Contacto</flux:label>
                        <flux:input type="email" wire:model="config.correo_contacto" icon="envelope" placeholder="contacto@institucion.edu.mx" />
                        <flux:error name="config.correo_contacto" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Teléfono</flux:label>
                        <flux:input wire:model="config.telefono_contacto" icon="phone" placeholder="662 000 0000" />
                        <flux:error name="config.telefono_contacto" />
                    </flux:field>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <!-- Domicilio y Legal -->
            <div class="space-y-6">
                <flux:heading size="lg">Domicilio y Documentación</flux:heading>
                
                <flux:field>
                    <flux:label>Domicilio Fiscal</flux:label>
                    <flux:textarea wire:model="config.domicilio_fiscal" rows="3" placeholder="Dirección completa..." />
                    <flux:error name="config.domicilio_fiscal" />
                </flux:field>

                <flux:field>
                    <flux:label>Página Web</flux:label>
                    <flux:input wire:model="config.pagina_web" icon="globe-alt" placeholder="https://www.institucion.edu.mx" />
                    <flux:error name="config.pagina_web" />
                </flux:field>

                <flux:field>
                    <flux:label>Leyenda para Documentos</flux:label>
                    <flux:textarea wire:model="config.leyenda_documentos" rows="3" placeholder="Ej. 2026, Año del Centenario..." />
                    <flux:error name="config.leyenda_documentos" />
                </flux:field>
            </div>

            @if ($mensaje)
                <div class="bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-800 p-4 rounded-lg flex items-center gap-3">
                    <flux:icon name="check-circle" class="text-teal-600 dark:text-teal-400" />
                    <span class="text-sm text-teal-800 dark:text-teal-200">{{ $mensaje }}</span>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="check">
                    Guardar Cambios
                </flux:button>
            </div>
        </form>
    </div>
</div>
