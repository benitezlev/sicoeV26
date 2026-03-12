<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    protected $table = "certificaciones";
     protected $fillable = [
        'codigo',
        'name',
        'nivel',

    ];
}
