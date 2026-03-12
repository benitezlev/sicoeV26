<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
                Listado de Materias
            </h2>

            {{-- Mensajes --}}
            @if(session('mensaje'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded-lg">
                    {{ session('mensaje') }}
                </div>
            @endif

            {{-- Botón registrar materia --}}
            <div class="mb-4">
                <a href="{{ route('materias.create') }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                    Registrar nueva materia
                </a>
            </div>

            {{-- Tabla de materias --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Clave</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nombre</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Horas</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tipo</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Activo</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($materias as $materia)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->clave }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->nombre }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $materia->num_horas }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ ucfirst($materia->tipo) }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        @if($materia->activo) bg-green-100 text-green-800 dark:bg-green-800 dark:text-white
                                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                        {{ $materia->activo ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 space-x-2">
                                    <a href="{{ route('materias.edit', $materia->id) }}"
                                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm transition-colors">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('materias.destroy', $materia->id) }}" class="inline">
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

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $materias->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
