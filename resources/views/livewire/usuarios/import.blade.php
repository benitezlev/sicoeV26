<?php

use function Livewire\Volt\{state, layout, rules};
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Expediente;
use App\Models\Importacion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

layout('layouts.app');

state([
    'archivo' => null,
    'duplicados' => [],
    'errores' => [],
    'insertados' => 0,
    'importacionFinalizada' => false,
]);

$importar = function () {
    $this->validate([
        'archivo' => 'required|file|mimes:csv,xlsx|max:10240',
    ]);

    $nombreOriginal = $this->archivo->getClientOriginalName();
    $path = $this->archivo->getRealPath();

    $csv = fopen($path, 'r');
    stream_filter_append($csv, 'convert.iconv.ISO-8859-1/UTF-8');

    $encabezados = fgetcsv($csv);
    $requeridos = ['curp', 'nombre', 'sexo'];

    foreach ($requeridos as $campo) {
        if (!in_array($campo, $encabezados)) {
            fclose($csv);
            $this->addError('archivo', "El archivo no contiene el campo obligatorio: $campo");
            return;
        }
    }

    $this->duplicados = [];
    $this->errores = [];
    $this->insertados = 0;

    while (($fila = fgetcsv($csv)) !== false) {
        if (count($encabezados) !== count($fila)) {
            $this->errores[] = "Fila con columnas incompletas.";
            continue;
        }

        $datos = array_combine($encabezados, $fila);
        $curp = strtoupper(trim($datos['curp'] ?? ''));
        $nombre = trim($datos['nombre'] ?? '');

        if (!$curp) {
            $this->errores[] = "El alumno '{$nombre}' no tiene CURP definido.";
            continue;
        }

        // Validación de duplicados por CURP
        if (User::where('curp', $curp)->exists()) {
            $this->duplicados[] = [
                'nombre' => $nombre . ' ' . ($datos['paterno'] ?? ''),
                'curp' => $curp,
                'motivo' => 'CURP ya registrado en el sistema'
            ];
            continue;
        }

        $sexoRaw = strtoupper(trim($datos['sexo'] ?? ''));
        $sexo = match ($sexoRaw) {
            'HOMBRE', 'H', 'MASCULINO' => 'H',
            'MUJER', 'M', 'FEMENINO' => 'M',
            default => null,
        };

        if (!$sexo) {
            $this->errores[] = "CURP $curp ({$nombre}) tiene sexo inválido o ausente: '$sexoRaw'";
            continue;
        }

        // Configuración de Nivel y Datos Flexibles
        $nivel = strtolower(trim($datos['nivel'] ?? 'estatal'));
        if (!in_array($nivel, ['estatal', 'municipal', 'fiscalia', 'administrativo'])) {
            $nivel = 'estatal';
        }

        $email = $datos['email'] ?? strtolower($curp) . '@sicoe.mx';
        $password = $datos['password'] ?? $curp;

        try {
            $user = User::create([
                'nombre' => $nombre,
                'paterno' => $datos['paterno'] ?? '',
                'materno' => $datos['materno'] ?? '',
                'username' => $datos['username'] ?? $curp,
                'email' => $email,
                'password' => Hash::make($password),
                'curp' => $curp,
                'cuip' => $datos['cuip'] ?? null,
                'cup' => $datos['cup'] ?? null,
                'sexo' => $sexo,
                'tipo' => strtolower($datos['tipo'] ?? 'alumno') === 'alumno' ? 'alumno' : ($datos['tipo'] ?? 'alumno'),
                'nivel' => $nivel,
                'plantel_id' => null, // Asignación dinámica posterior
                'perfil_data' => [
                    'perfil' => $datos['perfil'] ?? null,
                    'municipio_id' => $datos['municipio_id'] ?? null,
                    'dependencia' => $datos['dependencia'] ?? null,
                    'adscripcion' => $datos['adscripcion'] ?? null,
                    'area_especializada' => $datos['area_especializada'] ?? null,
                    'importado_el' => now()->toDateTimeString(),
                ],
            ]);

            $user->assignRole('alumno');

            // Folio de expediente basado en nivel
            $prefix = match($nivel) {
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

            $this->insertados++;
        } catch (\Exception $e) {
            $this->errores[] = "Error crítico procesando '{$nombre}' ({$curp}): " . $e->getMessage();
        }
    }

    fclose($csv);

    $import = Importacion::create([
        'modulo' => 'usuarios_alumnos_v2',
        'archivo' => $nombreOriginal,
        'user_id' => auth()->id(),
        'registros' => $this->insertados,
        'duplicados' => count($this->duplicados),
        'errores' => count($this->errores),
    ]);

    $import->addMedia($path)
        ->usingFileName($nombreOriginal)
        ->toMediaCollection('archivo_importacion');

    $this->importacionFinalizada = true;
    
    Flux::toast(
        heading: 'Importación Completada',
        text: "Se procesaron {$this->insertados} registros nuevos.",
        variant: 'success'
    );
};

$exportarDuplicados = function () {
    $contenido = "Nombre,CURP,Motivo\n";
    foreach ($this->duplicados as $d) {
        $contenido .= "\"{$d['nombre']}\",\"{$d['curp']}\",\"{$d['motivo']}\"\n";
    }
    
    return response()->streamDownload(function () use ($contenido) {
        echo "\xEF\xBB\xBF"; // UTF-8 BOM para Excel
        echo $contenido;
    }, 'duplicados_' . now()->format('YmdHis') . '.csv');
};

$exportarErrores = function () {
    $contenido = "Error\n" . implode("\n", $this->errores);
    return response()->streamDownload(function () use ($contenido) {
        echo $contenido;
    }, 'errores_importacion_' . now()->format('YmdHis') . '.csv');
};

$descargarPlantilla = function () {
    $columnas = ['cuip', 'curp', 'cup', 'paterno', 'materno', 'nombre', 'dependencia', 'adscripcion', 'perfil', 'sexo', 'tipo', 'username', 'password'];
    $ejemplo = ['CUIP999000', 'CURP123456HDFXYZ01', 'CUP001', 'ALVAREZ', 'DIAZ', 'IVÁN', 'SECRETARÍA DE SEGURIDAD', 'DIRECCIÓN GENERAL', 'POLICÍA PREVENTIVO ESTATAL', 'Hombre', 'ALUMNO', 'CURP123456HDFXYZ01', 'P@ssword'];
    
    $contenido = implode(',', $columnas) . "\n" . implode(',', $ejemplo);
    
    return response()->streamDownload(function () use ($contenido) {
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $contenido;
    }, 'plantilla_importacion_alumnos.csv');
};

$resetear = function () {
    $this->reset(['archivo', 'duplicados', 'errores', 'insertados', 'importacionFinalizada']);
};

?>

<div class="max-w-4xl mx-auto">
    <x-slot name="header">Importación de Alumnos</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('alumnos.index') }}" variant="ghost" icon="arrow-left" size="sm" />
                <flux:heading size="xl">Importar Alumnos Masivamente</flux:heading>
            </div>
            
            <flux:button wire:click="descargarPlantilla" variant="ghost" icon="document-arrow-down" size="sm" class="font-bold border-dashed border-zinc-200 dark:border-zinc-700">
                Descargar Plantilla CSV
            </flux:button>
        </div>

        @if (!$importacionFinalizada)
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-8">
                <div class="space-y-2">
                    <flux:heading>Instrucciones</flux:heading>
                    <flux:subheading>
                        Sube un archivo CSV o Excel con los datos de los alumnos. El sistema validará automáticamente que no existan CURPs duplicadas y generará sus expedientes institucionales.
                    </flux:subheading>
                    <div class="mt-4 flex gap-4">
                        <flux:badge color="zinc" icon="check-circle">CURP (Obligatorio)</flux:badge>
                        <flux:badge color="zinc" icon="check-circle">Nombre (Obligatorio)</flux:badge>
                        <flux:badge color="zinc" icon="check-circle">Sexo (Obligatorio)</flux:badge>
                    </div>
                </div>

                <form wire:submit="importar" class="space-y-6">
                    <flux:field>
                        <flux:label>Seleccionar Archivo</flux:label>
                        <flux:input type="file" wire:model="archivo" accept=".csv,.xlsx" />
                        <flux:error name="archivo" />
                    </flux:field>

                    <div class="flex justify-end gap-2">
                        <flux:button type="submit" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled">
                            <span wire:loading.remove>Comenzar Importación</span>
                            <span wire:loading>Procesando...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <!-- Resultados de la Importación -->
            <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-2xl border border-green-200 dark:border-green-800">
                        <div class="flex justify-between items-center">
                            <flux:heading size="sm" class="text-green-700 dark:text-green-400">Insertados</flux:heading>
                            <flux:icon name="user-plus" class="text-green-500" />
                        </div>
                        <div class="text-3xl font-bold text-green-800 dark:text-green-300 mt-2">{{ $insertados }}</div>
                    </div>

                    <div class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-2xl border border-amber-200 dark:border-amber-800">
                        <div class="flex justify-between items-center">
                            <flux:heading size="sm" class="text-amber-700 dark:text-amber-400">Duplicados</flux:heading>
                            <flux:icon name="users" class="text-amber-500" />
                        </div>
                        <div class="text-3xl font-bold text-amber-800 dark:text-amber-300 mt-2">{{ count($duplicados) }}</div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-2xl border border-red-200 dark:border-red-800">
                        <div class="flex justify-between items-center">
                            <flux:heading size="sm" class="text-red-700 dark:text-red-400">Errores</flux:heading>
                            <flux:icon name="exclamation-triangle" class="text-red-500" />
                        </div>
                        <div class="text-3xl font-bold text-red-800 dark:text-red-300 mt-2">{{ count($errores) }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="p-6 border-b border-zinc-100 dark:border-zinc-700 flex justify-between items-center">
                        <flux:heading>Resumen de Acción</flux:heading>
                        <flux:button variant="primary" size="sm" wire:click="resetear">Nueva Importación</flux:button>
                    </div>

                    <div class="p-6 space-y-4">
                        @if (count($duplicados) > 0)
                            <div class="flex items-center justify-between p-4 bg-amber-50 dark:bg-amber-900/10 rounded-xl">
                                <span class="text-sm text-amber-800 dark:text-amber-300">Se detectaron CURPs que ya pertenecen a usuarios registrados.</span>
                                <flux:button size="sm" variant="ghost" icon="document-arrow-down" wire:click="exportarDuplicados">Bajar Reporte</flux:button>
                            </div>
                        @endif

                        @if (count($errores) > 0)
                            <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/10 rounded-xl">
                                <span class="text-sm text-red-800 dark:text-red-300">Algunos registros no pudieron procesarse debido a datos inválidos.</span>
                                <flux:button size="sm" variant="ghost" icon="document-arrow-down" wire:click="exportarErrores">Bajar Reporte</flux:button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
