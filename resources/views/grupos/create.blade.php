<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">
                Crear nuevo grupo
            </h2>

            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('grupos.store') }}" class="space-y-6">
                @csrf

                {{-- Nombre --}}
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre del grupo
                    </label>
                    <input type="text" name="nombre" id="nombre"
                           class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('nombre') }}" required>
                </div>

                {{-- Plantel --}}
                <div>
                    <label for="plantel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Plantel
                    </label>
                    <select name="plantel_id" id="plantel_id"
                            class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">Seleccione un plantel</option>
                        @foreach($planteles as $plantel)
                            <option value="{{ $plantel->id }}" {{ old('plantel_id') == $plantel->id ? 'selected' : '' }}>
                                {{ $plantel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Curso --}}
                <div>
                    <label for="curso_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Curso
                    </label>
                    <select name="curso_id" id="curso_id"
                            class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">Seleccione un curso</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Periodo --}}
                <div>
                    <label for="periodo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Periodo
                    </label>
                    <input type="text" name="periodo" id="periodo" placeholder="2026-I"
                           class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('periodo') }}" required>
                </div>

                {{-- Fechas y horario --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Inicio
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('fecha_inicio') }}">
                    </div>

                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Fin
                        </label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('fecha_fin') }}">
                    </div>

                    <div>
                        <label for="hora_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora de Inicio
                        </label>
                        <input type="time" name="hora_inicio" id="hora_inicio"
                               class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('hora_inicio') }}">
                    </div>

                    <div>
                        <label for="hora_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora de Fin
                        </label>
                        <input type="time" name="hora_fin" id="hora_fin"
                               class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('hora_fin') }}">
                    </div>
                </div>

                {{-- Total de horas --}}
                <div>
                    <label for="total_horas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Total de Horas
                    </label>
                    <input type="number" name="total_horas" id="total_horas" min="1"
                           class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('total_horas') }}">
                </div>

                {{-- Botón --}}
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-colors">
                        Crear grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
