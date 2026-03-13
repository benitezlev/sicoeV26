<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioMovimiento extends Model
{
    protected $fillable = [
        'user_id', 'tipo_movimiento', 
        'nivel_anterior', 'perfil_data_anterior', 'plantel_id_anterior',
        'nivel_nuevo', 'perfil_data_nuevo', 'plantel_id_nuevo',
        'motivo', 'registrado_por'
    ];

    protected $casts = [
        'perfil_data_anterior' => 'array',
        'perfil_data_nuevo' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plantelAnterior()
    {
        return $this->belongsTo(Plantel::class, 'plantel_id_anterior');
    }

    public function plantelNuevo()
    {
        return $this->belongsTo(Plantel::class, 'plantel_id_nuevo');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
