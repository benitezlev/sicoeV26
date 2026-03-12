<x-app-layout>

    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-4 mt-10 max-w-2xl mx-auto">

            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6">
                Observar documento: {{ $documento->tipo }} de {{ $documento->user->nombre_completo }}
            </h2>

            <div class="mb-4">
                <p><strong>CURP:</strong> {{ $documento->user->curp }}</p>
                <p><strong>Archivo:</strong>
                    <a href="{{ asset('storage/' . $documento->archivo) }}" target="_blank" class="text-blue-600 hover:underline">
                        Ver documento
                    </a>
                </p>
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

            <form action="{{ route('documentos.observacion.store', $documento->id) }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="4" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">{{ old('observaciones') }}</textarea>
                </div>

                <x-secondary-button>
                    Registrar observación
                </x-secondary-button>
            </form>

        </div>
    </div>

</x-app-layout>


