<?php

namespace App\Livewire;

use App\Models\Docente;
use Livewire\Component;

class CrearDocentes extends Component
{

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

    protected $rules = [

        'name' => 'required',
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


    public function crearPersonal()
    {
        $datos = $this->validate();

        if (isset($this->plantel)) {
            if ($this->plantel === "Lerma") {
                $cuid1 = "01LERM".$this->cve_servidor ;
            }if ($this->plantel === "Plantel de Formación y Actualización Toluca") {
                $cuid1 = "02PFATOL".$this->cve_servidor ;
            }if ($this->plantel === "Plantel de Formación y Actualización Tlalnepantla") {
                $cuid1 = "02PFATLAL".$this->cve_servidor ;
            }if ($this->plantel === "Plantel de Formación y Actualización Nezahualcóyotl") {
                $cuid1 = "02PFANZA".$this->cve_servidor ;
            }

            Docente::create([
                'name' => $datos['name'],
                'cuid' => $cuid1,
                'sexo' => $datos['sexo'],
                'curp' => $datos['curp'],
                'cuip' => $datos['cuip'],
                'tel' => $datos['tel'],
                'email' => $datos['email'],
                'cve_servidor' => $datos['cve_servidor'],
                'adscrip' => $datos['adscrip'],
                'plantel' => $datos['plantel'],
                'cargo' => $datos['cargo'],
                'puesto' => $datos['puesto'],
                'ingreso' => $datos['ingreso'],
                'grado_estudio' => $datos['grado_estudio'],
                'acredita' => $datos['acredita'],
                'cedula' => $datos['cedula'],
                'campo_estudio' => $datos['campo_estudio'],

        ]);

        session()->flash('mensaje','Docente Creado Correctamente');
        return redirect()->route('docentes');
        }


    }


    public function render()
    {
        return view('livewire.crear-docentes');
    }
}
