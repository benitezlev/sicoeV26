<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = [
        'expediente_id',
        'name',
        'vigencia',
        'image',
    ];

    protected $date = [
        'vigencia',
    ];

    public function vigencia_doc()
    {
        $fueature_day = Carbon::parse($this->vigencia);
        $hoy = Carbon::parse(now()->subDays(24));

        if (isset($this->vigencia)) {
            $doc_vigencia = $hoy->longAbsoluteDiffForHumans($fueature_day);
        }else {

            $doc_vigencia = "No Aplica";
        }
        return $doc_vigencia;
    }
}
