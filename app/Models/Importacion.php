<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Importacion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "importaciones";

    protected $fillable = [
        'modulo', 'archivo', 'user_id', 'registros',  'duplicados', 'errores',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
