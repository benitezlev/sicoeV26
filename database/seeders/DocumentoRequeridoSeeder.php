<?php

namespace Database\Seeders;

use App\Models\DocumentoRequerido;
use Illuminate\Database\Seeder;

class DocumentoRequeridoSeeder extends Seeder
{
    public function run(): void
    {
        // Documentos Comunes para todos
        $comunes = [
            ['tipo' => 'ACTA', 'descripcion' => 'Acta de Nacimiento'],
            ['tipo' => 'IDENTIFICACION', 'descripcion' => 'Identificación Oficial (INE/Pasaporte)'],
            ['tipo' => 'CURP', 'descripcion' => 'CURP actualizado'],
        ];

        foreach ($comunes as $doc) {
            DocumentoRequerido::updateOrCreate(
                ['tipo' => $doc['tipo'], 'nivel' => null],
                ['perfil' => 'alumno', 'descripcion' => $doc['descripcion']]
            );
        }

        // Documentos para Fiscalía
        DocumentoRequerido::updateOrCreate(
            ['tipo' => 'OFICIO_FISCALIA', 'nivel' => 'fiscalia'],
            ['perfil' => 'alumno', 'descripcion' => 'Oficio de Comisión de Fiscalía']
        );

        // Documentos para Municipales
        DocumentoRequerido::updateOrCreate(
            ['tipo' => 'NOMBRAMIENTO_MUN', 'nivel' => 'municipal'],
            ['perfil' => 'alumno', 'descripcion' => 'Nombramiento Municipal Vigente']
        );

        // Documentos para Estatal
        DocumentoRequerido::updateOrCreate(
            ['tipo' => 'ALTA_ESTATAL', 'nivel' => 'estatal'],
            ['perfil' => 'alumno', 'descripcion' => 'Documento de Alta en Corporación Estatal']
        );
    }
}
