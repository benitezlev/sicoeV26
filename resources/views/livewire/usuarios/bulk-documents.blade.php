<?php

use function Livewire\Volt\{state, layout, usesFileUploads};
use App\Models\User;
use App\Models\DocumentosExpediente;
use App\Models\Expediente;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();
layout('layouts.app');

state([
    'archivos' => [],
    'procesados' => [],
    'errores' => [],
    'is_loading' => false
]);

$procesar = function() {
    $this->validate([
        'archivos.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $this->procesados = [];
    $this->errores = [];
    $this->is_loading = true;

    foreach ($this->archivos as $file) {
        $filename = $file->getClientOriginalName();
        // Patron esperado: CURP_TIPO.ext (ej: PEVR800101HDFRRN01_ACTA.pdf)
        $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));

        if (count($parts) < 2) {
            $this->errores[] = "[$filename] Formato de nombre inválido. Use CURP_TIPO.ext";
            continue;
        }

        $curp = strtoupper($parts[0]);
        $tipo = strtoupper($parts[1]);

        $user = User::where('curp', $curp)->first();

        if (!$user) {
            $this->errores[] = "[$filename] No se encontró usuario con CURP: $curp";
            continue;
        }

        if (!$user->expediente) {
            // Crear expediente si no existe (aunque ya deberían existir por automatización previa)
            $user->expediente()->create([
                'folio' => ($user->nivel ? strtoupper(substr($user->nivel, 0, 3)) : 'GEN') . '-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'estatus' => 'incompleto'
            ]);
            $user->refresh();
        }

        $path = $file->storeAs('expedientes/' . $user->id, $filename, 'public');

        DocumentosExpediente::updateOrCreate(
            [
                'expediente_id' => $user->expediente->id,
                'tipo' => $tipo
            ],
            [
                'nombre' => $filename,
                'ruta' => $path,
                'estatus' => 'pendiente', // Sube como pendiente para revisión
                'cargado_por' => auth()->id(),
                'fecha_carga' => now()
            ]
        );

        $this->procesados[] = "[$filename] ✅ Vinculado correctamente a {$user->nombre_completo}";
    }

    $this->archivos = [];
    $this->is_loading = false;
    
    if (count($this->procesados) > 0) {
        Flux::toast(heading: 'Proceso completado', variant: 'success');
    }
};

?>

<div class="space-y-6">
    <x-slot name="header">Carga Masiva de Expedientes</x-slot>

    <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-xl">
        <div class="max-w-2xl mx-auto text-center space-y-4">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-sm leading-relaxed">
                <flux:icon name="information-circle" variant="mini" class="inline mr-1" />
                <strong>Instrucciones:</strong> El nombre de los archivos debe seguir el formato <code>CURP_TIPO.pdf</code> <br>
                Ejemplos: <code>PEVR800101HDFRRN01_ACTA.pdf</code>, <code>ABCD900101HDFRRN05_IDENTIFICACION.jpg</code>
            </div>

            <flux:field>
                <div 
                    x-data="{ isDragging: false }"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false"
                    :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-zinc-300 bg-zinc-50'"
                    class="relative border-2 border-dashed rounded-2xl p-12 transition-all cursor-pointer hover:border-zinc-400 group"
                >
                    <input 
                        type="file" 
                        wire:model="archivos" 
                        multiple 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    >
                    
                    <div class="space-y-4">
                        <div class="mx-auto w-16 h-16 bg-white dark:bg-zinc-700 rounded-full flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-600 group-hover:scale-110 transition-transform">
                            <flux:icon name="cloud-arrow-up" class="w-8 h-8 text-zinc-400" />
                        </div>
                        <div class="text-zinc-600">
                            <span class="font-bold text-zinc-900 dark:text-white">Haga clic para subir</span> o arrastre y suelte
                            <p class="text-xs text-zinc-400 mt-1">PDF, JPG, PNG (Máx. 5MB por archivo)</p>
                        </div>
                    </div>
                </div>
                <flux:error name="archivos.*" />
            </flux:field>

            @if(count($archivos) > 0)
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Total de archivos seleccionados: <b>{{ count($archivos) }}</b>
                    </span>
                    <flux:button variant="primary" icon="play" wire:click="procesar" wire:loading.attr="disabled">
                        <span wire:loading.remove>Comenzar Procesamiento</span>
                        <span wire:loading>Procesando...</span>
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    @if(count($procesados) > 0 || count($errores) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-emerald-100 dark:border-emerald-900/30 overflow-hidden">
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-900/30">
                    <flux:heading size="sm" class="text-emerald-700 uppercase tracking-wider">Procesados Exitosamente ({{ count($procesados) }})</flux:heading>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto space-y-2">
                    @foreach($procesados as $msg)
                        <div class="text-xs text-emerald-600 font-mono p-2 bg-emerald-50/50 dark:bg-emerald-900/10 rounded border border-emerald-50 dark:border-emerald-900/20">{{ $msg }}</div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-red-100 dark:border-red-900/30 overflow-hidden">
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/30">
                    <flux:heading size="sm" class="text-red-700 uppercase tracking-wider">Errores o Advertencias ({{ count($errores) }})</flux:heading>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto space-y-2">
                    @forelse($errores as $msg)
                        <div class="text-xs text-red-600 font-mono p-2 bg-red-50/50 dark:bg-red-900/10 rounded border border-red-50 dark:border-red-900/20">{{ $msg }}</div>
                    @empty
                        <p class="text-sm text-zinc-400 italic text-center py-4">Sin errores reportados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
