<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Importar Alumnos desde CSV</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="importar">
        <input type="file" wire:model="archivo" class="mb-4">
        @error('archivo') <span class="text-red-500">{{ $message }}</span> @enderror

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Importar CSV
        </button>
    </form>
</div>
