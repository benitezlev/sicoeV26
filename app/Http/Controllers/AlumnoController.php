<?php

namespace App\Http\Controllers;

use App\Imports\AlumnoImport;
use App\Models\Expediente;
use App\Models\Importacion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Session;



class AlumnoController extends Controller
{
    public $archivo;

    public function index()
    {
        return view('admin.alumnos.index');
    }

    public function vistaImportar()
    {
        return view('admin.alumnos.importar');

    }


    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,xlsx',
        ]);

        $archivo = $request->file('archivo');
        $nombreArchivo = $archivo->getClientOriginalName();

        $csv = fopen($archivo->getRealPath(), 'r');
        stream_filter_append($csv, 'convert.iconv.ISO-8859-1/UTF-8');

        $encabezados = fgetcsv($csv);
        $requeridos = ['curp', 'nombre', 'sexo'];

        foreach ($requeridos as $campo) {
            if (!in_array($campo, $encabezados)) {
                fclose($csv);
                return back()->withErrors(['archivo' => "Falta el campo obligatorio: $campo"]);
            }
        }

        function normalizarSexo($valor)
        {
            $valor = strtoupper(trim($valor));
            return match ($valor) {
                'HOMBRE' => 'H',
                'MUJER' => 'M',
                default => null,
            };
        }

        $errores = [];
        $duplicados = [];
        $insertados = 0;

        while (($fila = fgetcsv($csv)) !== false) {
            $datos = array_combine($encabezados, $fila);
            $curp = trim($datos['curp'] ?? '');

            if (!$curp) {
                $errores[] = "Fila sin CURP.";
                continue;
            }

            if (User::where('curp', $curp)->exists()) {
                $duplicados[] = "{$datos['nombre']} {$datos['paterno']} {$datos['materno']} -- ({$curp}) --";
                continue;
            }

            $sexo = normalizarSexo($datos['sexo'] ?? '');
            if ($sexo === null) {
                $errores[] = "CURP $curp tiene sexo inválido: '{$datos['sexo']}'";
                continue;
            }

            $email = $datos['email'] ?? strtolower($curp) . '@sicoe.mx';
            $password = $datos['password'] ?? $curp;
            $username = $datos['username'] ?? Str::slug($curp);

            try {
                $user = User::create([
                    'paterno' => $datos['paterno'] ?? 'Sin nombre',
                    'materno' => $datos['materno'] ?? null,
                    'nombre' => $datos['nombre'] ?? null,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'curp' => $curp,
                    'cuip' => $datos['cuip'] ?? null,
                    'cup' => $datos['cup'] ?? null,
                    'dependencia' => $datos['dependencia'] ?? null,
                    'adscripcion' => $datos['adscripcion'] ?? null,
                    'sexo' => $sexo,
                    'tipo' => $datos['tipo'] ?? null,
                    'perfil' => $datos['perfil'] ?? null,
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('alumno');
                }

                // Crear expediente institucional
                try {
                    $expediente = Expediente::create([
                        'user_id' => $user->id,
                        'folio' => 'EXP-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                        'estatus' => 'incompleto',
                        'fecha_apertura' => now(),
                        'observaciones' => null,
                    ]);

                    Log::channel('expedientes')->info("Expediente creado: {$expediente->id} para usuario {$user->id} ({$curp})");
                } catch (\Exception $e) {
                    $errores[] = "Error al crear expediente para CURP $curp: " . $e->getMessage();
                    Log::channel('expedientes')->error("Error al crear expediente para CURP {$curp}: " . $e->getMessage());
                }

                $insertados++;
            } catch (\Exception $e) {
                $errores[] = "Error al insertar CURP $curp: " . $e->getMessage();
                Log::channel('expedientes')->error("Error al insertar CURP {$curp}: " . $e->getMessage());
            }
        }

        fclose($csv);

        Importacion::create([
            'modulo' => 'alumnos',
            'archivo' => $nombreArchivo,
            'user_id' => auth()->id(),
            'registros' => $insertados,
            'duplicados' => count($duplicados),
            'errores' => count($errores),
        ]);

        session()->flash('mensaje', "Importación completada: $insertados alumnos nuevos.");
        session()->flash('duplicados', $duplicados);
        session()->flash('errores', $errores);

        return redirect()->route('alumnos.importar');
    }


    public function exportarDuplicados(Request $request)
    {
        $duplicados = session('duplicados') ?? [];

        if (empty($duplicados)) {
            return back()->withErrors(['duplicados' => 'No hay CURP duplicados para exportar.']);
        }

        $contenido = implode("\n", $duplicados);
        $nombreArchivo = 'duplicados_' . now()->format('Ymd_His') . '.csv';

        Storage::put("public/$nombreArchivo", $contenido);

        return response()->download(storage_path("app/public/$nombreArchivo"));
    }

    public function exportarErrores(Request $request)
    {
        $errores = session('errores') ?? [];

        if (empty($errores)) {
            return back()->withErrors(['errores' => 'No hay errores para exportar.']);
        }

        $contenido = implode("\n", $errores);
        $nombreArchivo = 'errores_importacion_' . now()->format('Ymd_His') . '.csv';

        Storage::put("public/$nombreArchivo", $contenido);

        return response()->download(storage_path("app/public/$nombreArchivo"));
    }




}
