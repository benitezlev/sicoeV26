<?php

use function Livewire\Volt\{state, usesFileUploads, on, layout, mount};
use App\Models\ConfiguracionInstitucional;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();

layout('layouts.app');

state([
    'nombre_institucion' => '',
    'siglas' => '',
    'siglas_departamento' => '',
    'titular_ums' => '',
    'puesto_titular' => '',
    'rfc' => '',
    'domicilio_fiscal' => '',
    'telefono_contacto' => '',
    'correo_contacto' => '',
    'pagina_web' => '',
    'aviso_privacidad_url' => '',
    'leyenda_documentos' => '',
    'objetivo_institucional' => '',
    'logo_path' => '',
    'pleca_recurso_1' => '',
    'pleca_recurso_2' => '',
    'logo' => null,
    'recurso_1' => null,
    'recurso_2' => null,
]);

mount(function () {
    $config = ConfiguracionInstitucional::first();
    if ($config) {
        $this->nombre_institucion = $config->nombre_institucion;
        $this->siglas = $config->siglas;
        $this->siglas_departamento = $config->siglas_departamento;
        $this->titular_ums = $config->titular_ums;
        $this->puesto_titular = $config->puesto_titular;
        $this->rfc = $config->rfc;
        $this->domicilio_fiscal = $config->domicilio_fiscal;
        $this->telefono_contacto = $config->telefono_contacto;
        $this->correo_contacto = $config->correo_contacto;
        $this->pagina_web = $config->pagina_web;
        $this->aviso_privacidad_url = $config->aviso_privacidad_url;
        $this->leyenda_documentos = $config->leyenda_documentos;
        $this->objetivo_institucional = $config->objetivo_institucional;
        $this->logo_path = $config->logo_path;
        $this->pleca_recurso_1 = $config->pleca_recurso_1;
        $this->pleca_recurso_2 = $config->pleca_recurso_2;
    }
});

$save = function () {
    $this->validate([
        'nombre_institucion' => 'required|string|max:255',
        'siglas' => 'nullable|string|max:20',
        'siglas_departamento' => 'nullable|string|max:50',
        'titular_ums' => 'nullable|string|max:255',
        'puesto_titular' => 'nullable|string|max:255',
        'rfc' => 'nullable|string|max:13',
        'domicilio_fiscal' => 'nullable|string|max:500',
        'telefono_contacto' => 'nullable|string|max:20',
        'correo_contacto' => 'nullable|email|max:255',
        'pagina_web' => 'nullable|url|max:255',
        'aviso_privacidad_url' => 'nullable|url|max:255',
        'leyenda_documentos' => 'nullable|string|max:1000',
        'objetivo_institucional' => 'nullable|string|max:2000',
        'logo' => 'nullable|image|max:2048',
        'recurso_1' => 'nullable|image|max:3072',
        'recurso_2' => 'nullable|image|max:3072',
    ]);

    $config = ConfiguracionInstitucional::firstOrNew();

    if ($this->logo) {
        if ($config->logo_path) {
            Storage::disk('public')->delete('logos/' . $config->logo_path);
        }
        $path = $this->logo->store('logos', 'public');
        $this->logo_path = basename($path);
    }

    if ($this->recurso_1) {
        if ($config->pleca_recurso_1) {
            Storage::disk('public')->delete('plecas/' . $config->pleca_recurso_1);
        }
        $path = $this->recurso_1->store('plecas', 'public');
        $this->pleca_recurso_1 = basename($path);
    }

    if ($this->recurso_2) {
        if ($config->pleca_recurso_2) {
            Storage::disk('public')->delete('plecas/' . $config->pleca_recurso_2);
        }
        $path = $this->recurso_2->store('plecas', 'public');
        $this->pleca_recurso_2 = basename($path);
    }

    $config->fill([
        'nombre_institucion' => $this->nombre_institucion,
        'siglas' => $this->siglas,
        'siglas_departamento' => $this->siglas_departamento,
        'titular_ums' => $this->titular_ums,
        'puesto_titular' => $this->puesto_titular,
        'rfc' => $this->rfc,
        'domicilio_fiscal' => $this->domicilio_fiscal,
        'telefono_contacto' => $this->telefono_contacto,
        'correo_contacto' => $this->correo_contacto,
        'pagina_web' => $this->pagina_web,
        'aviso_privacidad_url' => $this->aviso_privacidad_url,
        'leyenda_documentos' => $this->leyenda_documentos,
        'objetivo_institucional' => $this->objetivo_institucional,
        'logo_path' => $this->logo_path,
        'pleca_recurso_1' => $this->pleca_recurso_1,
        'pleca_recurso_2' => $this->pleca_recurso_2,
        'updated_by' => auth()->id(),
    ]);

    $config->save();

    Flux::toast(
        heading: 'Configuración actualizada',
        text: 'Los recursos institucionales han sido guardados.',
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
                <!-- LOGO SECTION -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col items-center text-center">
                    <flux:heading size="lg" class="mb-6 w-full text-left">Logo UMS</flux:heading>
                    
                    <div class="relative group cursor-pointer">
                        <div class="size-48 rounded-3xl bg-zinc-50 dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-500/50">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain p-4">
                            @elseif ($logo_path && Storage::disk('public')->exists('logos/' . $logo_path))
                                <img src="{{ asset('storage/logos/' . $logo_path) }}" class="w-full h-full object-contain p-4">
                            @else
                                <img src="{{ asset('img/Logo-UMS-1.png') }}" class="w-full h-full object-contain p-4 opacity-80">
                            @endif
                        </div>
                        <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                    
                    <p class="mt-4 text-[10px] text-zinc-400 font-medium">Click para cambiar logo</p>
                    <flux:error name="logo" class="mt-2" />
                </div>

                <!-- PLECA SECTIONS (RECURSO 1 & 2) -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-8">
                    <div>
                        <flux:heading size="lg" class="mb-4">Pleca - Recurso 1</flux:heading>
                        <div class="relative group cursor-pointer w-full">
                            <div class="h-20 w-full rounded-xl bg-zinc-50 dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-500/50">
                                @if ($recurso_1)
                                    <img src="{{ $recurso_1->temporaryUrl() }}" class="w-full h-full object-contain">
                                @elseif ($pleca_recurso_1 && Storage::disk('public')->exists('plecas/' . $pleca_recurso_1))
                                    <img src="{{ asset('storage/plecas/' . $pleca_recurso_1) }}" class="w-full h-full object-contain">
                                @else
                                    <div class="text-[9px] text-zinc-400 italic font-mono">SIN RECURSO 1</div>
                                @endif
                            </div>
                            <input type="file" wire:model="recurso_1" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <p class="mt-2 text-[9px] text-zinc-400">Pleca decorativa superior izquierda (Opcional)</p>
                        <flux:error name="recurso_1" class="mt-1" />
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-700/50">
                        <flux:heading size="lg" class="mb-4">Pleca - Recurso 2</flux:heading>
                        <div class="relative group cursor-pointer w-full">
                            <div class="h-20 w-full rounded-xl bg-zinc-50 dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-500/50">
                                @if ($recurso_2)
                                    <img src="{{ $recurso_2->temporaryUrl() }}" class="w-full h-full object-contain">
                                @elseif ($pleca_recurso_2 && Storage::disk('public')->exists('plecas/' . $pleca_recurso_2))
                                    <img src="{{ asset('storage/plecas/' . $pleca_recurso_2) }}" class="w-full h-full object-contain">
                                @else
                                    <div class="text-[9px] text-zinc-400 italic font-mono">SIN RECURSO 2</div>
                                @endif
                            </div>
                            <input type="file" wire:model="recurso_2" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <p class="mt-2 text-[9px] text-zinc-400">Pleca decorativa superior derecha (Opcional)</p>
                        <flux:error name="recurso_2" class="mt-1" />
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
                                <flux:input wire:model="nombre_institucion" placeholder="Universidad Mundial de Sonora" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Titular / Director General</flux:label>
                            <flux:input wire:model="titular_ums" icon="user" placeholder="Nombre completo" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Puesto del Titular</flux:label>
                            <flux:input wire:model="puesto_titular" placeholder="Ej. Director General" />
                        </flux:field>

                        <flux:field>
                            <flux:label>RFC Laboral</flux:label>
                            <flux:input wire:model="rfc" placeholder="UMS000000XXX" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Siglas de la Institución</flux:label>
                            <flux:input wire:model="siglas" placeholder="Ej. UMS" />
                        </flux:field>
                    </div>
                </div>

                <!-- Bloque de Contacto -->
                <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-6">
                    <flux:heading size="lg" icon="phone">Contacto y Ubicación</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <flux:field>
                            <flux:label>Correo Electrónico Institucional</flux:label>
                            <flux:input type="email" wire:model="correo_contacto" icon="envelope" placeholder="admision@ums.edu.mx" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Teléfono de Atención</flux:label>
                            <flux:input wire:model="telefono_contacto" icon="phone" placeholder="662 000 0000" />
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>Domicilio Físico / Fiscal</flux:label>
                                <flux:textarea wire:model="domicilio_fiscal" rows="2" placeholder="Calle, Número, Colonia, CP..." />
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
                            <flux:textarea wire:model="leyenda_documentos" rows="2" placeholder="Ej. 2026, Año de la Transformación Académica." />
                        </flux:field>

                        <flux:field>
                            <flux:label>Objetivo / Misión (Para formatos oficiales)</flux:label>
                            <flux:textarea wire:model="objetivo_institucional" rows="3" placeholder="Define la misión de la UMS para reportes académicos..." />
                        </flux:field>

                        <flux:field>
                            <flux:label>URL Aviso de Privacidad</flux:label>
                            <flux:input wire:model="aviso_privacidad_url" icon="link" placeholder="https://www.ums.edu.mx/privacidad" />
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
