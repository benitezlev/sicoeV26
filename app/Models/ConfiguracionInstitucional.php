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

    /**
     * Obtiene si el copiloto IA está activo a nivel institucional.
     */
    public static function isCopilotoActivo(): bool
    {
        $config = self::first();
        if (!$config) {
            return true; // Activo por defecto si no hay registro
        }
        $params = $config->parametros_adicionales ?? [];
        return (bool) ($params['copiloto_ia_activo'] ?? true);
    }

    /**
     * Modifica el estado de activación del copiloto IA local.
     */
    public static function setCopilotoActivo(bool $activo): void
    {
        $config = self::first() ?? new self();
        $params = $config->parametros_adicionales ?? [];
        $params['copiloto_ia_activo'] = $activo;
        $config->parametros_adicionales = $params;
        $config->save();
    }
}
