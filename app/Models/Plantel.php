<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Plantel extends Model implements HasMedia
{
    use InteractsWithMedia;
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
