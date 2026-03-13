<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plantel extends Model
{
    protected $table = "planteles";

    protected $fillable = [
        'name',
        'direccion',
        'tel',
        'titular',
    ];

    public function configuracion() : BelongsTo
    {
        return $this->belongsTo(ConfiguracionInstitucional::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
