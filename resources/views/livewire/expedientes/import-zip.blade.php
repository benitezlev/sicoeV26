<?php

use function Livewire\Volt\{state, layout, usesFileUploads};
use App\Jobs\ProcessZipImport;
use Illuminate\Support\Facades\Log;
use Flux\Flux;

layout('layouts.app');
usesFileUploads();

state([
    'zipFile'       => null,
    'archivoNombre' => null,   // Nombre visual del archivo seleccionado
    'listo'         => false,  // Si el archivo fue subido correctamente
]);

// Livewire llama esto automáticamente cuando el archivo termina de subirse
$updatedZipFile = function () {
    // Al actualizar el archivo, registramos el nombre y marcamos como listo
    if ($this->zipFile) {
        $this->archivoNombre = $this->zipFile->getClientOriginalName();
        $this->listo = true;
        $this->resetErrorBag('zipFile');
    }
};

$importarZip = function () {
    if (!$this->zipFile) {
        $this->addError('zipFile', 'Selecciona un archivo ZIP primero.');
        return;
    }

    try {
        // Validación flexible: acepta cualquier extensión .zip independiente del MIME
        $this->validate([
            'zipFile' => [
                'required',
                'file',
                'max:102400', // 100MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'zip') {
                        $fail('El archivo debe tener extensión .zip');
                    }
                },
            ],
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Validación ZIP fallida: ' . json_encode($e->errors()));
        throw $e;
    }

    $path = $this->zipFile->store('temp_imports');

    Log::info('ZIP cargado para importación masiva', [
        'path'     => $path,
        'archivo'  => $this->archivoNombre,
        'user_id'  => auth()->id(),
    ]);

    // Despachar Job a la cola
    ProcessZipImport::dispatch($path, auth()->id());

    // Limpiar estado
    $this->zipFile       = null;
    $this->archivoNombre = null;
    $this->listo         = false;

    Flux::toast(
        heading: 'Importación en Cola',
        text: 'El archivo ZIP se está procesando. Los documentos aparecerán en los expedientes en unos minutos.',
        variant: 'success'
    );
};

?>

<div class="max-w-2xl mx-auto">
    <x-slot name="header">Importación Masiva de Documentos (ZIP)</x-slot>

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('expedientes.index') }}" variant="ghost" icon="arrow-left" size="sm" />
            <flux:heading size="xl">Carga Masiva de Expedientes</flux:heading>
        </div>

        <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-8">
            {{-- Instrucciones --}}
            <div class="space-y-4">
                <flux:heading size="lg">¿Cómo funciona?</flux:heading>
                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <p>1. Crea un archivo ZIP con los documentos (PDF, JPG, PNG).</p>
                    <p>2. El nombre de cada archivo debe seguir este formato: <br>
                       <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-1 rounded font-bold text-blue-600">CURP_TIPO.extension</code>
                       &nbsp;ó&nbsp;
                       <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-1 rounded font-bold text-purple-600">CUIP_TIPO.extension</code>
                    </p>
                    <p class="text-xs text-zinc-500">
                        El sistema detecta automáticamente si el identificador es <strong>CURP</strong> (18 caracteres) o <strong>CUIP</strong> (22 caracteres).
                    </p>
                    <ul class="list-disc list-inside space-y-1 mt-1">
                        <li><code class="text-zinc-500">CURP123456HDFXRR01_ACTA.pdf</code> &rarr; vincula por CURP</li>
                        <li><code class="text-zinc-500">CUIP1234567890ABCDEF1234_IDENTIFICACION.pdf</code> &rarr; vincula por CUIP</li>
                    </ul>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
                        <flux:heading size="sm" class="text-blue-700 dark:text-blue-400 mb-2">Tipos de documentos soportados:</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            <flux:badge size="xs" color="blue">ACTA</flux:badge>
                            <flux:badge size="xs" color="blue">IDENTIFICACION</flux:badge>
                            <flux:badge size="xs" color="blue">CONSTANCIA</flux:badge>
                            <flux:badge size="xs" color="blue">OFICIO</flux:badge>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="space-y-6">
                {{-- Zona de selección de archivo con Alpine para progreso --}}
                <div
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="space-y-3"
                >
                    <flux:field>
                        <flux:label>Seleccionar Archivo ZIP</flux:label>

                        {{-- Input HTML nativo — wire:model en file input requiere input nativo, no flux:input --}}
                        <input
                            type="file"
                            wire:model="zipFile"
                            accept=".zip"
                            class="block w-full text-sm text-zinc-500
                                   file:mr-4 file:py-2 file:px-4
                                   file:rounded-xl file:border-0
                                   file:text-xs file:font-bold file:uppercase file:tracking-widest
                                   file:bg-blue-50 file:text-blue-700
                                   hover:file:bg-blue-100
                                   dark:file:bg-zinc-900 dark:file:text-zinc-400
                                   border border-zinc-200 dark:border-zinc-700
                                   rounded-xl p-2 bg-white dark:bg-zinc-900 shadow-sm"
                        />

                        {{-- Error visible --}}
                        @error('zipFile')
                            <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                <flux:icon name="exclamation-circle" class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror
                    </flux:field>

                    {{-- Barra de progreso durante la carga del archivo a Livewire --}}
                    <div x-show="uploading" x-transition class="space-y-1">
                        <p class="text-xs text-zinc-500">Subiendo archivo...</p>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-600 h-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    {{-- Confirmación visual una vez subido el archivo --}}
                    @if ($listo && $archivoNombre)
                        <div class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="w-5 h-5 shrink-0" />
                            <span>Archivo listo: <strong>{{ $archivoNombre }}</strong></span>
                        </div>
                    @endif
                </div>

                {{-- Botón de procesar --}}
                <div class="flex justify-end gap-2">
                    <flux:button
                        wire:click="importarZip"
                        variant="primary"
                        icon="arrow-up-tray"
                        wire:loading.attr="disabled"
                        wire:target="zipFile, importarZip"
                        :disabled="!$listo"
                    >
                        <span wire:loading.remove wire:target="importarZip">Procesar Masivamente</span>
                        <span wire:loading wire:target="importarZip">Procesando...</span>
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="p-4 bg-zinc-900 rounded-2xl flex items-center gap-4 text-white">
            <flux:icon name="information-circle" class="w-8 h-8 text-blue-400" />
            <div class="text-xs">
                <span class="font-bold block">Procesamiento Asíncrono</span>
                Debido a que el procesamiento de archivos pesados consume recursos, el sistema utiliza <b>Queues (Colas)</b> para no bloquear tu navegación mientras se extraen y validan los documentos.
            </div>
        </div>
    </div>
</div>
