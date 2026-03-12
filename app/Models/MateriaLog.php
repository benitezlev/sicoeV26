<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaLog extends Model
{
    protected $table = 'materia_logs';

    protected $fillable = [
        'materia_id',
        'user_id',
        'accion',
        'datos_previos',
        'datos_nuevos',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

