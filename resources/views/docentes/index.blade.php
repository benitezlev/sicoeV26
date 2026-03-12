<x-app-layout>
    <div class="p-6 mt-10 sm:ml-64">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">
            Listado de Docentes
        </h1>

        {{-- Tabla de docentes --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">ID</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nombre</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">CURP</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Email</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Plantel</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Cargo</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Puesto</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($docentes['data'] as $docente)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['id'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['name'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['curp'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['email'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['plantel'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['cargo'] }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $docente['puesto'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-600 dark:text-gray-400">
                                No hay registros disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6 flex justify-center space-x-2">
            @foreach($docentes['meta']['links'] as $link)
                @php
                    $label = is_array($link['label']) ? implode(' ', $link['label']) : $link['label'];
                    $queryParams = parse_url($link['url'] ?? '', PHP_URL_QUERY);
                    $localUrl = route('profesores') . ($queryParams ? '?' . $queryParams : '');
                @endphp

                @if(isset($link['url']) && $link['url'])
                    <a href="{{ $localUrl }}"
                       class="px-3 py-1 rounded-lg text-sm font-medium shadow-sm
                              {{ $link['active'] ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300' }}">
                        {!! $label !!}
                    </a>
                @else
                    <span class="px-3 py-1 rounded-lg text-sm bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                        {!! $label !!}
                    </span>
                @endif
            @endforeach
        </div>

        {{-- Info de registros --}}
        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
            Página <strong>{{ $docentes['meta']['current_page'][0] }}</strong> de
            <strong>{{ $docentes['meta']['last_page'][0] }}</strong>
            (Total: <strong>{{ $docentes['meta']['total'][0] }}</strong> registros)
        </div>
    </div>
</x-app-layout>
