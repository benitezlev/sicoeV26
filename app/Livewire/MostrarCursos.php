<?php

namespace App\Livewire;

use App\Models\Curso;
use Livewire\Component;

class MostrarCursos extends Component
{



    public function render()
    {
        $cursos = Curso::all();
        return view('livewire.mostrar-cursos', compact('cursos'));
    }
}
