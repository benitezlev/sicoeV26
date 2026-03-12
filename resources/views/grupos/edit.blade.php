<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                Editar grupo: {{ $grupo->nombre }}
            </h2>

            {{-- Mensajes --}}
            @if(session('mensaje'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded-lg">
                    {{ session('mensaje') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Formulario de actualización de datos básicos --}}
            <form method="POST" action="{{ route('grupos.update', $grupo->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del grupo</label>
                    <input type="text" name="nombre" id="nombre" value="{{ $grupo->nombre }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200" required>
                </div>

                <div>
                    <label for="periodo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</label>
                    <input type="text" name="periodo" id="periodo" value="{{ $grupo->periodo }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200" required>
                </div>

                <div>
                    <label for="plantel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plantel</label>
                    <input type="text" id="plantel" value="{{ $grupo->plantel->name }}"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg bg-gray-100 dark:bg-gray-700 dark:text-gray-300" readonly>
                </div>

                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                    <select name="estado" id="estado"
                            class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200">
                        <option value="activo" {{ $grupo->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="concluido" {{ $grupo->estado == 'concluido' ? 'selected' : '' }}>Concluido</option>
                        <option value="cancelado" {{ $grupo->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Inicio
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200"
                               value="{{ old('fecha_inicio', $grupo->fecha_inicio?->format('Y-m-d')) }}">
                    </div>

                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Fin
                        </label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200"
                               value="{{ old('fecha_fin', $grupo->fecha_fin?->format('Y-m-d')) }}">
                    </div>

                    <div>
                        <label for="hora_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora de Inicio
                        </label>
                        <input type="time" name="hora_inicio" id="hora_inicio"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200"
                               value="{{ old('hora_inicio', $grupo->hora_inicio) }}">
                    </div>

                    <div>
                        <label for="hora_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora de Fin
                        </label>
                        <input type="time" name="hora_fin" id="hora_fin"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200"
                               value="{{ old('hora_fin', $grupo->hora_fin) }}">
                    </div>
                </div>

                <div>
                    <label for="total_horas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Total de Horas
                    </label>
                    <input type="number" name="total_horas" id="total_horas" min="1"
                           class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200"
                           value="{{ old('total_horas', $grupo->total_horas) }}">
                </div>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors">
                    Actualizar grupo
                </button>
            </form>

            {{-- Asignación de docente vía API --}}
            <div class="mt-10">
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Asignar docente</h3>
                <form method="POST" action="{{ route('grupos.asignarDocente', $grupo->id) }}" class="space-y-4">
                    @csrf
                    <label for="docente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selecciona un docente del plantel {{ $grupo->plantel->name }}
                    </label>
                    <select name="docente_id" id="docente_id"
                            class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-200" required>
                        @forelse($docentes['data'] as $docente)
                            <option value="{{ $docente['id'] }}">
                                {{ $docente['name'] }} ({{ $docente['email'] }})
                            </option>
                        @empty
                            <option disabled>No hay docentes disponibles para el plantel {{ $grupo->plantel->name }}</option>
                        @endforelse
                    </select>
                    @if(empty($docentes['data']))
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            Este plantel aún no tiene docentes registrados en el sistema SAD.
                            Contacta al área académica para registrar docentes antes de asignarlos.
                        </p>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition-colors">
                        Asignar docente
                    </button>
                </form>
            </div>

            {{-- Asignación de alumnos --}}
            <div class="mt-10">
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Asignar alumnos</h3>
                <form method="POST" action="{{ route('grupos.asignarAlumnos', $grupo->id) }}" class="space-y-4">
                    @csrf
                    <label for="alumnos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona alumnos</label>
                    <select name="alumnos[]" id="alumnos" multiple
                            class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-gray-200">
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}">
                                {{ $alumno->nombre }} {{ $alumno->paterno }} {{ $alumno->materno }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-colors">
                        Asignar alumnos
                    </button>
                </form>
            </div>

            {{-- Expediente del grupo --}}
            <div class="mt-10">
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Expediente del grupo</h3>
                <form method="POST" action="{{ route('grupos.subirExpediente', $grupo->id) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="tipo_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de documento</label>
                        <input type="text" name="tipo_documento" id="tipo_documento"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-800 dark:text-gray-200" required>
                    </div>
                    <div>
                        <label for="archivo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo (PDF/Excel)</label>
                        <input type="file" name="archivo" id="archivo"
                               class="w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-800 dark:text-gray-200" required>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg shadow transition-colors">
                        Subir documento
                    </button>
                </form>

                <div class="mt-6">
                    <h4 class="text-md font-semibold mb-2 text-gray-800 dark:text-gray-100">Documentos existentes</h4>
                    <ul class="list-disc ml-6 space-y-2 text-gray-700 dark:text-gray-300">
                        @foreach($grupo->expediente as $doc)
                            <li>
                                {{ $doc->tipo_documento }} -
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $doc->archivo }} ({{ $doc->created_at->format('d/m/Y') }})
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
