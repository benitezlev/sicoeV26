<?php

namespace App\Livewire;


use Livewire\Component;
use Spatie\Permission\Models\Role;

class CrearRol extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required',
    ];


    public function crearRol()
    {
        $datos = $this->validate();

        Role::create([
            'name' => $datos['name'],
        ]);

         session()->flash('mensaje','Rol creado exitosamente');
        return redirect()->route('roles');


    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.crear-rol', compact('roles'));
    }
}
