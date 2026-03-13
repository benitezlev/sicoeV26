<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Grupo;
use App\Models\Expediente;
use App\Models\DocumentosExpediente;
use App\Models\Calificacion;
use App\Models\Municipio;
use App\Models\Plantel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AcademicDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados
        DB::statement("SET session_replication_role = 'replica'");
        Calificacion::truncate();
        DB::table('grupo_user')->delete();
        Grupo::truncate();
        DB::table('curso_materia')->delete();
        Materia::truncate();
        Curso::truncate();
        DocumentosExpediente::truncate();
        Expediente::truncate();
        User::role('alumno')->delete();
        DB::statement("SET session_replication_role = 'origin'");

        $municipios = Municipio::all();
        $planteles = Plantel::all();

        $alumnoData = [
            ['nombre' => 'JUAN', 'paterno' => 'PEREZ', 'materno' => 'GARCIA', 'nivel' => 'estatal', 'sexo' => 'H'],
            ['nombre' => 'MARIA', 'paterno' => 'LOPEZ', 'materno' => 'MARTINEZ', 'nivel' => 'municipal', 'sexo' => 'M'],
            ['nombre' => 'CARLOS', 'paterno' => 'RODRIGUEZ', 'materno' => 'HERNANDEZ', 'nivel' => 'fiscalia', 'sexo' => 'H'],
            ['nombre' => 'ANA', 'paterno' => 'SANCHEZ', 'materno' => 'RAMIREZ', 'nivel' => 'estatal', 'sexo' => 'M'],
            ['nombre' => 'LUIS', 'paterno' => 'GONZALEZ', 'materno' => 'FLORES', 'nivel' => 'municipal', 'sexo' => 'H'],
        ];

        foreach ($alumnoData as $i => $data) {
            $curp = "TEST" . str_pad($i + 1, 14, "0", STR_PAD_LEFT);
            $user = User::create([
                'nombre' => $data['nombre'],
                'paterno' => $data['paterno'],
                'materno' => $data['materno'],
                'email' => strtolower($data['nombre']) . ($i) . "@example.com",
                'username' => $curp,
                'password' => Hash::make('password'),
                'curp' => $curp,
                'nivel' => $data['nivel'],
                'sexo' => $data['sexo'],
                'tipo' => 'alumno',
                'plantel_id' => $planteles->random()->id,
                'municipio_id' => $data['nivel'] === 'municipal' ? ($municipios->count() > 0 ? $municipios->random()->id : null) : null,
                'perfil_data' => [
                    'dependencia' => 'Corporación de Prueba',
                    'adscripcion' => 'Unidad Alfa',
                ]
            ]);
            $user->assignRole('alumno');

            $prefix = match($user->nivel) {
                'municipal' => 'MUN',
                'fiscalia' => 'FIS',
                default => 'EST'
            };
            
            $expediente = Expediente::create([
                'user_id' => $user->id,
                'folio' => "{$prefix}-2026-" . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'estatus' => 'incompleto',
                'fecha_apertura' => now(),
            ]);

            DocumentosExpediente::create([
                'expediente_id' => $expediente->id,
                'user_id' => $user->id,
                'tipo' => 'ACTA',
                'archivo' => 'expedientes/demo/acta_nacimiento_test.pdf',
                'fecha_carga' => now(),
                'cargado_por' => 1,
                'estatus' => 'validado'
            ]);
        }

        $curso = Curso::create([
            'identificador' => 'FI-PREV-01',
            'nombre' => 'Formación Inicial para Policía Preventivo',
            'tipo' => 'Formación Inicial',
            'num_horas' => 972,
            'categoria' => 'Seguridad Pública',
            'descripcion' => 'Programa rector de formación inicial'
        ]);

        $materiasList = [
            ['nombre' => 'Derecho Penal', 'clave' => 'DP-01', 'tipo' => 'teorica'],
            ['nombre' => 'Armamento y Tiro', 'clave' => 'AT-01', 'tipo' => 'practica'],
            ['nombre' => 'Acondicionamiento Físico', 'clave' => 'AF-01', 'tipo' => 'practica'],
            ['nombre' => 'Primeros Auxilios', 'clave' => 'PA-01', 'tipo' => 'mixta'],
            ['nombre' => 'Derechos Humanos', 'clave' => 'DH-01', 'tipo' => 'teorica'],
        ];

        foreach ($materiasList as $idx => $mData) {
            $m = Materia::create(array_merge($mData, ['num_horas' => 40, 'activo' => true]));
            $curso->materias()->attach($m->id, [
                'orden' => $idx + 1,
                'semestre' => 1,
                'creditos' => 5,
                'obligatoria' => true
            ]);
        }

        $grupo = Grupo::create([
            'nombre' => 'Generación 2026-A',
            'plantel_id' => $planteles->first()->id,
            'curso_id' => $curso->id,
            'periodo' => '2026-1',
            'estado' => 'activo',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
        ]);

        $alumnos = User::role('alumno')->get();
        foreach ($alumnos as $alumno) {
            $grupo->alumnos()->attach($alumno->id, [
                'fecha_asignacion' => now(),
                'estado' => 'activo'
            ]);

            if ($alumno->id == $alumnos->first()->id) {
                foreach ($curso->materias as $materia) {
                    Calificacion::create([
                        'user_id' => $alumno->id,
                        'grupo_id' => $grupo->id,
                        'materia_id' => $materia->id,
                        'unidad' => 1,
                        'calificacion' => rand(7, 10),
                        'registrado_por' => 1,
                    ]);
                }
            }
        }
    }
}
