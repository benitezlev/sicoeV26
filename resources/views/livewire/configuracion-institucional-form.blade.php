<div class="p-6 mt-10 sm:ml-64">
    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-3">
            <label>Nombre de la institución</label>
            <input type="text" wire:model.defer="config.nombre_institucion" class="form-control">
        </div>

        <div class="mb-3">
            <label>RFC</label>
            <input type="text" wire:model.defer="config.rfc" class="form-control">
        </div>

        <div class="mb-3">
            <label>Logo institucional</label>
            <input type="file" wire:model="logo" class="form-control">
            @if ($config->logo_path)
                <img src="{{ asset('storage/logos/' . $config->logo_path) }}" width="150">
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
