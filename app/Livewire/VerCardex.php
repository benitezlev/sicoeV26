<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

class VerCardex extends Component
{
     public $docente;

    public function render()
    {
        $docente = $this->docente;
        $ingreso = Carbon::parse($this->docente->ingreso)->isoFormat('dddd DD MMMM YYYY');
        $hoy = Carbon::parse(now()->subDays(24));
        $inicio = Carbon::parse($docente->ingreso);
        $antiguedad = $hoy->longAbsoluteDiffForHumans($inicio);

        return view('livewire.ver-cardex', compact('docente', 'ingreso', 'antiguedad'));
    }
}
