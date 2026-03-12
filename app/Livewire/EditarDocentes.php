<?php

namespace App\Livewire;

use App\Models\Docente;
use Livewire\Component;

class EditarDocentes extends Component
{

    public $docente_id;
    public $name;
    public $cuid;
    public $sexo;
    public $curp;
    public $cuip;
    public $tel;
    public $email;
    public $cve_servidor;
    public $adscrip;
    public $plantel;
    public $cargo;
    public $puesto;
    public $ingreso;
    public $grado_estudio;
    public $acredita;
    public $cedula;
    public $campo_estudio;


    protected $rules =[
        'name' => 'required',
        'cuid' => 'required',
        'sexo' => 'required',
        'curp' => 'required',
        'cuip' => 'required',
        'tel' => 'required',
        'email' => 'required',
        'cve_servidor' => 'required',
        'adscrip' => 'required',
        'plantel' => 'required',
        'cargo' => 'required',
        'puesto' => 'required',
        'ingreso' => 'required',
        'grado_estudio' => 'required',
        'acredita' => 'required',
        'cedula' => 'required',
        'campo_estudio' => 'required',
    ];

    public function mount(Docente $docente)
    {
        $this->docente_id = $docente->id;
        $this->name = $docente-> name;
        $this->cuid = $docente-> cuid;
        $this->sexo = $docente-> sexo;
        $this->curp = $docente-> curp;
        $this->cuip = $docente-> cuip;
        $this->tel = $docente-> tel;
        $this->email = $docente-> email;
        $this->cve_servidor = $docente-> cve_servidor;
        $this->adscrip = $docente-> adscrip;
        $this->plantel = $docente-> plantel;
        $this->cargo = $docente-> cargo;
        $this->puesto = $docente-> puesto;
        $this->ingreso = $docente-> ingreso;
        $this->grado_estudio = $docente-> grado_estudio;
        $this->acredita = $docente-> acredita;
        $this->cedula = $docente-> cedula;
        $this->campo_estudio = $docente-> campo_estudio;

    }

    public function editarDocente()
    {
        $datos = $this->validate();

        $docente = Docente::find($this->docente_id);
            $docente->name = $datos['name'];
            $docente->cuid = $datos['cuid'];
            $docente->sexo = $datos['sexo'];
            $docente->curp = $datos['curp'];
            $docente->cuip = $datos['cuip'];
            $docente->tel = $datos['tel'];
            $docente->email = $datos['email'];
            $docente->cve_servidor = $datos['cve_servidor'];
            $docente->adscrip = $datos['adscrip'];
            $docente->plantel = $datos['plantel'];
            $docente->cargo = $datos['cargo'];
            $docente->puesto = $datos['puesto'];
            $docente->ingreso = $datos['ingreso'];
            $docente->grado_estudio = $datos['grado_estudio'];
            $docente->acredita = $datos['acredita'];
            $docente->cedula = $datos['cedula'];
            $docente->campo_estudio = $datos['campo_estudio'];

            $docente->save();
            session()->flash('mensaje','Docente Actualizado Correctamente');
            return redirect()->route('docentes');

    }

    public function render()
    {
        return view('livewire.editar-docentes');
    }
}
