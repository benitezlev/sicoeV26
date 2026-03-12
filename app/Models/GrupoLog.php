<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoLog extends Model
{
     protected $fillable = [
        'grupo_id',
        'usuario_id',
        'accion',
        'datos_previos',
        'datos_nuevos',
        'ip_address',
        'user_agent',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
