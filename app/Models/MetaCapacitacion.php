<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaCapacitacion extends Model
{
    protected $table = 'metas_capacitacion';

    protected $fillable = [
        'anio',
        'meta',
    ];

    protected $casts = [
        'anio' => 'integer',
        'meta' => 'integer',
    ];
}
