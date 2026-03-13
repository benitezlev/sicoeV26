<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoRequerido extends Model
{
    protected $table = 'documentos_requeridos';
     protected $fillable = ['perfil', 'tipo', 'nivel', 'descripcion'];

}
