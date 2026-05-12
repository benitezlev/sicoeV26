<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Recurso;
use Illuminate\Database\Seeder;

class RecursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recursos = [
            [
                'nombre' => 'FASP',
                'clave' => 'FASP',
                'descripcion' => 'Fondo de Aportaciones para la Seguridad Pública.',
                'activo' => true,
            ],
            [
                'nombre' => 'FORTAMUN',
                'clave' => 'FORTAMUN',
                'descripcion' => 'Fondo de Aportaciones para el Fortalecimiento de los Municipios y de las Demarcaciones Territoriales.',
                'activo' => true,
            ],
            [
                'nombre' => 'Recurso Propio',
                'clave' => 'PROPIO',
                'descripcion' => 'Presupuesto ordinario estatal o recursos generados por la propia institución.',
                'activo' => true,
            ],
            [
                'nombre' => 'Recurso Estatal',
                'clave' => 'ESTATAL',
                'descripcion' => 'Subsidio o asignación presupuestal directa del Gobierno del Estado de México.',
                'activo' => true,
            ],
            [
                'nombre' => 'Recurso Federal',
                'clave' => 'FEDERAL',
                'descripcion' => 'Convenios y asignaciones federales extraordinarias directas.',
                'activo' => true,
            ],
        ];

        foreach ($recursos as $recurso) {
            Recurso::updateOrCreate(
                ['nombre' => $recurso['nombre']],
                $recurso
            );
        }
    }
}
