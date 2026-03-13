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
                $filename = $file->getFilename(); // Ejemplo: CURP_ACTA.pdf
                $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));

                if (count($parts) < 2) continue;

                $curp = strtoupper($parts[0]);
                $tipo = strtoupper($parts[1]);

                // Buscar usuario
                $user = \App\Models\User::where('curp', $curp)->first();
                
                if ($user) {
                    $expediente = \App\Models\Expediente::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'folio' => 'IMP-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                            'estatus' => 'incompleto',
                            'fecha_apertura' => now(),
                        ]
                    );

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
                    
                    \Log::info("Documento {$tipo} procesado para CURP: {$curp}");
                }
            }

            // Limpiar
            \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
            \Illuminate\Support\Facades\Storage::delete($this->zipPath);
        }
    }
}
