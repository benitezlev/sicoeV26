<x-app-layout>
    <div  class="p-6 mt-10 sm:ml-64">

            <div class="p-4 mt-10">
                <div class="flex items-center justify-center mb-4 rounded ">
                    <p class="text-2xl text-gray-600 dark:text-gray-500">
                    <span>Vista Curso</span>
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

                    <div class="max-w-lg p-6 mx-auto bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <p class="mb-3 font-bold text-gray-700 dark:text-gray-400"><strong>Identificador:</strong> {{ $curso->identificador }}</p>
                        <p class="mb-3 font-bold text-gray-700 dark:text-gray-400"><strong>Nombre:</strong> {{ $curso->nombre }}</p>
                        <p class="mb-3 font-bold text-gray-700 dark:text-gray-400"><strong>Tipo:</strong> {{ $curso->tipo }}</p>
                        <p class="mb-3 font-bold text-gray-700 dark:text-gray-400"><strong>Número de horas:</strong> {{ $curso->num_horas }}</p>

                        <a class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" href="{{ route('cursos.edit', $curso) }}">✏️ Editar</a>
                        <a class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" href="{{ route('cursos.index') }}">⬅️ Volver al listado</a>

                    </div>
            </div>

    </div>

</x-app-layout>
