<div x-data="{
        materias: @entangle('materias').defer,
        actualizarOrden() {
            this.materias.forEach((m, index) => m.orden = index + 1);
            $wire.actualizarOrden(this.materias);
        }
    }"
    class="p-6">

    <h3 class="text-lg font-bold mb-4">Tira académica: {{ $curso->nombre }}</h3>

    <ul class="space-y-2" x-sortable x-on:end="actualizarOrden()">
        @foreach($materias as $materia)
            <li x-sortable-item="{{ $materia['id'] }}"
                class="p-4 bg-white dark:bg-gray-800 border rounded shadow cursor-move">
                <span class="font-semibold">{{ $materia['nombre'] }}</span>
                <span class="ml-2 text-sm text-gray-500">({{ $materia['num_horas'] }} hrs)</span>
            </li>
        @endforeach
    </ul>

    <div class="mt-4">
        <button wire:click="guardarOrden"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Guardar orden
        </button>
    </div>
</div>
