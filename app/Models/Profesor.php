<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
   protected $fillable = [
        'externo_id',
        'nombre',
        'curp',
        'plantel_id',
        'estatus',
    ];

    public function plantel()
    {
        return $this->belongsTo(Plantel::class);
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

}
