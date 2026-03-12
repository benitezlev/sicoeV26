<div class="p-6 mt-10 sm:ml-64">
    <div class="p-4 mt-10">
        <div class="flex items-center justify-center mb-4 rounded">
            <p class="text-2xl text-gray-600 dark:text-gray-500">
                <span>Importar Alumnos</span>
            </p>
        </div>

        @if (session('mensaje'))
            <div id="toast-success"
                class="flex justify-end w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800"
                role="alert">
                <div
                    class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                    </svg>
                </div>
                <div class="text-sm font-normal ms-3">{{ session('mensaje') }}</div>
                <button type="button" class="ms-auto p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                    data-dismiss-target="#toast-success" aria-label="Close">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('duplicados') && count(session('duplicados')) > 0)
            <div class="mt-6 bg-red-50 border border-red-200 p-4 rounded text-sm text-red-700 max-w-4XL">
                <strong>⚠️ CURP duplicados detectados ({{ count(session('duplicados')) }}):</strong>

                    <ul class="mt-2 list-disc list-inside max-h-64 overflow-y-auto">
                        @foreach (session('duplicados') as $curp)
                            <li>{{ $curp }}</li>
                        @endforeach
                    </ul>



                <form action="{{ route('alumnos.exportar.duplicados') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                        📄 Exportar duplicados
                    </button>
                </form>
            </div>
        @endif

        @if (session('errores') && count(session('errores')) > 0)
            <div class="mt-6 bg-yellow-50 border border-yellow-200 p-4 rounded text-sm text-yellow-800 max-w-4XL">
                <strong>❌ Errores durante la importación ({{ count(session('errores')) }}):</strong>
                <ul class="mt-2 list-disc list-inside max-h-64 overflow-y-auto">
                    @foreach (session('errores') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <form action="{{ route('alumnos.exportar.errores') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 text-sm">
                        📄 Exportar errores
                    </button>
                </form>

            </div>
        @endif

        <div class="max-w-4xl mx-auto mt-10">
            <form action="{{ route('alumnos.importar') }}" method="POST" enctype="multipart/form-data"
                class="bg-white shadow rounded p-6 space-y-4">
                @csrf

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="archivo">
                        Archivo CSV o Excel:
                    </label>
                    <input type="file" name="archivo" id="archivo" accept=".csv,.xlsx"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    @error('archivo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Importar Alumnos
                </button>
            </form>

            <div class="mt-8 text-md text-gray-500">
                <p>📄 Asegúrate que el archivo contenga los encabezados requeridos:
                    <code>curp, cuip, nombre, dependencia, sexo...</code>
                </p>
                <p>📍 Se ignorarán filas duplicadas por CURP o incompletas.</p>
            </div>
        </div>
    </div>
</div>
