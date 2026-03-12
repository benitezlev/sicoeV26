<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class AlumnoImport extends Component
{
    use WithFileUploads;
    public $archivo;

     public function importar()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:csv,xlsx',
        ]);

        Excel::import(new AlumnoImport, $this->archivo->getRealPath());

        session()->flash('success', 'Importación completada con trazabilidad.');
    }



    public function render()
    {
        return view('livewire.alumno-import');
    }
}
