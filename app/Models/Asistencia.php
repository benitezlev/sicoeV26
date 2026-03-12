<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $fillable = [
        'grupo_id','plantel_id','archivo','estado',
        'fecha_inicio_real','fecha_validacion_externa',
        'subido_at','validado_at','validado_por'
    ];

    protected $casts = [
        'subido_at' => 'datetime',
        'validado_at' => 'datetime',
        'fecha_inicio_real' => 'datetime',
    ];

    // Relación con grupo
    public function grupo() {
        return $this->belongsTo(Grupo::class);
    }

    // Relación con plantel
    public function plantel() {
        return $this->belongsTo(Plantel::class);
    }

    // Relación con validador
    public function validador() {
        return $this->belongsTo(User::class, 'validado_por');
    }

    // Método para saber si está dentro del periodo de validación
    public function dentroPeriodoValidacion() {
        return $this->subido_at && Carbon::now()->lessThan($this->subido_at->addHours(3));
    }

}
