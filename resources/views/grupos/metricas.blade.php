<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                Métricas del grupo: {{ $grupo->nombre }}
            </h2>

            {{-- Datos básicos --}}
            <div class="space-y-2 text-gray-700 dark:text-gray-300">
                <p><span class="font-semibold">Curso:</span> {{ $grupo->curso->nombre }}</p>
                <p><span class="font-semibold">Plantel:</span> {{ $grupo->plantel->nombre }}</p>
                <p><span class="font-semibold">Periodo:</span> {{ $grupo->periodo }}</p>
            </div>

            {{-- Indicadores --}}
            <div class="mt-8">
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Indicadores</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total de alumnos</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $totalAlumnos }}</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Documentos en expediente</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $documentos }}</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cursos programados</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $cursosProgramados }}</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cursos impartidos</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $cursosImpartidos }}</p>
                    </div>
                </div>
            </div>

            {{-- Botón volver --}}
            <div class="mt-8">
                <a href="{{ route('grupos.index') }}"
                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow transition-colors">
                    Volver al listado
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
