<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class MostrarRol extends Component
{
    public function render()
    {
        $roles = Role::all();
        return view('livewire.mostrar-rol', compact('roles'));
    }
}
