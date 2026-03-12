<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
            Editar materias del curso: {{ $curso->nombre }}
        </h2>

        <form method="POST" action="{{ route('materias.update', $curso->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Orden</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Materia</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Horas</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Semestre</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Créditos</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Obligatoria</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($curso->materias as $materia)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-2">
                                    <input type="number" name="materias[{{ $materia->id }}][orden]" value="{{ $materia->pivot->orden }}"
                                           class="w-20 border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->nombre }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" name="materias[{{ $materia->id }}][num_horas]" value="{{ $materia->num_horas }}"
                                           class="w-24 border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="materias[{{ $materia->id }}][semestre]" value="{{ $materia->pivot->semestre }}"
                                           class="w-20 border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="materias[{{ $materia->id }}][creditos]" value="{{ $materia->pivot->creditos }}"
                                           class="w-20 border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="checkbox" name="materias[{{ $materia->id }}][obligatoria]" value="1"
                                           {{ $materia->pivot->obligatoria ? 'checked' : '' }}
                                           class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit"
                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                Guardar cambios
            </button>
        </form>
    </div>
</x-app-layout>
