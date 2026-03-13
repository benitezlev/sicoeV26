<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $fillable = ['nombre', 'clave'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
