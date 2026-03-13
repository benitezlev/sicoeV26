<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'user_id', 'grupo_id', 'materia_id', 'unidad', 
        'calificacion', 'registrado_por', 'observaciones'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
