<div>
    <h2>Subir Firma Digital</h2>

    <input type="file" wire:model="firma" accept="image/*">
    @if($firmaPreview)
        <p>Previsualización:</p>
        <img src="data:image/png;base64,{{ $firmaPreview }}" style="height: 100px;">
    @endif

    <button wire:click="guardarFirma">Guardar Firma</button>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
</div>
