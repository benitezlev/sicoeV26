<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{

    protected $table = "importaciones";

    protected $fillable = [
        'modulo', 'archivo', 'user_id', 'registros',  'duplicados', 'errores',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
