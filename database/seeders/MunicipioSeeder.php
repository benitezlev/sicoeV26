<?php

namespace Database\Seeders;

use App\Models\Municipio;
use Illuminate\Database\Seeder;

class MunicipioSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = [
            'Toluca', 'Metepec', 'Lerma', 'Tlalnepantla de Baz', 'Naucalpan de Juárez',
            'Ecatepec de Morelos', 'Nezahualcóyotl', 'Huixquilucan', 'Atizapán de Zaragoza',
            'Cuautitlán Izcalli', 'Zinacantepec', 'Almoloya de Juárez', 'San Mateo Atenco',
            'Ocoyoacac', 'Tianguistenco', 'Xonacatlán', 'Otzolotepec', 'Temoaya'
        ];

        foreach ($municipios as $nombre) {
            Municipio::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
