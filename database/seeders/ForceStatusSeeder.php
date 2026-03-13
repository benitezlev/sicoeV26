<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Grupo;
use App\Models\AsistenciaIndividual;

class ForceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = User::role('alumno')->get();
        $grupo = Grupo::first();

        if (!$grupo) return;

        foreach ($alumnos as $alumno) {
            // El 80% presente, el resto falta o permiso
            $status = 'presente';
            $rand = rand(1, 10);
            if ($rand == 9) $status = 'falta';
            if ($rand == 10) $status = 'permiso';

            AsistenciaIndividual::updateOrCreate(
                ['user_id' => $alumno->id, 'grupo_id' => $grupo->id, 'fecha' => date('Y-m-d')],
                ['estatus' => $status]
            );
        }
    }
}
