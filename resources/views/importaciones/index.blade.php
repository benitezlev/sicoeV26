<div class="p-6 mt-10 sm:ml-64">
    <div class="p-4 mt-10">
        <div class="flex items-center justify-center mb-4 rounded">
            <p class="text-2xl text-gray-600 dark:text-gray-500">
                <span>Historial de Importaciones</span>
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

            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400 border">
        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Usuario</th>
                <th class="px-4 py-2">Módulo</th>
                <th class="px-4 py-2">Archivo</th>
                <th class="px-4 py-2">Registros</th>
            </tr>
        </thead>
        <tbody>
            @forelse($importaciones as $imp)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $imp->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $imp->usuario->name }}</td>
                    <td class="px-4 py-2">{{ ucfirst($imp->modulo) }}</td>
                    <td class="px-4 py-2">{{ $imp->archivo }}</td>
                    <td class="px-4 py-2">{{ $imp->registros }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">No hay importaciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-6">
        {{ $importaciones->links() }}
    </div>



    </div>
</div>
