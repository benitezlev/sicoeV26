<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetaCapacitacion;

class MetaCapacitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metas = [
            ['anio' => 2024, 'meta' => 2000],
            ['anio' => 2025, 'meta' => 2500],
            ['anio' => 2026, 'meta' => 3000],
        ];

        foreach ($metas as $m) {
            MetaCapacitacion::updateOrCreate(
                ['anio' => $m['anio']],
                ['meta' => $m['meta']]
            );
        }
    }
}
