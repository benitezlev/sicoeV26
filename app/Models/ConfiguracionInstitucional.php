<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionInstitucional extends Model
{
    protected $table = 'configuracion_institucional';

    protected $fillable = [
        'nombre_institucion',
        'siglas',
        'siglas_departamento',
        'titular_ums',
        'puesto_titular',
        'rfc',
        'domicilio_fiscal',
        'telefono_contacto',
        'correo_contacto',
        'pagina_web',
        'logo_path',
        'pleca_recurso_1',
        'pleca_recurso_2',
        'aviso_privacidad_url',
        'objetivo_institucional',
        'leyenda_documentos',
        'parametros_adicionales',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parametros_adicionales' => 'array',
    ];

}
