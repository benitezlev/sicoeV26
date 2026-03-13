<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaIndividual extends Model
{
    protected $table = 'asistencias_individuales';

    protected $fillable = [
        'user_id',
        'grupo_id',
        'asistencia_id',
        'fecha',
        'estatus',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function asistenciaBatch()
    {
        return $this->belongsTo(Asistencia::class, 'asistencia_id');
    }
}
