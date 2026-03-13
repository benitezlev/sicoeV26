<?php

use function Livewire\Volt\{state, layout, rules};
use Livewire\WithFileUploads;
use App\Jobs\ProcessZipImport;
use Flux\Flux;

layout('layouts.app');

state([
    'zipFile' => null,
    'uploading' => false,
]);

$importarZip = function () {
    $this->validate([
        'zipFile' => 'required|mimes:zip|max:102400', // 100MB max
    ]);

    $path = $this->zipFile->store('temp_imports');
    
    // Despachar Job a la cola
    ProcessZipImport::dispatch($path, auth()->id());

    $this->zipFile = null;
    
    Flux::toast(
        heading: 'Importación en Cola',
        text: 'El archivo ZIP se está procesando en segundo plano. Los documentos aparecerán en los expedientes en unos minutos.',
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
            <div class="space-y-4">
                <flux:heading size="lg">¿Cómo funciona?</flux:heading>
                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <p>1. Crea un archivo ZIP con los documentos (PDF, JPG, PNG).</p>
                    <p>2. El nombre de cada archivo dentro del ZIP debe seguir este formato: <br>
                       <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-1 rounded font-bold text-blue-600">CURP_TIPO.extension</code>
                    </p>
                    <p>Ejemplo: <code class="text-zinc-500">CURP123456HDFXRR01_ACTA.pdf</code></p>
                    
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

            <form wire:submit="importarZip" class="space-y-6">
                <flux:field>
                    <flux:label>Seleccionar Archivo ZIP</flux:label>
                    <flux:input type="file" wire:model="zipFile" accept=".zip" />
                    <flux:error name="zipFile" />
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button type="submit" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled">
                        <span wire:loading.remove>Subir y Procesar Masivamente</span>
                        <span wire:loading>Subiendo archivo...</span>
                    </flux:button>
                </div>
            </form>
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
