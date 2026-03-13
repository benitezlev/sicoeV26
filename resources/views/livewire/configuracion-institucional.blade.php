<?php

use function Livewire\Volt\{state, usesFileUploads, on, layout};
use App\Models\ConfiguracionInstitucional;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

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
        'config.titular_ums' => 'nullable|string|max:255',
        'config.puesto_titular' => 'nullable|string|max:255',
        'config.rfc' => 'nullable|string|max:13',
        'config.domicilio_fiscal' => 'nullable|string|max:500',
        'config.telefono_contacto' => 'nullable|string|max:20',
        'config.correo_contacto' => 'nullable|email|max:255',
        'config.pagina_web' => 'nullable|url|max:255',
        'config.aviso_privacidad_url' => 'nullable|url|max:255',
        'config.leyenda_documentos' => 'nullable|string|max:1000',
        'config.objetivo_institucional' => 'nullable|string|max:2000',
        'logo' => 'nullable|image|max:2048',
    ]);

    if ($this->logo) {
        if ($this->config->logo_path) {
            Storage::disk('public')->delete('logos/' . $this->config->logo_path);
        }
        
        $path = $this->logo->store('logos', 'public');
        $this->config->logo_path = basename($path);
    }

    $this->config->updated_by = auth()->id();
    $this->config->save();

    Flux::toast(
        heading: 'Configuración actualizada',
        text: 'Los datos institucionales de la UMS han sido guardados.',
        variant: 'success',
    );
};

?>

<div class="max-w-5xl mx-auto space-y-8 p-6">
    <x-slot name="header">Configuración Institucional</x-slot>

    <div class="flex items-center gap-4 mb-8">
        <div class="p-3 bg-blue-500/10 rounded-2xl border border-blue-500/20">
            <flux:icon name="building-library" class="text-blue-600 dark:text-blue-400" />
        </div>
        <div>
            <h1 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Identidad Institucional</h1>
            <p class="text-xs text-zinc-500 font-medium italic">Personaliza la información que aparecerá en reportes y documentos oficiales.</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna de Identidad Visual -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col items-center text-center">
                    <flux:heading size="lg" class="mb-6 w-full text-left">Logo UMS</flux:heading>
                    
                    <div class="relative group cursor-pointer">
                        <div class="size-48 rounded-3xl bg-zinc-50 dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-500/50">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain p-4">
                            @elseif ($config->logo_path && Storage::disk('public')->exists('logos/' . $config->logo_path))
                                <img src="{{ asset('storage/logos/' . $config->logo_path) }}" class="w-full h-full object-contain p-4">
                            @else
                                <img src="{{ asset('img/Logo-UMS-1.png') }}" class="w-full h-full object-contain p-4 opacity-80">
                            @endif
                        </div>
                        <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                    
                    <p class="mt-4 text-[10px] text-zinc-400 font-medium">Click para subir logo (PNG/JPG, Máx 2MB)</p>
                    <flux:error name="logo" class="mt-2" />

                    <div class="mt-8 w-full space-y-4 text-left">
                        <flux:field>
                            <flux:label>Siglas de la Institución</flux:label>
                            <flux:input wire:model="config.siglas" placeholder="Ej. UMS" />
                        </flux:field>
                    </div>
                </div>

                <div class="bg-zinc-900 dark:bg-zinc-100 p-6 rounded-3xl text-white dark:text-zinc-800 shadow-xl border border-zinc-800 dark:border-zinc-200">
                    <h4 class="text-sm font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <flux:icon name="shield-check" variant="mini" class="text-blue-400 dark:text-blue-600" />
                        Certificación
                    </h4>
                    <p class="text-[10px] opacity-70 leading-relaxed font-medium">
                        La información aquí registrada es utilizada para el estampado de logotipos y leyendas de validez oficial en la documentación emitida por el SICOE.
                    </p>
                </div>
            </div>

            <!-- Columna de Datos Generales -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Bloque General -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                    <flux:heading size="lg" icon="document-text">Información Legal y Titularidad</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>Nombre Oficial de la Institución</flux:label>
                                <flux:input wire:model="config.nombre_institucion" placeholder="Universidad Mundial de Sonora" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Titular / Director General</flux:label>
                            <flux:input wire:model="config.titular_ums" icon="user" placeholder="Nombre completo" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Puesto del Titular</flux:label>
                            <flux:input wire:model="config.puesto_titular" placeholder="Ej. Director General" />
                        </flux:field>

                        <flux:field>
                            <flux:label>RFC Laboral</flux:label>
                            <flux:input wire:model="config.rfc" placeholder="UMS000000XXX" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Página Web</flux:label>
                            <flux:input wire:model="config.pagina_web" icon="globe-alt" placeholder="www.ums.edu.mx" />
                        </flux:field>
                    </div>
                </div>

                <!-- Bloque de Contacto -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                    <flux:heading size="lg" icon="phone">Contacto y Ubicación</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <flux:field>
                            <flux:label>Correo Electrónico Institucional</flux:label>
                            <flux:input type="email" wire:model="config.correo_contacto" icon="envelope" placeholder="admision@ums.edu.mx" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Teléfono de Atención</flux:label>
                            <flux:input wire:model="config.telefono_contacto" icon="phone" placeholder="662 000 0000" />
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>Domicilio Físico / Fiscal</flux:label>
                                <flux:textarea wire:model="config.domicilio_fiscal" rows="2" placeholder="Calle, Número, Colonia, CP..." />
                            </flux:field>
                        </div>
                    </div>
                </div>

                <!-- Bloque de Documentación Oficial -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                    <flux:heading size="lg" icon="paint-brush">Personalización de Documentos</flux:heading>
                    
                    <div class="space-y-6 mt-4">
                        <flux:field>
                            <flux:label>Leyenda Oficial del Año (Pie de Página)</flux:label>
                            <flux:textarea wire:model="config.leyenda_documentos" rows="2" placeholder="Ej. 2026, Año de la Transformación Académica." />
                        </flux:field>

                        <flux:field>
                            <flux:label>Objetivo / Misión (Para formatos oficiales)</flux:label>
                            <flux:textarea wire:model="config.objetivo_institucional" rows="3" placeholder="Define la misión de la UMS para reportes académicos..." />
                        </flux:field>

                        <flux:field>
                            <flux:label>URL Aviso de Privacidad</flux:label>
                            <flux:input wire:model="config.aviso_privacidad_url" icon="link" placeholder="https://www.ums.edu.mx/privacidad" />
                        </flux:field>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <flux:button type="submit" variant="primary" class="px-12 py-3 rounded-2xl font-black uppercase tracking-widest text-xs">
                Actualizar Datos UMS
            </flux:button>
        </div>
    </form>
</div>
