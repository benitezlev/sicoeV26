<?php

use function Livewire\Volt\{state, computed, layout, mount, usesFileUploads};
use App\Models\Grupo;
use App\Models\User;
use App\Models\GrupoExpediente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();

layout('layouts.app');

state([
    'grupoId' => null,
    'docente' => null,
    'searchAlumnos' => '',
    'selectedAlumnos' => [],
    'tipoDocumento' => '',
    'archivo' => null,
    'docentesAPI' => [],
    'searchDocente' => '',
    'escaneoAsistencia' => null,
]);

mount(function ($grupo) {
    $this->grupoId = is_numeric($grupo) ? $grupo : $grupo->id;
    $this->cargarDocente();
});

$grupo = computed(function () {
    return Grupo::with(['plantel', 'curso', 'alumnos', 'expediente'])->findOrFail($this->grupoId);
});

$cargarDocente = function () {
    $grupo = Grupo::find($this->grupoId);
    if ($grupo && $grupo->docente_id) {
        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url') . '/docentes/' . $grupo->docente_id);
        
        if ($response->successful()) {
            $data = $response->json();
            $this->docente = $data['data'] ?? $data;
        }
    }
};

$alumnosDisponibles = computed(function () {
    $inscritosIds = $this->grupo->alumnos->pluck('id')->toArray();
    return User::where('tipo', 'alumno')
        ->whereNotIn('id', $inscritosIds)
        ->when($this->searchAlumnos, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->searchAlumnos . '%')
                  ->orWhere('paterno', 'like', '%' . $this->searchAlumnos . '%')
                  ->orWhere('materno', 'like', '%' . $this->searchAlumnos . '%')
                  ->orWhere('curp', 'like', '%' . $this->searchAlumnos . '%');
            });
        })
        ->limit(10)
        ->get();
});

$asignarAlumnos = function () {
    if (empty($this->selectedAlumnos)) {
        Flux::toast(heading: 'Error', text: 'Selecciona al menos un alumno.', variant: 'danger');
        return;
    }

    $grupo = Grupo::find($this->grupoId);
    foreach ($this->selectedAlumnos as $alumnoId) {
        $grupo->alumnos()->syncWithoutDetaching([
            $alumnoId => ['fecha_asignacion' => now(), 'estado' => 'activo']
        ]);
    }

    $this->reset(['selectedAlumnos', 'searchAlumnos']);
    Flux::toast(heading: 'Alumnos asignados', variant: 'success');
};

$desvincularAlumno = function ($alumnoId) {
    $this->grupo->alumnos()->detach($alumnoId);
    Flux::toast(heading: 'Alumno removido del grupo', variant: 'warning');
};

$buscarDocentesAPI = function () {
    $response = Http::withToken(config('services.sad.token'))
        ->get(config('services.sad.url') . '/docentes', [
            'plantel' => $this->grupo->plantel->name,
            'search' => $this->searchDocente,
            'per_page' => 10
        ]);

    if ($response->successful()) {
        $this->docentesAPI = $response->json()['data'] ?? [];
    }
};

$asignarDocente = function ($docenteId) {
    $grupo = Grupo::find($this->grupoId);
    $grupo->docente_id = $docenteId;
    $grupo->save();

    $this->cargarDocente();
    $this->dispatch('modal-hide', name: 'modal-asignar-docente');
    Flux::toast(heading: 'Docente asignado correctamente', variant: 'success');
};

$subirDocumento = function () {
    $this->validate([
        'tipoDocumento' => 'required|string',
        'archivo' => 'required|file|max:10240', // 10MB
    ]);

    $path = $this->archivo->store('expedientes_grupos', 'public');

    GrupoExpediente::create([
        'grupo_id' => $this->grupoId,
        'tipo_documento' => $this->tipoDocumento,
        'archivo' => $path,
        'usuario_id' => auth()->id(),
    ]);

    $this->reset(['tipoDocumento', 'archivo']);
    Flux::toast(heading: 'Documento cargado al expediente', variant: 'success');
};

$subirAsistencia = function () {
    $this->validate([
        'escaneoAsistencia' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
    ]);

    $path = $this->escaneoAsistencia->store('asistencias', 'public');

    App\Models\Asistencia::create([
        'grupo_id' => $this->grupoId,
        'plantel_id' => $this->grupo->plantel_id,
        'archivo' => $path,
        'estado' => 'pendiente',
        'subido_at' => now(),
        'fecha_inicio_real' => now(),
    ]);

    $this->reset('escaneoAsistencia');
    $this->dispatch('modal-hide', name: 'modal-subir-asistencia');
    Flux::toast(heading: 'Lista enviada', text: 'Tienes 3 horas para que un coordinador la valide.', variant: 'success');
};

?>

<div class="space-y-6">
    <x-slot name="header">Detalle del Grupo</x-slot>

    <!-- Encabezado y Acciones Rápidas -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <flux:button icon="chevron-left" variant="ghost" href="{{ route('grupos.index') }}" />
            <div>
                <flux:heading size="xl">{{ $this->grupo->nombre }}</flux:heading>
                <flux:subheading>{{ $this->grupo->curso->nombre }} | {{ $this->grupo->plantel->name }}</flux:subheading>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="chart-bar" href="{{ route('grupos.metricas', $this->grupo->id) }}">Métricas</flux:button>
            <flux:button variant="ghost" icon="document-text" href="{{ route('asistencias.generar', $this->grupo->id) }}">Lista Asistencia</flux:button>
            <flux:button variant="ghost" icon="arrow-up-tray" wire:click="$dispatch('modal-show', { name: 'modal-subir-asistencia' })">Subir Lista</flux:button>
            <flux:badge :color="match($this->grupo->state) { 'activo' => 'green', 'concluido' => 'blue', default => 'zinc' }">
                {{ ucfirst($this->grupo->estado) }}
            </flux:badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel Izquierdo: Información y Alumnos -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Datos del Grupo -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase text-zinc-400 font-bold">Periodo</span>
                    <span class="font-medium">{{ $this->grupo->periodo }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase text-zinc-400 font-bold">Horas Totales</span>
                    <span class="font-medium">{{ $this->grupo->total_horas }} hrs</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase text-zinc-400 font-bold">Fecha Inicio</span>
                    <span class="font-medium">{{ $this->grupo->fecha_inicio?->format('d/m/Y') }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase text-zinc-400 font-bold">Fecha Fin</span>
                    <span class="font-medium">{{ $this->grupo->fecha_fin?->format('d/m/Y') }}</span>
                </div>
                <div class="flex flex-col md:col-span-2">
                    <span class="text-[10px] uppercase text-zinc-400 font-bold">Horario</span>
                    <span class="font-medium">{{ $this->grupo->hora_inicio }} - {{ $this->grupo->hora_fin }}</span>
                </div>
            </div>

            <!-- Listado de Alumnos -->
            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <flux:heading size="lg">Alumnos Inscritos ({{ $this->grupo->alumnos->count() }})</flux:heading>
                    <flux:button variant="primary" size="sm" icon="user-plus" wire:click="$dispatch('modal-show', { name: 'modal-asignar-alumnos' })">Inscribir Alumnos</flux:button>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nombre</flux:table.column>
                        <flux:table.column>CURP</flux:table.column>
                        <flux:table.column align="center">F. Inscripción</flux:table.column>
                        <flux:table.column align="right"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($this->grupo->alumnos as $alumno)
                            <flux:table.row :key="$alumno->id">
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$alumno->nombre" size="xs" color="zinc" />
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm">{{ $alumno->name }}</span>
                                            <span class="text-[10px] text-zinc-400">{{ $alumno->email }}</span>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="font-mono text-xs">{{ $alumno->curp }}</flux:table.cell>
                                <flux:table.cell align="center" class="text-xs">
                                    {{ \Carbon\Carbon::parse($alumno->pivot->fecha_asignacion)->format('d/m/Y') }}
                                </flux:table.cell>
                                <flux:table.cell align="right">
                                    <flux:button variant="ghost" size="sm" icon="user-minus" color="red" wire:confirm="¿Deseas dar de baja a este alumno del grupo?" wire:click="desvincularAlumno({{ $alumno->id }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" align="center" class="py-12 text-zinc-400 italic">No hay alumnos inscritos en este grupo.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <!-- Panel Derecho: Docente y Expediente -->
        <div class="space-y-6">
            
            <!-- Docente Asignado -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Docente</flux:heading>
                    <flux:button variant="ghost" size="sm" icon="pencil" wire:click="$dispatch('modal-show', { name: 'modal-asignar-docente' })">Cambiar</flux:button>
                </div>
                
                @if($this->docente)
                    <div class="flex flex-col items-center text-center space-y-2 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl">
                        <flux:avatar :name="$this->docente['name']" size="xl" />
                        <div class="flex flex-col">
                            <span class="font-bold text-zinc-900 dark:text-white">{{ $this->docente['name'] }}</span>
                            <span class="text-xs text-zinc-500">{{ $this->docente['email'] }}</span>
                        </div>
                        <flux:badge size="sm" variant="inset">{{ $this->docente['cargo'] }}</flux:badge>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-400">
                        <flux:icon name="user-circle" class="size-10 mb-2 opacity-50" />
                        <span class="text-sm">Sin docente asignado</span>
                    </div>
                @endif
            </div>

            <!-- Expediente -->
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Expediente Digital</flux:heading>
                    <flux:button variant="ghost" size="sm" icon="document-plus" wire:click="$dispatch('modal-show', { name: 'modal-subir-documento' })">Subir</flux:button>
                </div>

                <div class="space-y-2">
                    @forelse($this->grupo->expediente as $doc)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <flux:icon :name="$doc->archivo ? 'document-check' : 'document-text'" class="size-5 {{ $doc->archivo ? 'text-blue-500' : 'text-zinc-400' }}" />
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold leading-tight">{{ str_replace('_', ' ', ucfirst($doc->tipo_documento)) }}</span>
                                        <span class="text-[10px] text-zinc-500">{{ $doc->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                @if($doc->archivo)
                                    <flux:button variant="ghost" size="sm" icon="arrow-down-tray" :href="Storage::url($doc->archivo)" target="_blank" />
                                @else
                                    <flux:badge size="sm" variant="outline" color="zinc" class="text-[9px]">Pendiente de subir</flux:badge>
                                @endif
                            </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-xs italic">No hay documentos cargados.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Alumnos -->
    <flux:modal name="modal-asignar-alumnos" class="max-w-xl">
        <form wire:submit="asignarAlumnos" class="space-y-6">
            <div>
                <flux:heading size="lg">Inscribir Alumnos</flux:heading>
                <flux:subheading>Busca y selecciona los alumnos para este grupo.</flux:subheading>
            </div>

            <flux:input wire:model.live.debounce.300ms="searchAlumnos" placeholder="Buscar por nombre o CURP..." icon="magnifying-glass" />

            <div class="max-h-64 overflow-y-auto space-y-2 border border-zinc-200 dark:border-zinc-700 p-2 rounded-lg">
                @foreach($this->alumnosDisponibles as $a)
                    <label class="flex items-center gap-3 p-2 hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg cursor-pointer">
                        <input type="checkbox" wire:model="selectedAlumnos" value="{{ $a->id }}" class="rounded text-indigo-600 focus:ring-indigo-500" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">{{ $a->name }}</span>
                            <span class="text-[10px] text-zinc-400">{{ $a->curp }}</span>
                        </div>
                    </label>
                @endforeach
                @if($this->alumnosDisponibles->isEmpty())
                    <div class="text-center py-4 text-zinc-400 text-sm">No se encontraron alumnos disponibles.</div>
                @endif
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Inscribir Seleccionados</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Asignar Docente -->
    <flux:modal name="modal-asignar-docente" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Asignar Docente</flux:heading>
                <flux:subheading>Directorio externo SAD (Filtrado por plantel)</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:input wire:model="searchDocente" placeholder="Buscar docente..." class="grow" />
                <flux:button icon="magnifying-glass" wire:click="buscarDocentesAPI" />
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($this->docentesAPI as $d)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$d['name']" size="sm" />
                            <div class="flex flex-col">
                                <span class="text-sm font-bold">{{ $d['name'] }}</span>
                                <span class="text-[10px] text-zinc-500">{{ $d['cargo'] }}</span>
                            </div>
                        </div>
                        <flux:button variant="primary" size="sm" wire:click="asignarDocente({{ $d['id'] }})">Elegir</flux:button>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <flux:modal.close><flux:button variant="ghost">Cerrar</flux:button></flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <!-- Modal Subir Documento -->
    <flux:modal name="modal-subir-documento" class="max-w-md">
        <form wire:submit="subirDocumento" class="space-y-6">
            <div>
                <flux:heading size="lg">Subir Documento</flux:heading>
                <flux:subheading>Añade archivos al expediente del grupo.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Tipo de Documento</flux:label>
                <flux:select wire:model="tipoDocumento">
                    <flux:select.option value="lista_asistencia">Lista de Asistencia</flux:select.option>
                    <flux:select.option value="planeacion_didactica">Planeación Didáctica</flux:select.option>
                    <flux:select.option value="reporte_evaluacion">Reporte de Evaluación</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
                <flux:error name="tipoDocumento" />
            </flux:field>

            <flux:input type="file" wire:model="archivo" />
            <flux:error name="archivo" />

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Subir Archivo</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Subir Asistencia Escaneada -->
    <flux:modal name="modal-subir-asistencia" class="max-w-md">
        <form wire:submit="subirAsistencia" class="space-y-6">
            <div>
                <flux:heading size="lg">Subir Lista Escaneada</flux:heading>
                <flux:subheading>Carga la lista de asistencia firmada para validación (Límite 3h).</flux:subheading>
            </div>

            <flux:input type="file" wire:model="escaneoAsistencia" />
            <flux:error name="escaneoAsistencia" />

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Enviar a Validación</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
