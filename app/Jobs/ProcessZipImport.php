<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessZipImport implements ShouldQueue
{
    use Queueable;

    public $zipPath;
    public $userId;

    public function __construct(string $zipPath, int $userId)
    {
        $this->zipPath = $zipPath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $storagePath = storage_path('app/' . $this->zipPath);
        
        if (!file_exists($storagePath)) {
            \Log::error("Archivo ZIP no encontrado en: " . $storagePath);
            return;
        }

        $zip = new \ZipArchive;
        if ($zip->open($storagePath) === TRUE) {
            $extractPath = storage_path('app/temp_zip_' . uniqid());
            $zip->extractTo($extractPath);
            $zip->close();

            $files = \Illuminate\Support\Facades\File::allFiles($extractPath);

            foreach ($files as $file) {
                $filename = $file->getFilename(); // Ejemplo: IDENTIFIER_ACTA.pdf
                $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));

                if (count($parts) < 2) continue;

                $identifier = strtoupper(trim($parts[0]));
                $tipo = strtoupper(trim($parts[1]));

                // Detectar si el identificador es CURP (18 caracteres) o CUIP (22 caracteres)
                $isCurp = (strlen($identifier) === 18);
                $isCuip = (strlen($identifier) === 22);

                // Buscar usuario por el campo correspondiente
                $user = null;
                if ($isCurp) {
                    $user = \App\Models\User::where('curp', $identifier)->first();
                } elseif ($isCuip) {
                    $user = \App\Models\User::where('cuip', $identifier)->first();
                }

                // Fallback general por robustez
                if (!$user) {
                    $user = \App\Models\User::where('curp', $identifier)
                        ->orWhere('cuip', $identifier)
                        ->first();
                }
                
                if ($user) {
                    $expediente = \App\Models\Expediente::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'folio' => 'IMP-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                            'estatus' => 'incompleto',
                            'fecha_apertura' => now(),
                        ]
                    );

                    // Eliminar documentos duplicados anteriores del mismo tipo en el expediente para evitar acumulación
                    $existingDocs = \App\Models\DocumentosExpediente::where('expediente_id', $expediente->id)
                        ->where('tipo', $tipo)
                        ->get();

                    foreach ($existingDocs as $oldDoc) {
                        // Elimina los archivos físicos asociados de Spatie MediaLibrary y limpia base de datos
                        $oldDoc->clearMediaCollection('archivo');
                        $oldDoc->delete();
                    }

                    $doc = \App\Models\DocumentosExpediente::create([
                        'user_id' => $user->id,
                        'expediente_id' => $expediente->id,
                        'tipo' => $tipo,
                        'archivo' => 'importado_via_zip',
                        'fecha_carga' => now(),
                        'cargado_por' => $this->userId,
                        'estatus' => 'pendiente',
                    ]);

                    $doc->addMedia($file->getPathname())
                        ->usingFileName($filename)
                        ->toMediaCollection('archivo');
                    
                    \Log::info("Documento {$tipo} procesado para identificador (CURP/CUIP): {$identifier}");
                }
            }

            // Limpiar
            \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
            \Illuminate\Support\Facades\Storage::delete($this->zipPath);
        }
    }
}
