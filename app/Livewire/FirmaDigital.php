<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;


class FirmaDigital extends Component
{

    use WithFileUploads;

    public $firma;
    public $firmaPreview;

    public function updatedFirma()
    {
        $this->firmaPreview = base64_encode(file_get_contents($this->firma->getRealPath()));
    }

    public function guardarFirma()
    {
        auth()->user()->update([
            'firma_digital' => $this->firmaPreview,
        ]);

        session()->flash('success', 'Firma digital registrada correctamente.');
    }


    public function render()
    {
        return view('livewire.firma-digital');
    }
}
