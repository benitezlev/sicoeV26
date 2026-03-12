<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ConfiguracionInstitucionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('configuracion_institucional')->insert([
            'nombre_institucion' => 'Universidad Mexiquense de Seguridad',
            'siglas' => 'UMS',
            'rfc' => 'UMS123456789',
            'domicilio_fiscal' => 'Av. Lerma #123, Lerma de Villada, Estado de México',
            'telefono_contacto' => '722-123-4567',
            'correo_contacto' => 'contacto@ums.edu.mx',
            'pagina_web' => 'https://www.ums.edu.mx',
            'logo_path' => 'logo_ums.png',
            'leyenda_documentos' => 'Documento generado por el Sistema de Control Escolar (SICOE)',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

    }
}
