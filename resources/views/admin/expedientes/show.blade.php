<x-app-layout>

    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-4 mt-10">

            <div class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded shadow">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    EXPEDIENTE DE: {{ $expediente->user->nombre_completo }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>CURP:</strong> {{ $expediente->user->curp }}</p>
                    <p><strong>Perfil:</strong> {{ $expediente->user->perfil }}</p>
                    <p><strong>Dependencia:</strong> {{ $expediente->user->adscripcion }}</p>

                    <p><strong>Estatus:</strong>
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            @class([
                                'bg-green-100 text-green-800' => $expediente->estatus === 'completo',
                                'bg-yellow-100 text-yellow-800' => $expediente->estatus === 'incompleto',
                                'bg-red-100 text-red-800' => $expediente->estatus === 'observado',
                            ])
                        ">
                            {{ ucfirst($expediente->estatus) }}
                        </span>
                    </p>
                </div>
            </div>

            <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-4">Documentos cargados</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-10">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Tipo</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Archivo</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Cargado por</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Fecha</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Estatus</th>
                            <th class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($expediente->documentos as $doc)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $doc->tipo }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        Ver documento
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $doc->cargador?->nombre_completo ?? 'Sistema' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($doc->fecha_carga)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @class([
                                            'bg-green-200 text-green-900' => $doc->estatus === 'validado',
                                            'bg-yellow-200 text-yellow-900' => $doc->estatus === 'pendiente',
                                            'bg-red-200 text-red-900' => $doc->estatus === 'observado',
                                        ])
                                    ">
                                        {{ ucfirst($doc->estatus) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm space-x-2">
                                    <a href="{{ route('documentos.validar', $doc->id) }}" class="text-green-600 hover:underline">Validar</a>
                                    <a href="{{ route('documentos.observar', $doc->id) }}" class="text-red-600 hover:underline">Observar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">

                <a href="{{ route('documentos.cargar', $expediente->id) }}">
                    <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Cargar nuevo documento</button>
                </a>
            </div>

        </div>
    </div>

</x-app-layout>
