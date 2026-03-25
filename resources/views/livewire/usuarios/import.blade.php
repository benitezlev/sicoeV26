<?php

use function Livewire\Volt\{state, layout, rules, usesFileUploads};
use App\Models\User;
use App\Models\Expediente;
use App\Models\Importacion;
use App\Models\Grupo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();

layout('layouts.app');

state([
    'archivo' => null,
    'duplicados' => [],
    'errores' => [],
    'insertados' => 0,
    'importacionFinalizada' => false,
]);

$importar = function () {
    Log::info('Iniciando proceso de importación');
    Log::info('Estado del archivo:', ['archivo' => $this->archivo]);

    if (!$this->archivo) {
        $this->addError('archivo', 'El servidor no recibió el archivo. Verifica que no exceda los 2MB.');
        return;
    }

    try {
        $this->validate([
            'archivo' => 'file|max:2048', 
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Falla de validación:', $e->errors());
        throw $e;
    }

    $nombreOriginal = $this->archivo->getClientOriginalName();
    $path = $this->archivo->getRealPath();

    $csv = fopen($path, 'r');
    
    // Auto-detección de delimitador (Coma vs Punto y Coma)
    $primeraLinea = fgets($csv);
    rewind($csv);
    $separador = (strpos($primeraLinea, ';') !== false && strpos($primeraLinea, ',') === false) ? ';' : ',';
    
    // Intentar detectar encoding
    $enc = mb_detect_encoding($primeraLinea, "UTF-8, ISO-8859-1, Windows-1252", true);
    if ($enc && $enc !== 'UTF-8') {
        stream_filter_append($csv, "convert.iconv.$enc/UTF-8");
    }

    $encabezados = fgetcsv($csv, 0, $separador);
    Log::info('Encabezados detectados:', ['encabezados' => $encabezados, 'separador' => $separador, 'encoding' => $enc]);
    
    $requeridos = ['curp', 'nombre', 'sexo', 'tipo'];
    
    // Limpiar BOM y espacios de encabezados
    if ($encabezados) {
        $encabezados = array_map(fn($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))), $encabezados);
    }

    foreach ($requeridos as $campo) {
        if (!$encabezados || !in_array($campo, $encabezados)) {
            Log::error("Falta encabezado obligatorio: $campo . Disponibles: " . implode(',', $encabezados ?? []));
            fclose($csv);
            $this->addError('archivo', "El archivo no contiene el campo obligatorio: $campo. Verifica que los encabezados estén en minúsculas o el delimitador sea correcto.");
            return;
        }
    }

    $this->duplicados = [];
    $this->errores = [];
    $this->insertados = 0;
    $filaNum = 1;

    while (($fila = fgetcsv($csv, 0, $separador)) !== false) {
        $filaNum++;
        
        // Sanitizar cada celda para asegurar que sea UTF-8 válido y evitar errores de JSON
        $fila = array_map(function($valor) {
            $valor = (string)$valor;
            $currentEnc = mb_detect_encoding($valor, "UTF-8, ISO-8859-1, Windows-1252", true) ?: 'UTF-8';
            return iconv($currentEnc, "UTF-8//IGNORE", $valor);
        }, $fila);

        if (count($encabezados) !== count($fila)) {
            $msg = "Fila $filaNum con columnas incompletas. Esperadas: " . count($encabezados) . " - Recibidas: " . count($fila);
            Log::warning($msg);
            $this->errores[] = $msg;
            continue;
        }

        $datos = array_combine($encabezados, $fila);
        $tipoUsuario = strtolower(trim($datos['tipo'] ?? 'aspirante'));
        $curp = strtoupper(trim($datos['curp'] ?? ''));
        $cuip = trim($datos['cuip'] ?? '');
        $cup = trim($datos['cup'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');

        // Lógica de Identificador Flexible (CUIP para Activos, CURP para Aspirantes)
        $identificador = ($tipoUsuario === 'activo' && !empty($cuip)) ? $cuip : $curp;
        $campoIdentificador = ($tipoUsuario === 'activo' && !empty($cuip)) ? 'cuip' : 'curp';

        if (!$identificador) {
            $msg = "Fila $filaNum: Identificador clave (CUIP o CURP) ausente para tipo '$tipoUsuario'.";
            Log::warning($msg);
            $this->errores[] = $msg;
            continue;
        }

        // Buscar usuario existente
        $user = User::where($campoIdentificador, $identificador)->first();
        
        if ($user) {
            // Si el usuario ya existe, lo consideramos "duplicado" para creación, 
            // pero permitimos que continúe para la lógica de inscripción a grupo si aplica.
            $yaRegistrado = true;
        } else {
            $yaRegistrado = false;
        }

        $sexoRaw = strtoupper(trim($datos['sexo'] ?? ''));
        $sexo = match ($sexoRaw) {
            'HOMBRE', 'H', 'MASCULINO' => 'H',
            'MUJER', 'M', 'FEMENINO' => 'M',
            default => null,
        };

        if (!$sexo) {
            $msg = "Fila $filaNum: CURP $curp ({$nombre}) tiene sexo inválido o ausente: '$sexoRaw'";
            Log::warning($msg);
            $this->errores[] = $msg;
            continue;
        }

        // Configuración de Nivel y Datos Flexibles
        $nivel = strtolower(trim($datos['nivel'] ?? 'estatal'));
        if (!in_array($nivel, ['estatal', 'municipal', 'fiscalia', 'administrativo'])) {
            $nivel = 'estatal';
        }

        if (!$yaRegistrado) {
            try {
                $user = User::create([
                    'nombre' => $nombre,
                    'paterno' => $datos['paterno'] ?? '',
                    'materno' => $datos['materno'] ?? '',
                    'username' => $datos['username'] ?? $identificador,
                    'email' => strtolower($identificador) . '@sicoe.mx',
                    'password' => Hash::make($datos['password'] ?? $identificador),
                    'curp' => $curp ?: null,
                    'cuip' => $cuip ?: null,
                    'cup' => $cup ?: null,
                    'sexo' => $sexo,
                    'tipo' => 'alumno', // Rol base
                    'nivel' => $nivel,
                    'perfil_data' => [
                        'perfil' => $datos['perfil'] ?? null,
                        'dependencia' => $datos['dependencia'] ?? null,
                        'adscripcion' => $datos['adscripcion'] ?? null,
                        'importado_el' => now()->toDateTimeString(),
                        'tipo_captura' => $tipoUsuario,
                    ],
                ]);

                $user->assignRole('alumno');

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
                $msg = "Error crítico procesando '{$nombre}' ({$identificador}): " . $e->getMessage();
                Log::error($msg);
                $this->errores[] = $msg;
                continue;
            }
        }

        // Lógica de Auto-Inscripción a Grupo
        $grupoNombre = trim($datos['grupo'] ?? '');
        if ($grupoNombre && $user) {
            $grupo = Grupo::where('nombre', $grupoNombre)->first();
            if ($grupo) {
                // Verificar si ya está inscrito
                $estaInscrito = $user->grupos()->where('grupo_id', $grupo->id)->exists();
                if (!$estaInscrito) {
                    $user->grupos()->attach($grupo->id, [
                        'estado' => 'activo',
                        'fecha_asignacion' => now()
                    ]);
                    Log::info("Alumno {$user->id} inscrito automáticamente al grupo: {$grupoNombre}");
                }
            } else {
                $this->errores[] = "Fila $filaNum: El grupo '$grupoNombre' no fue encontrado.";
            }
        }

        if ($yaRegistrado && !$grupoNombre) {
            $this->duplicados[] = [
                'nombre' => $nombre . ' ' . ($datos['paterno'] ?? ''),
                'curp' => $identificador,
                'motivo' => 'Usuario ya existe y no se especificó grupo'
            ];
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
    $this->archivo = null; // Limpiar después de procesar
    
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
    $columnas = ['cuip', 'curp', 'cup', 'paterno', 'materno', 'nombre', 'dependencia', 'adscripcion', 'perfil', 'sexo', 'tipo', 'username', 'password', 'grupo'];
    
    $ejemplos = [
        ['CUIP999000', 'CURP123456HDFXYZ01', 'CUP-001', 'GOMEZ', 'PEREZ', 'JUAN', 'SECRETARIA DE SEGURIDAD', 'REGION NEZA', 'POLICIA', 'HOMBRE', 'activo', 'juan.gomez', 'P@ssword123', 'PFA-NEZ-GPO01'],
        ['', 'CURP789012MDFXYZ02', '', 'LOPEZ', 'RUIZ', 'MARÍA', 'UMS', 'PLANTEL NEZA', 'CADETE', 'MUJER', 'aspirante', 'maria.lopez', 'Maria123', 'PFA-NEZ-GPO01'],
    ];
    
    $contenido = implode(',', $columnas) . "\n";
    foreach($ejemplos as $e) {
        $contenido .= implode(',', $e) . "\n";
    }
    
    return response()->streamDownload(function () use ($contenido) {
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $contenido;
    }, 'plantilla_sicoe_completa.csv');
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
                        Sube un archivo CSV con los datos de alumnos. Puedes incluir una columna <b>'grupo'</b> con el nombre exacto (ej: <code>PFA-NEZ-GPO01</code>) para inscribirlos automáticamente.
                    </flux:subheading>
                    <div class="mt-4 flex gap-4">
                        <flux:badge color="zinc" icon="check-circle">Tipo Usuario (Activo / Aspirante)</flux:badge>
                        <flux:badge color="zinc" icon="check-circle">CUIP (Activos)</flux:badge>
                        <flux:badge color="zinc" icon="check-circle">CURP (Aspirantes)</flux:badge>
                        <flux:badge color="blue" icon="academic-cap">Grupo (Opcional)</flux:badge>
                    </div>
                </div>

                <form wire:submit="importar" class="space-y-6">
                    <div 
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="space-y-4"
                    >
                        <flux:field>
                            <flux:label>Seleccionar Archivo (CSV / XLSX)</flux:label>
                            <input type="file" wire:model="archivo" accept=".csv,.xlsx" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-900 dark:file:text-zinc-400 border border-zinc-200 dark:border-zinc-700 rounded-xl p-2 bg-white dark:bg-zinc-900 shadow-sm" />
                            <flux:error name="archivo" />
                        </flux:field>

                        <!-- Barra de progreso real de Livewire -->
                        <div x-show="uploading" class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:button type="submit" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled" wire:target="archivo, importar">
                            <span wire:loading.remove wire:target="importar">Comenzar Importación</span>
                            <span wire:loading wire:target="importar" class="font-bold">Procesando registros...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
            
            <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl flex gap-4 items-center">
                <flux:icon name="exclamation-triangle" class="text-amber-500 shrink-0" />
                <div class="text-[11px] text-amber-800 dark:text-amber-400 leading-tight">
                    <p class="font-bold uppercase tracking-wider mb-1">Nota de Capacidad de Servidor</p>
                    Tu servidor PHP actual tiene un límite de <b>2.0 MB</b> por archivo. Si tu lista de alumnos es muy extensa, te recomendamos subirla en bloques pequeños o contactar a soporte para incrementar el <code>upload_max_filesize</code>.
                </div>
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
