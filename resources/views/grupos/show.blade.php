<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <div class="p-6 mt-10 bg-white dark:bg-gray-900 rounded-lg shadow-lg transition-colors duration-300">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100">
                Detalle del grupo: {{ $grupo->nombre }}
            </h2>

            {{-- Datos básicos del grupo --}}
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                <p><span class="font-semibold">Curso:</span> {{ $grupo->curso->nombre }}</p>
                <p><span class="font-semibold">Plantel:</span> {{ $grupo->plantel->name }}</p>
                <p><span class="font-semibold">Periodo:</span> {{ $grupo->periodo }}</p>
                <p><span class="font-semibold">Estado:</span>
                    <span class="px-2 py-1 rounded text-sm
                        @if($grupo->estado === 'activo') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                        @elseif($grupo->estado === 'concluido') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                        {{ ucfirst($grupo->estado) }}
                    </span>
                </p>
                <p><span class="font-semibold">Fecha de Inicio:</span> {{ $grupo->fecha_inicio?->format('d/m/Y') ?? '---' }}</p>
                <p><span class="font-semibold">Fecha de Fin:</span> {{ $grupo->fecha_fin?->format('d/m/Y') ?? '---' }}</p>
                <p><span class="font-semibold">Horario:</span> {{ $grupo->hora_inicio ?? '--:--' }} - {{ $grupo->hora_fin ?? '--:--' }}</p>
                <p><span class="font-semibold">Total de Horas:</span> {{ $grupo->total_horas ?? '---' }}</p>
                <p class="md:col-span-2"><span class="font-semibold">Docente asignado:</span>
                    @if($grupo->docente_id && $docenteNombre)
                        {{ $docenteNombre }}
                    @else
                        <span class="text-gray-500 dark:text-gray-400">No asignado</span>
                    @endif
                </p>
            </div>

            {{-- Asignación de alumnos --}}
        <div class="mt-10">
            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Asignar alumnos</h3>
            <form method="POST" action="{{ route('grupos.asignarAlumnos', $grupo->id) }}" class="space-y-4">
                @csrf
                <label for="alumnos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Selecciona alumnos
                </label>
                <select name="alumnos[]" id="alumnos" multiple
                        class="mt-1 w-full border-gray-300 dark:border-gray-700 rounded-lg shadow-sm
                            focus:ring-green-500 focus:border-green-500 dark:bg-gray-800 dark:text-gray-200">
                    @foreach($alumnos as $alumno)
                        <option value="{{ $alumno->id }}">
                            {{ $alumno->paterno }} {{ $alumno->materno }} {{ $alumno->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-colors">
                    Asignar alumnos
                </button>
            </form>
        </div>


            {{-- Expediente del grupo --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Expediente</h3>
                @if($grupo->expediente->count() > 0)
                    <ul class="list-disc ml-6 space-y-2 text-gray-700 dark:text-gray-300">
                        @foreach($grupo->expediente as $doc)
                            <li>
                                {{ $doc->tipo_documento }} -
                                <a href="{{ Storage::url($doc->archivo) }}" target="_blank"
                                   class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Descargar
                                </a>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    ({{ $doc->created_at->format('d/m/Y') }})
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No hay documentos en el expediente.</p>
                @endif
            </div>

            {{-- Botones de acción --}}
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('grupos.edit', $grupo->id) }}"
                   class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg shadow transition-colors">
                   Editar grupo
                </a>
                <a href="{{ route('grupos.index') }}"
                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow transition-colors">
                   Volver al listado
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
