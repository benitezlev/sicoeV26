<?php

namespace App\Livewire;

use App\Models\CardexDocente;
use App\Models\Plantel;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;



class CrearDocente extends Component
{

     public $nombre, $curp, $cuip, $email, $adscripcion, $dependencia, $tipo, $perfil;
    public $grado_academico, $especialidad, $experiencia_docente, $certificaciones, $formacion_complementaria, $observaciones;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'curp' => 'required|string|unique:users,curp',
        'email' => 'nullable|email|unique:users,email',
        'cuip' => 'nullable|string',
        'adscripcion' => 'nullable|string',
        'dependencia' => 'nullable|string',
        'tipo' => 'nullable|string',
        'perfil' => 'nullable|string',
        'grado_academico' => 'nullable|string',
        'especialidad' => 'nullable|string',
        'experiencia_docente' => 'nullable|string',
        'certificaciones' => 'nullable|string',
        'formacion_complementaria' => 'nullable|string',
        'observaciones' => 'nullable|string',
    ];

    public function registrar()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->nombre,
            'username' => strtolower($this->curp),
            'email' => $this->email ?? strtolower($this->curp) . '@sicoe.mx',
            'password' => Hash::make($this->curp),
            'curp' => $this->curp,
            'cuip' => $this->cuip,
            'adscripcion' => $this->adscripcion,
            'dependencia' => $this->dependencia,
            'tipo' => $this->tipo,
            'perfil' => $this->perfil,
        ]);

        $user->assignRole('docente');

        CardexDocente::create([
            'docente_id' => $user->id,
            'grado_academico' => $this->grado_academico,
            'especialidad' => $this->especialidad,
            'experiencia_docente' => $this->experiencia_docente,
            'certificaciones' => $this->certificaciones,
            'formacion_complementaria' => $this->formacion_complementaria,
            'observaciones' => $this->observaciones,
        ]);

        session()->flash('mensaje', 'Docente registrado correctamente.');
        $this->reset();
    }

    public function render()
    {
        $planteles = Plantel::all();
        return view('livewire.crear-docente', compact('planteles'));
    }
}
