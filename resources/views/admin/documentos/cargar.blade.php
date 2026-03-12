<x-app-layout>

    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-4 mt-10 max-w-2xl mx-auto">

            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6">
                Cargar documento para {{ $expediente->user->nombre_completo }}
            </h2>

            <div class="mb-6">
                <p><strong>CURP:</strong> {{ $expediente->user->curp }}</p>
                <p><strong>Perfil:</strong> {{ $expediente->user->perfil }}</p>
                <p><strong>Adscripción:</strong> {{ $expediente->user->adscripcion }}</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documentos.store', $expediente->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de documento</label>
                    <select name="tipo" id="tipo" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <option value="">Selecciona tipo</option>
                        <option value="ACTA">Acta</option>
                        <option value="CONSTANCIA">Constancia</option>
                        <option value="OFICIO">Oficio</option>
                        <option value="IDENTIFICACION">Identificación</option>
                    </select>
                </div>

                <div>
                    <label for="archivo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo</label>
                    <input type="file" name="archivo" id="archivo" required
                        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Se renombrará como <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">CURP_TIPO.ext</code>
                    </p>
                </div>

                <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    Subir documento
                </button>
            </form>

        </div>
    </div>
</x-app-layout>

