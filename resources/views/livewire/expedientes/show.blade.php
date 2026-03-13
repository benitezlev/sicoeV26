<?php

use function Livewire\Volt\{state, computed, layout, mount};
use App\Models\Expediente;
use App\Models\DocumentosExpediente;
use App\Models\DocumentoRequerido;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Flux\Flux;

layout('layouts.app');

// State & Variables
state([
    'expediente' => null,
    'archivo' => null,
    'tipo_documento' => 'IDENTIFICACION',
    'observacion_texto' => '',
    'doc_id_para_observar' => null,
]);

// Initialize
mount(function (Expediente $expediente) {
    $this->expediente = $expediente->load(['user.movimientos.autor', 'user.movimientos.plantelAnterior', 'user.movimientos.plantelNuevo', 'user.calificaciones.materia', 'user.calificaciones.grupo', 'documentos.cargador']);
});

// Actions
$revalidar = function () {
    $faltantes = $this->expediente->validarDocumentosPorPerfil();
    $this->expediente->refresh();

    Flux::toast(
        heading: 'Estatus actualizado',
        text: empty($faltantes) ? 'Expediente completo.' : 'Se detectaron documentos faltantes.',
        variant: empty($faltantes) ? 'success' : 'warning'
    );
};

$validarDocumento = function ($id) {
    $doc = DocumentosExpediente::findOrFail($id);
    $doc->update([
        'estatus' => 'validado',
        'observaciones' => null,
    ]);

    Log::channel('expedientes')->info("Documento ID {$id} VALIDADO por " . auth()->user()->name);
    $this->expediente->refresh();
    Flux::toast(heading: 'Documento validado');
};

$abrirModalObservacion = function ($id) {
    $this->doc_id_para_observar = $id;
    $this->observacion_texto = '';
    $this->dispatch('modal-show', name: 'modal-observar');
};

$guardarObservacion = function () {
    $this->validate(['observacion_texto' => 'required|min:5']);

    $doc = DocumentosExpediente::findOrFail($this->doc_id_para_observar);
    $doc->update([
        'estatus' => 'observado',
        'observaciones' => $this->observacion_texto
    ]);

    $this->expediente->update(['estatus' => 'observado']);
    Log::channel('expedientes')->warning("Documento ID {$this->doc_id_para_observar} OBSERVADO: {$this->observacion_texto}");

    $this->expediente->refresh();
    $this->dispatch('modal-hide', name: 'modal-observar');
    Flux::toast(heading: 'Observación registrada', variant: 'warning');
};

$cargarDocumento = function () {
    $this->validate([
        'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'tipo_documento' => 'required'
    ]);

    $curp = $this->expediente->user->curp;
    $nombre = "{$curp}_{$this->tipo_documento}." . $this->archivo->getClientOriginalExtension();
    $ruta = $this->archivo->storeAs("expedientes/{$curp}", $nombre, 'public');

    DocumentosExpediente::create([
        'user_id' => $this->expediente->user_id,
        'expediente_id' => $this->expediente->id,
        'tipo' => $this->tipo_documento,
        'archivo' => $ruta,
        'fecha_carga' => now(),
        'cargado_por' => auth()->id(),
        'estatus' => 'pendiente',
    ]);

    $this->archivo = null;
    $this->expediente->refresh();
    $this->dispatch('modal-hide', name: 'modal-cargar');
    Flux::toast(heading: 'Documento cargado correctamente');
};

?>

<div class="space-y-8">
    <x-slot name="header">Detalle de Expediente</x-slot>

    <!-- Resumen de Alumno -->
    <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <flux:avatar src="{{ $expediente->user->profile_photo_url }}" :name="$expediente->user->nombre" size="xl" />
                <div class="space-y-1">
                    <flux:heading size="xl">{{ $expediente->user->nombre }} {{ $expediente->user->paterno }} {{ $expediente->user->materno }}</flux:heading>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-500">
                        <span class="flex items-center gap-1">
                            <flux:badge size="xs" :color="match($expediente->user->nivel){'fiscalia'=>'purple','municipal'=>'emerald',default=>'blue'}" variant="outline">
                                {{ ucfirst($expediente->user->nivel) }}
                            </flux:badge>
                        </span>
                        <span class="flex items-center gap-1"><flux:icon name="identification" variant="mini" /> {{ $expediente->user->curp }}</span>
                        <span class="flex items-center gap-1"><flux:icon name="building-office" variant="mini" /> {{ $expediente->user->perfil_data['dependencia'] ?? 'Sin dependencia' }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-3">
                <flux:badge size="lg" :color="match($expediente->estatus) {
                    'completo' => 'green',
                    'observado' => 'red',
                    default => 'amber'
                }" variant="pill" class="px-4 py-1 text-sm font-bold uppercase tracking-widest">
                    {{ $expediente->estatus }}
                </flux:badge>
                <div class="flex gap-2">
                    <flux:button icon="arrow-path" size="sm" wire:click="revalidar">Revalidar Estatus</flux:button>
                    <flux:modal.trigger name="modal-cargar">
                        <flux:button variant="primary" icon="document-plus" size="sm">Cargar Documento</flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ tab: 'documentos' }" class="space-y-6">
        <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl w-fit">
            <button 
                @click="tab = 'documentos'" 
                :class="tab === 'documentos' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            >
                <flux:icon name="document-text" variant="mini" />
                Documentación
            </button>
            <button 
                @click="tab = 'historial'" 
                :class="tab === 'historial' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            >
                <flux:icon name="clock" variant="mini" />
                Historial de Movimientos
            </button>
            <button 
                @click="tab = 'kardex'" 
                :class="tab === 'kardex' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            >
                <flux:icon name="academic-cap" variant="mini" />
                Kárdex Académico
            </button>
        </div>

        <div x-show="tab === 'documentos'" x-cloak class="animate-in fade-in duration-300">
            <div class="space-y-4">
                <flux:heading size="lg" class="px-2 text-zinc-600">Documentación Cargada</flux:heading>
                
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Tipo de Documento</flux:table.column>
                            <flux:table.column>Archivo / Fecha</flux:table.column>
                            <flux:table.column>Cargado Por</flux:table.column>
                            <flux:table.column align="center">Estatus</flux:table.column>
                            <flux:table.column align="center">Acciones</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($expediente->documentos as $doc)
                                <flux:table.row :key="$doc->id">
                                    <flux:table.cell>
                                        <span class="font-bold text-zinc-700 dark:text-zinc-200">{{ $doc->tipo }}</span>
                                        @if($doc->observaciones)
                                            <div class="mt-1 text-xs text-red-500 italic flex items-center gap-1">
                                                <flux:icon name="exclamation-circle" variant="mini" />
                                                {{ $doc->observaciones }}
                                            </div>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <div class="flex flex-col">
                                            <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                                <flux:icon name="document-text" variant="mini" /> ver_archivo.pdf
                                            </a>
                                            <span class="text-[10px] text-zinc-400 font-mono">{{ \Carbon\Carbon::parse($doc->fecha_carga)->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <span class="text-xs text-zinc-600">{{ $doc->cargador?->nombre ?? 'Sistema' }}</span>
                                    </flux:table.cell>

                                    <flux:table.cell align="center">
                                        <flux:badge size="sm" :color="match($doc->estatus) {
                                            'validado' => 'green',
                                            'observado' => 'red',
                                            default => 'zinc'
                                        }" variant="inset">
                                            {{ ucfirst($doc->estatus) }}
                                        </flux:badge>
                                    </flux:table.cell>

                                    <flux:table.cell align="center">
                                        <div class="flex gap-2 justify-center">
                                            @if($doc->estatus !== 'validado')
                                                <flux:button variant="ghost" size="sm" icon="check-circle" color="green" wire:click="validarDocumento({{ $doc->id }})" />
                                            @endif
                                            
                                            <flux:button variant="ghost" size="sm" icon="chat-bubble-bottom-center-text" color="amber" wire:click="abrirModalObservacion({{ $doc->id }})" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" align="center" class="py-12 text-zinc-400">
                                        No hay documentos registrados en este expediente.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        </div>

        <div x-show="tab === 'historial'" x-cloak class="animate-in fade-in duration-300">
            <div class="space-y-6 py-4">
                <flux:heading size="lg" class="px-2 text-zinc-600">Línea de Tiempo de Adscripción</flux:heading>
                
                <div class="relative pl-8 space-y-8 before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-zinc-200 dark:before:bg-zinc-700">
                    @forelse ($expediente->user->movimientos->sortByDesc('created_at') as $mov)
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full border-4 border-white dark:border-zinc-800 bg-blue-500 shadow-sm"></div>
                            <div class="bg-zinc-50 dark:bg-zinc-900/50 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm transition-all hover:shadow-md">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" color="blue" inset="top bottom">{{ ucfirst(str_replace('_', ' ', $mov->tipo_movimiento)) }}</flux:badge>
                                        <span class="text-xs text-zinc-400 font-mono">{{ $mov->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <span class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Ref: #MOV-{{ $mov->id }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                    <div class="space-y-2 p-3 bg-white dark:bg-zinc-800 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                                        <span class="text-[10px] text-zinc-400 font-bold uppercase block">Estado Anterior</span>
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="xs" color="zinc" variant="outline">{{ ucfirst($mov->nivel_anterior ?? 'N/A') }}</flux:badge>
                                            <span class="text-sm text-zinc-600 italic">{{ $mov->plantelAnterior->name ?? 'Sin plantel' }}</span>
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $mov->perfil_data_anterior['dependencia'] ?? 'Sin dependencia' }}
                                        </div>
                                    </div>

                                    <div class="hidden md:flex justify-center">
                                        <flux:icon name="arrow-long-right" class="text-zinc-300" />
                                    </div>

                                    <div class="space-y-2 p-3 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-800/50">
                                        <span class="text-[10px] text-blue-400 font-bold uppercase block">Estado Nuevo</span>
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="xs" color="blue" variant="solid">{{ ucfirst($mov->nivel_nuevo) }}</flux:badge>
                                            <span class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ $mov->plantelNuevo->name ?? 'Sin plantel' }}</span>
                                        </div>
                                        <div class="text-xs text-zinc-700 dark:text-zinc-300 font-medium">
                                            {{ $mov->perfil_data_nuevo['dependencia'] ?? 'Sin dependencia' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center text-xs">
                                    <span class="text-zinc-500"><b class="text-zinc-400">Motivo:</b> {{ $mov->motivo }}</span>
                                    <span class="text-zinc-400">Autorizado por: <b>{{ $mov->autor->nombre ?? 'Sistema' }}</b></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-zinc-400 italic">
                            No se han registrado movimientos de adscripción para este usuario.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="tab === 'kardex'" x-cloak class="animate-in fade-in duration-300">
            <div class="space-y-6 py-4">
                <flux:heading size="lg" class="px-2 text-zinc-600">Historial Académico del Elemento</flux:heading>
                
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Materia</flux:table.column>
                            <flux:table.column>Grupo</flux:table.column>
                            <flux:table.column align="center" class="w-24">Unidad</flux:table.column>
                            <flux:table.column align="center" class="w-28">Calificación</flux:table.column>
                            <flux:table.column>Fecha Registro</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($expediente->user->calificaciones->sortByDesc('created_at') as $cal)
                                <flux:table.row :key="$cal->id">
                                    <flux:table.cell>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-zinc-700 dark:text-zinc-200 leading-tight">{{ $cal->materia->nombre }}</span>
                                            <span class="text-[9px] text-zinc-400 uppercase tracking-widest font-mono">ID: {{ $cal->materia->identificador ?? 'N/A' }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-xs text-zinc-600">{{ $cal->grupo->nombre }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge size="xs" color="zinc" variant="outline" class="font-mono px-2">{{ $cal->unidad }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <span class="text-base font-black {{ $cal->calificacion >= 6 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ number_format($cal->calificacion, 1) }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-[10px] text-zinc-400 font-mono italic">{{ $cal->created_at->format('d/m/Y') }}</span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" align="center" class="py-16 text-zinc-400 italic">
                                        No se han capturado calificaciones para este expediente.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Observación -->
    <flux:modal name="modal-observar" class="max-w-md">
        <form wire:submit="guardarObservacion" class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar Observación</flux:heading>
                <flux:subheading>Indica el motivo por el cual el documento no es válido.</flux:subheading>
            </div>

            <flux:textarea wire:model="observacion_texto" placeholder="Ej: La imagen está borrosa o la CURP no coincide..." rows="4" />
            <flux:error name="observacion_texto" />

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Observación</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Carga -->
    <flux:modal name="modal-cargar" class="max-w-md">
        <form wire:submit="cargarDocumento" class="space-y-6">
            <div>
                <flux:heading size="lg">Cargar Nuevo Documento</flux:heading>
                <flux:subheading>Sube un archivo para complementar el expediente institucional.</flux:subheading>
            </div>

            <flux:select wire:model="tipo_documento" label="Tipo de Documento">
                <flux:select.option value="ACTA">Acta de Nacimiento</flux:select.option>
                <flux:select.option value="CONSTANCIA">Constancia de Estudios</flux:select.option>
                <flux:select.option value="OFICIO">Oficio de Comisión</flux:select.option>
                <flux:select.option value="IDENTIFICACION">Identificación Oficial</flux:select.option>
            </flux:select>

            <flux:field>
                <flux:label>Seleccionar Archivo (PDF/JPG)</flux:label>
                <flux:input type="file" wire:model="archivo" />
                <flux:error name="archivo" />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Subir Archivo</span>
                    <span wire:loading>Subiendo...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
