<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ConfiguracionInstitucional;



class ConfiguracionInstitucionalForm extends Component
{
        use WithFileUploads;

     public $config;
     public $logo;

 


    public function mount()
    {
        $this->config = ConfiguracionInstitucional::firstOrNew();
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'config.nombre_institucion' => 'required|string|max:255',
            'config.rfc' => 'nullable|string|max:13',
            'config.logo_path' => 'nullable|string',
            // otros campos...
        ]);
    }

    public function save()
    {

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $this->config->logo_path = basename($path);
        }

        $this->validate([
            'config.nombre_institucion' => 'required|string|max:255',
            // otros campos...
        ]);

        $this->config->updated_by = auth()->id();
        $this->config->save();

        session()->flash('success', 'Configuración actualizada correctamente.');
    }


    public function render()
    {
        return view('livewire.configuracion-institucional-form');
    }
}
