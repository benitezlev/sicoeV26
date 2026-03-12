<?php

namespace App\Livewire;

use App\Models\Plantel;
use Livewire\Component;

class CrearPlantel extends Component
{

    public $name;
    public $direccion;
    public $tel;
    public $titular;


    protected $rules = [
        'name' => 'required',
        'direccion' => 'required',
        'tel' => 'required',
        'titular' => 'required',
    ];

    public function crearPlantel()
    {
        $datos = $this->validate();

        Plantel::create([
            'name' => $datos['name'],
            'direccion' => $datos['direccion'],
            'tel' => $datos['tel'],
            'titular' => $datos['titular'],
        ]);

        session()->flash('mensaje','Plantel creado exitosamente');
        return redirect()->route('plantel.index');

    }


    public function render()
    {
        return view('livewire.crear-plantel');
    }
}
