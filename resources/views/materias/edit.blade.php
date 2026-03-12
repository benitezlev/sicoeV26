<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                Editar materia: {{ $materia->nombre }}
            </h2>

            <form method="POST" action="{{ route('materias.update', $materia->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="{{ $materia->nombre }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200" required>
                </div>

                <div>
                    <label for="clave" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clave</label>
                    <input type="text" name="clave" id="clave" value="{{ $materia->clave }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200" required>
                </div>

                <div>
                    <label for="num_horas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de horas</label>
                    <input type="number" name="num_horas" id="num_horas" value="{{ $materia->num_horas }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="3"
                              class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">{{ $materia->descripcion }}</textarea>
                </div>

                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                    <select name="tipo" id="tipo"
                            class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                        <option value="teorica" {{ $materia->tipo == 'teorica' ? 'selected' : '' }}>Teórica</option>
                        <option value="practica" {{ $materia->tipo == 'practica' ? 'selected' : '' }}>Práctica</option>
                        <option value="mixta" {{ $materia->tipo == 'mixta' ? 'selected' : '' }}>Mixta</option>
                    </select>
                </div>

                <div>
                    <label class="inline-flex items-center text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="activo" value="1" {{ $materia->activo ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800">
                        <span class="ml-2">Activo</span>
                    </label>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                    Actualizar
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
