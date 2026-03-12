<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoExpediente extends Model
{
    protected $table = 'grupo_expediente';
    protected $fillable = [
        'grupo_id', 'tipo_documento', 'archivo', 'usuario_id'
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

}
