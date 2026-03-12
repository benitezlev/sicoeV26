<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-4 mt-10">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300">
                    Panel de Expedientes — Alumnos
                </h2>

                {{-- Espacio reservado para filtros por estatus o tipo documental --}}
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">CURP</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Nombre</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Estatus</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Documentos</th>
                            <th class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @foreach($expedientes as $expediente)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $expediente->user->curp }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $expediente->user->nombre_completo }}
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="@class([
                                        'inline-block text-xs font-semibold px-2.5 py-0.5 rounded-sm',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $expediente->estatus === 'completo',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $expediente->estatus === 'incompleto',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $expediente->estatus === 'observado',
                                    ])">
                                        {{ ucfirst($expediente->estatus) }}
                                    </span>

                                </td>

                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    @foreach($expediente->documentos as $doc)
                                        <span class="inline-block px-2 py-1 mr-1 rounded text-xs font-medium
                                            @class([
                                                'bg-green-200 text-green-900' => $doc->estatus === 'validado',
                                                'bg-yellow-200 text-yellow-900' => $doc->estatus === 'pendiente',
                                                'bg-red-200 text-red-900' => $doc->estatus === 'observado',
                                            ])
                                        ">
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">{{ $doc->tipo }}</span>
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('expedientes.show', $expediente->id) }}"
                                            class="inline-block px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800 hover:bg-blue-200">
                                            Ver
                                        </a>

                                        <a href="{{ route('documentos.cargar', $expediente->id) }}"
                                            class="inline-block px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 hover:bg-green-200">
                                            Cargar
                                        </a>

                                        @if($expediente->documentos->isNotEmpty() && $expediente->documentos->every(fn($doc) => $doc->estatus === 'validado'))
                                            <form method="POST" action="{{ route('expedientes.validar', $expediente->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('¿Deseas validar este expediente?')"
                                                    class="inline-block px-2 py-1 rounded text-xs font-semibold bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                                                    Validar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Paginación institucional --}}
                <div class="mt-6">
                    {{ $expedientes->links() }}

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
