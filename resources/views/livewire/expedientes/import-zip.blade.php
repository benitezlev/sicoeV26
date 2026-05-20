<?php

use function Livewire\Volt\{state, layout, usesFileUploads};
use App\Models\User;
use App\Models\Expediente;
use App\Models\DocumentosExpediente;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Flux\Flux;

layout('layouts.app');
usesFileUploads();

state([
    'zipFile'       => null,
    'archivoNombre' => null,
    'listo'         => false,
    'procesando'    => false,
    'procesados'    => [],
    'errores'       => [],
    'finalizado'    => false,
]);

// Hook: Livewire llama esto cuando termina de subir el archivo
$updatedZipFile = function () {
    if ($this->zipFile) {
        $this->archivoNombre = $this->zipFile->getClientOriginalName();
        $this->listo         = true;
        $this->finalizado    = false;
        $this->procesados    = [];
        $this->errores       = [];
        $this->resetErrorBag('zipFile');
    }
};

$importarZip = function () {
    if (!$this->zipFile) {
        $this->addError('zipFile', 'Selecciona un archivo ZIP primero.');
        return;
    }

    // Validar extensión
    $ext = strtolower($this->zipFile->getClientOriginalExtension());
    if ($ext !== 'zip') {
        $this->addError('zipFile', 'El archivo debe tener extensión .zip');
        return;
    }

    if ($this->zipFile->getSize() > 100 * 1024 * 1024) {
        $this->addError('zipFile', 'El archivo no debe superar 100MB.');
        return;
    }

    $this->procesando = true;
    $this->procesados = [];
    $this->errores    = [];

    // Guardar el ZIP en storage temporal
    $storagePath = $this->zipFile->getRealPath();
    $extractPath = storage_path('app/temp_zip_' . uniqid());

    $zip = new \ZipArchive;
    if ($zip->open($storagePath) !== TRUE) {
        $this->addError('zipFile', 'No se pudo abrir el archivo ZIP. Verifica que no esté corrupto.');
        $this->procesando = false;
        return;
    }

    $zip->extractTo($extractPath);
    $zip->close();

    $files = File::allFiles($extractPath);

    if (empty($files)) {
        $this->errores[] = '⚠️ El ZIP está vacío o no contiene archivos reconocibles.';
        File::deleteDirectory($extractPath);
        $this->procesando = false;
        $this->finalizado = true;
        return;
    }

    foreach ($files as $file) {
        $filename = $file->getFilename();

        // Ignorar archivos ocultos del sistema (macOS __MACOSX, thumbs, etc.)
        if (str_starts_with($filename, '.') || str_starts_with($filename, '__')) {
            continue;
        }

        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $parts    = explode('_', $baseName, 2); // Solo el primer _ separa IDENTIFIER del TIPO

        if (count($parts) < 2) {
            $this->errores[] = "❌ [{$filename}] Formato inválido. Usa: CURP_TIPO.ext o CUIP_TIPO.ext";
            continue;
        }

        $identifier = strtoupper(trim($parts[0]));
        $tipo       = strtoupper(trim($parts[1]));

        if (empty($identifier) || empty($tipo)) {
            $this->errores[] = "❌ [{$filename}] Identificador o tipo vacío.";
            continue;
        }

        // ── Detección CURP (18 chars) / CUIP (22 chars) ──
        $user = null;
        $metodo = '';

        if (strlen($identifier) === 18) {
            $user   = User::where('curp', $identifier)->first();
            $metodo = 'CURP';
        } elseif (strlen($identifier) === 22) {
            $user   = User::where('cuip', $identifier)->first();
            $metodo = 'CUIP';
        }

        // Fallback: buscar en ambos campos
        if (!$user) {
            $user   = User::where('curp', $identifier)->orWhere('cuip', $identifier)->first();
            $metodo = 'fallback(CURP|CUIP)';
        }

        if (!$user) {
            $this->errores[] = "❌ [{$filename}] No se encontró usuario con identificador: {$identifier}";
            continue;
        }

        // ── Crear o recuperar expediente ──
        $expediente = Expediente::firstOrCreate(
            ['user_id' => $user->id],
            [
                'folio'         => 'IMP-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'estatus'       => 'incompleto',
                'fecha_apertura'=> now(),
            ]
        );

        // ── Limpiar duplicados del mismo tipo ──
        $existingDocs = DocumentosExpediente::where('expediente_id', $expediente->id)
            ->where('tipo', $tipo)
            ->get();

        foreach ($existingDocs as $oldDoc) {
            $oldDoc->clearMediaCollection('archivo');
            $oldDoc->delete();
        }

        // ── Crear nuevo documento ──
        try {
            $doc = DocumentosExpediente::create([
                'user_id'      => $user->id,
                'expediente_id'=> $expediente->id,
                'tipo'         => $tipo,
                'archivo'      => 'importado_via_zip',
                'fecha_carga'  => now(),
                'cargado_por'  => auth()->id(),
                'estatus'      => 'pendiente',
            ]);

            $doc->addMedia($file->getPathname())
                ->usingFileName($filename)
                ->toMediaCollection('archivo');

            $nombre = trim(($user->nombre ?? '') . ' ' . ($user->paterno ?? '') . ' ' . ($user->materno ?? ''));
            $this->procesados[] = "✅ [{$filename}] → {$nombre} (vía {$metodo})";

            Log::info("ZIP import: documento {$tipo} procesado", [
                'archivo'    => $filename,
                'identifier' => $identifier,
                'metodo'     => $metodo,
                'user_id'    => $user->id,
            ]);
        } catch (\Exception $e) {
            $this->errores[] = "❌ [{$filename}] Error al guardar: " . $e->getMessage();
            Log::error("ZIP import error en {$filename}: " . $e->getMessage());
        }
    }

    // Limpiar directorio temporal
    File::deleteDirectory($extractPath);

    // Limpiar estado del componente
    $this->zipFile       = null;
    $this->archivoNombre = null;
    $this->listo         = false;
    $this->procesando    = false;
    $this->finalizado    = true;

    $total  = count($this->procesados);
    $fallos = count($this->errores);

    if ($total > 0) {
        Flux::toast(
            heading: "Importación Completada",
            text: "{$total} documento(s) importado(s)" . ($fallos > 0 ? ", {$fallos} con errores." : ' correctamente.'),
            variant: $fallos > 0 ? 'warning' : 'success'
        );
    } else {
        Flux::toast(
            heading: 'Sin documentos procesados',
            text: 'Revisa los errores en pantalla.',
            variant: 'danger'
        );
    }
};

$reiniciar = function () {
    $this->reset(['zipFile', 'archivoNombre', 'listo', 'procesando', 'procesados', 'errores', 'finalizado']);
};

?>

<div class="max-w-3xl mx-auto">
    <x-slot name="header">Importación Masiva de Documentos (ZIP)</x-slot>

    <div class="space-y-6">
        {{-- Encabezado --}}
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('expedientes.index') }}" variant="ghost" icon="arrow-left" size="sm" />
            <flux:heading size="xl">Carga Masiva de Expedientes</flux:heading>
        </div>

        @if (!$finalizado)
            {{-- Panel de instrucciones + formulario --}}
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-8">

                {{-- Instrucciones --}}
                <div class="space-y-3">
                    <flux:heading size="lg">¿Cómo funciona?</flux:heading>
                    <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <p>1. Crea un archivo ZIP con los documentos (PDF, JPG, PNG).</p>
                        <p>2. El nombre de cada archivo debe seguir este formato:</p>
                        <div class="pl-2 space-y-1">
                            <p>
                                Documentos personales: <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-0.5 rounded font-bold text-blue-600">CURP_TIPO.ext</code>
                                &nbsp;/&nbsp;
                                <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-0.5 rounded font-bold text-purple-600">CUIP_TIPO.ext</code>
                            </p>
                            <p>
                                Constancias de cursos: <code class="bg-zinc-100 dark:bg-zinc-900 px-2 py-0.5 rounded font-bold text-amber-600">CURP_CONSTANCIA_CODIGOCURSO.ext</code>
                            </p>
                        </div>
                        <p class="text-xs text-zinc-400">
                            El sistema detecta <strong>CURP</strong> (18 chars) y <strong>CUIP</strong> (22 chars) automáticamente.
                            Las constancias requieren el código del curso para no borrar constancias de otros cursos.
                        </p>
                        <p class="text-zinc-500 font-medium">Ejemplos:</p>
                        <ul class="list-disc list-inside space-y-1 text-zinc-500">
                            <li><code>CURP123456HDFXRR01_ACTA.pdf</code> &rarr; Documentación personal</li>
                            <li><code>CUIP1234567890ABCDEF1234_IDENTIFICACION.pdf</code> &rarr; Documentación personal</li>
                            <li><code class="text-amber-600 font-bold">CURP123456HDFXRR01_CONSTANCIA_PFA2024.pdf</code> &rarr; Tab Constancias (curso PFA2024)</li>
                            <li><code class="text-amber-600 font-bold">CURP123456HDFXRR01_CONSTANCIA_LIDERAZGO-ENE25.pdf</code> &rarr; Tab Constancias (otro curso)</li>
                        </ul>
                    </div>

                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800 flex flex-wrap gap-2 items-center">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mr-2">Tipos soportados:</span>
                        <flux:badge size="xs" color="blue">ACTA</flux:badge>
                        <flux:badge size="xs" color="blue">IDENTIFICACION</flux:badge>
                        <flux:badge size="xs" color="blue">CONSTANCIA</flux:badge>
                        <flux:badge size="xs" color="blue">OFICIO</flux:badge>
                    </div>
                </div>

                {{-- Zona de upload --}}
                <div
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="space-y-3"
                >
                    <flux:field>
                        <flux:label>Seleccionar Archivo ZIP (máx. 100 MB)</flux:label>
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
                        @error('zipFile')
                            <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                <flux:icon name="exclamation-circle" class="w-4 h-4 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </flux:field>

                    {{-- Progreso de subida a Livewire --}}
                    <div x-show="uploading" x-transition class="space-y-1">
                        <p class="text-xs text-zinc-500">Subiendo archivo al servidor...</p>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-600 h-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    {{-- Confirmación de archivo listo --}}
                    @if ($listo && $archivoNombre)
                        <div class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="w-5 h-5 shrink-0" />
                            <span>Listo para procesar: <strong>{{ $archivoNombre }}</strong></span>
                        </div>
                    @endif
                </div>

                {{-- Botón procesar --}}
                <div class="flex justify-end">
                    <flux:button
                        wire:click="importarZip"
                        variant="primary"
                        icon="arrow-up-tray"
                        wire:loading.attr="disabled"
                        wire:target="zipFile, importarZip"
                        :disabled="!$listo"
                    >
                        <span wire:loading.remove wire:target="importarZip">Procesar ZIP</span>
                        <span wire:loading wire:target="importarZip" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Procesando documentos...
                        </span>
                    </flux:button>
                </div>
            </div>
        @else
            {{-- Resultados --}}
            <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">

                {{-- Resumen --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 p-5 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center">
                            <flux:icon name="check-circle" class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-xs text-emerald-600 uppercase tracking-wider font-bold">Procesados</p>
                            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400">{{ count($procesados) }}</p>
                        </div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 p-5 rounded-2xl border border-red-200 dark:border-red-800 flex items-center gap-4">
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                            <flux:icon name="exclamation-triangle" class="w-6 h-6 text-red-600" />
                        </div>
                        <div>
                            <p class="text-xs text-red-600 uppercase tracking-wider font-bold">Errores</p>
                            <p class="text-3xl font-black text-red-700 dark:text-red-400">{{ count($errores) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Lista de resultados --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if(count($procesados) > 0)
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-emerald-100 dark:border-emerald-900/30 overflow-hidden">
                            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-900/30">
                                <flux:heading size="sm" class="text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                                    Documentos Importados ({{ count($procesados) }})
                                </flux:heading>
                            </div>
                            <div class="p-4 max-h-80 overflow-y-auto space-y-1">
                                @foreach($procesados as $msg)
                                    <div class="text-xs text-emerald-700 dark:text-emerald-400 font-mono p-2 bg-emerald-50/50 dark:bg-emerald-900/10 rounded border border-emerald-50 dark:border-emerald-900/20">
                                        {{ $msg }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($errores) > 0)
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-red-100 dark:border-red-900/30 overflow-hidden">
                            <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/30">
                                <flux:heading size="sm" class="text-red-700 dark:text-red-400 uppercase tracking-wider">
                                    Errores ({{ count($errores) }})
                                </flux:heading>
                            </div>
                            <div class="p-4 max-h-80 overflow-y-auto space-y-1">
                                @foreach($errores as $msg)
                                    <div class="text-xs text-red-700 dark:text-red-400 font-mono p-2 bg-red-50/50 dark:bg-red-900/10 rounded border border-red-50 dark:border-red-900/20">
                                        {{ $msg }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Acción --}}
                <div class="flex justify-end">
                    <flux:button wire:click="reiniciar" variant="primary" icon="arrow-path">
                        Nueva Importación
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</div>
