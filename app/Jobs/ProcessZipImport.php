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
        $this->userId  = $userId;
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
                $filename = $file->getFilename();
                $baseName = pathinfo($filename, PATHINFO_FILENAME);

                // ── Parsear segmentos del nombre del archivo ───────────────────
                // Formato 2 partes:  IDENTIFIER_TIPO.ext
                // Formato 3 partes:  IDENTIFIER_CONSTANCIA_CODIGOCURSO.ext
                //
                // Para CONSTANCIAS se exige 3 partes; el tipo guardado en BD es
                // "CONSTANCIA_CODIGOCURSO" — único por curso — evitando que la
                // limpieza de duplicados borre constancias de cursos distintos.
                // ──────────────────────────────────────────────────────────────
                $parts = explode('_', $baseName, 3); // máximo 3 segmentos

                if (count($parts) < 2) {
                    \Log::warning("ZIP import: nombre sin formato válido — {$filename}");
                    continue;
                }

                $identifier  = strtoupper(trim($parts[0]));
                $tipoBase    = strtoupper(trim($parts[1]));
                $codigoCurso = isset($parts[2]) ? strtoupper(trim($parts[2])) : null;

                // Tipo efectivo almacenado en BD:
                //   CONSTANCIA + código  →  "CONSTANCIA_PFA2024"        (única por curso)
                //   CONSTANCIA sin cód.  →  "CONSTANCIA"                (retrocompatible)
                //   Cualquier otro tipo  →  "ACTA", "OFICIO", etc.
                $tipo = ($tipoBase === 'CONSTANCIA' && $codigoCurso)
                    ? "CONSTANCIA_{$codigoCurso}"
                    : $tipoBase;

                // ── Detección CURP (18 chars) / CUIP (22 chars) ───────────────
                $user = null;
                if (strlen($identifier) === 18) {
                    $user = \App\Models\User::where('curp', $identifier)->first();
                } elseif (strlen($identifier) === 22) {
                    $user = \App\Models\User::where('cuip', $identifier)->first();
                }

                // Fallback: buscar en ambos campos
                if (!$user) {
                    $user = \App\Models\User::where('curp', $identifier)
                        ->orWhere('cuip', $identifier)
                        ->first();
                }

                if (!$user) {
                    \Log::warning("ZIP import: usuario no encontrado — identificador: {$identifier}, archivo: {$filename}");
                    continue;
                }

                // ── Crear o recuperar expediente ─────────────────────────────
                $expediente = \App\Models\Expediente::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'folio'          => 'IMP-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                        'estatus'        => 'incompleto',
                        'fecha_apertura' => now(),
                    ]
                );

                // ── Limpiar duplicados del tipo exacto ───────────────────────
                // "CONSTANCIA_PFA2024" solo borra la del mismo curso,
                // sin afectar "CONSTANCIA_LIDERAZGO" u otras constancias.
                $existingDocs = \App\Models\DocumentosExpediente::where('expediente_id', $expediente->id)
                    ->where('tipo', $tipo)
                    ->get();

                foreach ($existingDocs as $oldDoc) {
                    $oldDoc->clearMediaCollection('archivo');
                    $oldDoc->delete();
                }

                // ── Registrar nuevo documento ────────────────────────────────
                $doc = \App\Models\DocumentosExpediente::create([
                    'user_id'       => $user->id,
                    'expediente_id' => $expediente->id,
                    'tipo'          => $tipo,
                    'archivo'       => 'importado_via_zip',
                    'fecha_carga'   => now(),
                    'cargado_por'   => $this->userId,
                    'estatus'       => 'pendiente',
                ]);

                $doc->addMedia($file->getPathname())
                    ->usingFileName($filename)
                    ->toMediaCollection('archivo');

                \Log::info("ZIP import: documento '{$tipo}' registrado", [
                    'archivo'      => $filename,
                    'identifier'   => $identifier,
                    'user_id'      => $user->id,
                    'tipo_base'    => $tipoBase,
                    'codigo_curso' => $codigoCurso,
                ]);
            }

            // Limpiar directorio temporal
            \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
            \Illuminate\Support\Facades\Storage::delete($this->zipPath);
        }
    }
}
