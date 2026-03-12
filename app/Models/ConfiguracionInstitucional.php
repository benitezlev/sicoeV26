<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionInstitucional extends Model
{
    protected $table = 'configuracion_institucional';

    protected $fillable = [
        'nombre_institucion',
        'siglas',
        'rfc',
        'domicilio_fiscal',
        'telefono_contacto',
        'correo_contacto',
        'pagina_web',
        'logo_path',
        'leyenda_documentos',
        'parametros_adicionales',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parametros_adicionales' => 'array',
    ];

}
