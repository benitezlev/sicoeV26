<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
                Listado de Grupos
            </h2>

            {{-- Mensajes --}}
            @if(session('mensaje'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded-lg">
                    {{ session('mensaje') }}
                </div>
            @endif

            {{-- Botón crear grupo --}}
            <div class="mb-4">
                <a href="{{ route('grupos.create') }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                    Crear nuevo grupo
                </a>
            </div>

            {{-- Tabla de grupos --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Nombre</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Plantel</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Curso</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Periodo</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Inicio</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Fin</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Horario</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Horas</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Estado</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($grupos as $grupo)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $grupo->nombre }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $grupo->plantel->name }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $grupo->curso->nombre }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $grupo->periodo }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $grupo->fecha_inicio?->format('d/m/Y') ?? '---' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $grupo->fecha_fin?->format('d/m/Y') ?? '---' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $grupo->hora_inicio ?? '--:--' }} - {{ $grupo->hora_fin ?? '--:--' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $grupo->total_horas ?? '---' }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        @if($grupo->estado === 'activo') bg-green-100 text-green-800 dark:bg-green-800 dark:text-white
                                        @elseif($grupo->estado === 'concluido') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                        {{ ucfirst($grupo->estado) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col space-y-2">
                                        {{-- Botón ver --}}
                                        <a href="{{ route('grupos.show', $grupo->id) }}"
                                           class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm text-center">
                                            Ver
                                        </a>

                                        {{-- Botón métricas --}}
                                        <a href="{{ route('grupos.metricas', $grupo->id) }}"
                                           class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm text-center">
                                            Métricas
                                        </a>

                                        {{-- Botón editar --}}
                                        <a href="{{ route('grupos.edit', $grupo->id) }}"
                                           class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg shadow text-sm text-center">
                                            Editar
                                        </a>

                                        {{-- Botón generar lista PDF --}}
                                        <a href="{{ route('asistencias.generar', $grupo->id) }}"
                                           class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow text-sm text-center">
                                            Generar Lista PDF
                                        </a>

                                        {{-- Botón subir lista escaneada --}}
                                        <form action="{{ route('asistencias.subir', $grupo->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <label class="block px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow text-sm cursor-pointer text-center">
                                                Subir Lista
                                                <input type="file" name="archivo" class="hidden" onchange="this.form.submit()">
                                            </label>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $grupos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
