<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                Agregar materia al curso: {{ $curso->nombre }}
            </h2>

            <form method="POST" action="{{ route('panel.materias.store', $curso->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="materia_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Materia</label>
                    <select name="materia_id" id="materia_id"
                            class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                        @foreach ($materiasDisponibles as $materia)
                            <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="orden" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Orden</label>
                    <input type="number" name="orden" id="orden"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div>
                    <label for="semestre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Semestre</label>
                    <input type="number" name="semestre" id="semestre"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div>
                    <label for="creditos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Créditos</label>
                    <input type="number" name="creditos" id="creditos"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div>
                    <label class="inline-flex items-center text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="obligatoria" value="1"
                               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800">
                        <span class="ml-2">Obligatoria</span>
                    </label>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-colors">
                    Guardar
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
