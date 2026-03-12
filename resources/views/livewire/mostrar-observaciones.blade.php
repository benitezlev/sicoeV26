<div class="p-6 mt-10 sm:ml-64">
    <div class="p-4 mt-10">
        <div class="flex items-center justify-center mb-4 rounded ">
            <p class="text-2xl text-gray-600 dark:text-gray-500">
               <span>Observaciones en el expediente</span>
            </p>
         </div>

                @if (session()->has('mensaje'))

                <div id="toast-success" class="flex justify-end w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        <span class="sr-only">Check icon</span>
                    </div>
                    <div class="text-sm font-normal ms-3">{{session('mensaje')}}</div>
                    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>

                @endif

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">CURP</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Nombre</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Documento</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Observación</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Fecha</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">Responsable</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($expedientes as $expediente)
                                    @foreach($expediente->documentos->where('estatus', 'observado') as $doc)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $expediente->user->curp }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $expediente->user->nombre_completo }}
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    {{ $doc->tipo }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $doc->observaciones ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $doc->updated_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $doc->observado_por ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-6">
                            {{ $expedientes->links() }}
                        </div>
                    </div>
                </div>

    </div>

</div>

