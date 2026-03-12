<div class="p-6 mt-10 sm:ml-64">
    <div class="p-4 mt-10">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300">Panel de Expedientes — Alumnos</h2>

            {{-- Si en el futuro se desea filtrar por estatus, aquí puede ir el formulario --}}
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
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $expediente->user->curp }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $expediente->user->nombre_completo }}</td>
                            <td class="px-4 py-2 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    @class([
                                        'bg-green-100 text-green-800' => $expediente->estatus === 'completo',
                                        'bg-yellow-100 text-yellow-800' => $expediente->estatus === 'incompleto',
                                        'bg-red-100 text-red-800' => $expediente->estatus === 'observado',
                                    ])
                                ">
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
                                        {{ $doc->tipo }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-sm space-x-2 text-gray-700 dark:text-gray-300">
                                <a href="{{ route('expedientes.show', $expediente->id) }}" class="text-blue-600 hover:underline">Ver</a>
                                <a href="{{ route('documentos.cargar', $expediente->id) }}" class="text-green-600 hover:underline">Cargar</a>
                                <a href="{{ route('expedientes.validar', $expediente->id) }}" class="text-indigo-600 hover:underline">Validar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

           <div class="mt-6">
                {{ $expedientes->onEachSide(1)->links('vendor.pagination.tailwind') }}
           </div>

        </div>

    </div>
</div>
