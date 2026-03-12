<?php

namespace App\Http\Controllers;

use App\Models\DocumentosExpediente;
use App\Models\Expediente;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;




class ExpedienteController extends Controller
{


    public function index(Request $request)
    {

        // 🔍 Filtro adicional por estatus, si aplica
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $expedientes = Expediente::whereHas('user', fn($q) => $q->esAlumno())->paginate(15);

        return view('admin.expedientes.index', compact('expedientes'));
    }

    public function show($id)
    {
        $expediente = \App\Models\Expediente::with(['user', 'documentos.cargador'])->findOrFail($id);

        return view('admin.expedientes.show', compact('expediente'));
    }

    public function revalidar($id)
    {
        $expediente = Expediente::with(['user', 'documentos'])->findOrFail($id);

        // Ejecuta validación institucional por perfil
        $faltantes = $expediente->validarDocumentosPorPerfil();

        // Registra auditoría opcional
        Log::channel('expedientes')->info('Revalidación ejecutada', [
            'expediente_id' => $expediente->id,
            'usuario_id' => auth()->id(),
            'faltantes' => $faltantes,
            'resultado' => empty($faltantes) ? 'completo' : 'incompleto',
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Mensaje institucional
        $mensaje = empty($faltantes)
            ? '✅ Expediente completo y validado.'
            : '⚠️ Expediente revalidado: documentos faltantes detectados.';

        return redirect()->route('expedientes.show', $expediente->id)
            ->with('mensaje', $mensaje);
    }

    public function formularioCarga($expedienteId)
    {
        $expediente = Expediente::with('user')->findOrFail($expedienteId);
        return view('admin.documentos.cargar', compact('expediente'));
    }

    public function cargarMasivos(Request $request, $expedienteId)
    {
        $request->validate([
            'documentos' => 'required|array',
            'documentos.*.archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'documentos.*.tipo' => 'required|string|in:ACTA,CONSTANCIA,OFICIO,IDENTIFICACION',
        ]);

        $expediente = Expediente::findOrFail($expedienteId);

        foreach ($request->documentos as $doc) {
            $path = $doc['archivo']->store('documentos');

            $documento = DocumentosExpediente::create([
                'expediente_id' => $expediente->id,
                'tipo' => $doc['tipo'],
                'ruta' => $path,
                'estatus' => 'pendiente', // validación posterior
            ]);

            Log::channel('expedientes')->info("Documento masivo cargado: {$doc['tipo']} para expediente ID: {$expediente->id}. ID Documento: {$documento->id}");
        }

        return redirect()->route('expedientes.show', $expedienteId)->with('success', 'Documentos cargados correctamente.');
    }

}
