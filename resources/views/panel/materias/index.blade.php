<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
                Panel de Materias por Curso
            </h2>

            {{-- Selector de curso --}}
            <form method="GET" action="{{ route('panel.materias') }}" class="mb-6 space-y-2">
                <label for="curso_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Selecciona un curso
                </label>
                <select name="curso_id" id="curso_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-200">
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                    Ver materias
                </button>
            </form>

            {{-- Tira académica --}}
            @if ($cursoSeleccionado)
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">
                    Tira académica: {{ $cursoSeleccionado->nombre }}
                </h3>

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
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($cursoSeleccionado->materias as $materia)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->pivot->orden }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->nombre }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->num_horas }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->pivot->semestre }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->pivot->creditos }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded text-xs font-medium
                                            @if($materia->pivot->obligatoria) bg-green-100 text-green-800 dark:bg-green-800 dark:text-white
                                            @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                            {{ $materia->pivot->obligatoria ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('panel.materias.edit', $cursoSeleccionado->id) }}"
                                           class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm transition-colors">
                                            Editar
                                        </a>
                                        <form method="POST"
                                              action="{{ route('panel.materias.remove', [$cursoSeleccionado->id, $materia->id]) }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow text-sm transition-colors">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Botones de acción --}}
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('panel.materias.add', $cursoSeleccionado->id) }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-colors">
                        Agregar materia
                    </a>

                    <a href="{{ route('panel.materias.export.pdf', $cursoSeleccionado->id) }}"
                       class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow transition-colors">
                        Exportar PDF
                    </a>

                    <a href="{{ route('panel.materias.export.excel', $cursoSeleccionado->id) }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-colors">
                        Exportar Excel
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
