<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfesorLog extends Model
{
      protected $fillable = [
        'profesor_id',
        'usuario_id',
        'accion',
        'datos_previos',
        'datos_nuevos',
        'ip_address',
        'user_agent',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }


}
