<?php

namespace App\Livewire;

use App\Models\Curso;
use Livewire\Component;
use App\Models\CursoMateria;


class PanelMaterias extends Component
{

     public $curso;
    public $materias = [];

    public function mount($cursoId)
    {
        $this->curso = \App\Models\Curso::with('materias')->findOrFail($cursoId);

        $this->materias = $this->curso->materias->map(function ($m) {
            return [
                'id' => $m->id,
                'nombre' => $m->nombre,
                'num_horas' => $m->num_horas,
                'orden' => $m->pivot->orden,
            ];
        })->toArray();
    }

    public function actualizarOrden($materias)
    {
        $this->materias = $materias;
    }

    public function guardarOrden()
    {
        foreach ($this->materias as $m) {
            CursoMateria::where('curso_id', $this->curso->id)
                ->where('materia_id', $m['id'])
                ->update(['orden' => $m['orden']]);
        }

        session()->flash('mensaje', 'Orden de materias actualizado correctamente.');
    }


    public function render()
    {
        return view('livewire.panel-materias');
    }
}
