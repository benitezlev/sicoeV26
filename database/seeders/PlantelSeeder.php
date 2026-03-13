<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantelSeeder extends Seeder
{
    public function run(): void
    {
        $planteles = [
            ['id' => 1, 'name' => 'Toluca', 'direccion' => 'Toluca, Edo. Méx.', 'tel' => '7221234567', 'titular' => 'Titular Toluca'],
            ['id' => 2, 'name' => 'Tlalnepantla', 'direccion' => 'Tlalnepantla, Edo. Méx.', 'tel' => '5512345678', 'titular' => 'Titular Tlalnepantla'],
            ['id' => 3, 'name' => 'Nezahualcóyotl', 'direccion' => 'Nezahualcóyotl, Edo. Méx.', 'tel' => '5587654321', 'titular' => 'Titular Neza'],
            ['id' => 4, 'name' => 'Malinalco', 'direccion' => 'Malinalco, Edo. Méx.', 'tel' => '7141234567', 'titular' => 'Titular Malinalco'],
        ];

        foreach ($planteles as $plantel) {
            DB::table('planteles')->updateOrInsert(['id' => $plantel['id']], $plantel);
        }
    }
}
