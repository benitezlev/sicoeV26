<?php

namespace App\Http\Controllers;

use App\Models\DocumentosExpediente;
use App\Models\Expediente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentoExpedienteController extends Controller
{

    public function importar(Request $request)
    {
        $request->validate([
            'archivos' => 'required|array',
            'archivos.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $errores = [];
        $insertados = 0;

        $tiposPermitidos = ['ACTA', 'CONSTANCIA', 'OFICIO', 'IDENTIFICACION'];

        foreach ($request->file('archivos') as $archivo) {
            $nombre = $archivo->getClientOriginalName();
            $partes = explode('_', pathinfo($nombre, PATHINFO_FILENAME));

            $curp = strtoupper(trim($partes[0] ?? ''));
            $tipo = strtoupper(trim($partes[1] ?? ''));

            // 🔒 Validaciones institucionales
            if (!$curp || !$tipo) {
                $errores[] = "Nombre inválido: $nombre";
                continue;
            }

            if (!in_array($tipo, $tiposPermitidos)) {
                $errores[] = "Tipo inválido: $tipo en archivo $nombre";
                continue;
            }

            $user = User::where('curp', $curp)->first();
            if (!$user || !$user->expediente) {
                $errores[] = "CURP no encontrado o sin expediente: $curp";
                continue;
            }

            $existe = DocumentosExpediente::where('expediente_id', $user->expediente->id)
                ->where('tipo', $tipo)
                ->exists();

            if ($existe) {
                $errores[] = "Documento duplicado: $tipo para CURP $curp";
                continue;
            }

            // 📂 Guardar archivo
            $ruta = $archivo->storeAs("expedientes/{$curp}/{$tipo}", $nombre, 'public');

            // 🗂️ Registrar en base de datos
            DocumentosExpediente::create([
                'user_id' => $user->id,
                'expediente_id' => $user->expediente->id,
                'tipo' => $tipo,
                'archivo' => $ruta,
                'fecha_carga' => now(),
                'cargado_por' => auth()->id(),
                'estatus' => 'pendiente',
            ]);

            // 🧾 Log institucional
            Log::channel('expedientes')->info("Documento {$tipo} cargado para CURP {$curp}");

            $insertados++;
        }

        session()->flash('mensaje', "Carga completada: $insertados documentos.");
        session()->flash('errores', $errores);

        return back();
    }

    public function formularioCarga($expedienteId)
    {
        $expediente = Expediente::with('user')->findOrFail($expedienteId);
        return view('admin.documentos.cargar', compact('expediente'));
    }

    public function cargar(Request $request, $expedienteId)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'tipo' => 'required|string|in:ACTA,CONSTANCIA,OFICIO,IDENTIFICACION',
        ]);

        $expediente = Expediente::findOrFail($expedienteId);
        $curp = $expediente->user->curp;
        $nombre = "{$curp}_{$request->tipo}.{$request->file('archivo')->getClientOriginalExtension()}";

        $ruta = $request->file('archivo')->storeAs("expedientes/{$curp}/{$request->tipo}", $nombre, 'public');

        DocumentosExpediente::create([
            'user_id' => $expediente->user_id,
            'expediente_id' => $expediente->id,
            'tipo' => $request->tipo,
            'archivo' => $ruta,
            'fecha_carga' => now(),
            'cargado_por' => auth()->id(),
            'estatus' => 'pendiente',
        ]);

        Log::channel('expedientes')->info("Documento {$request->tipo} cargado manualmente para CURP {$curp}. ID Usuario: " . auth()->id());

        return redirect()->route('expedientes.show', $expediente->id)->with('mensaje', 'Documento cargado correctamente.');
    }

    public function validar($id)
    {
        $documento = DocumentosExpediente::findOrFail($id);
        $documento->update([
            'estatus' => 'validado',
            'observaciones' => null,
        ]);

        Log::channel('expedientes')->info("Documento ID {$documento->id} ({$documento->tipo}) VALIDADO por Usuario: " . auth()->id());

        return back()->with('mensaje', 'Documento validado.');
    }

    public function formularioObservacion($id)
    {
        $documento = DocumentosExpediente::findOrFail($id);
        return view('admin.documentos.observar', compact('documento'));
    }

    public function registrarObservacion(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'required|string|max:1000',
        ]);

        $documento = DocumentosExpediente::findOrFail($id);
        $documento->update([
            'estatus' => 'observado',
            'observaciones' => $request->observaciones,
        ]);

        Log::channel('expedientes')->warning("Documento ID {$documento->id} ({$documento->tipo}) OBSERVADO: {$request->observaciones}. Por Usuario: " . auth()->id());

        return redirect()->route('expedientes.show', $documento->expediente_id)->with('mensaje', 'Documento observado.');
    }


}
