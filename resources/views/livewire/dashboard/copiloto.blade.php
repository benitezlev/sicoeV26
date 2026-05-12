<?php

use function Livewire\Volt\{state, computed};
use App\Services\OllamaService;
use Flux\Flux;

state([
    'messages' => [
        [
            'role' => 'assistant',
            'text' => '¡Hola! Soy **SICOE-IA**, tu copiloto de analítica local. Puedo generar y ejecutar estadísticas avanzadas de matrícula, géneros, planteles y recursos directamente desde nuestra base de datos.',
            'sql' => null,
            'data' => null,
            'success' => true
        ]
    ],
    'input' => '',
    'loading' => false,
]);

$ask = function (OllamaService $ollama) {
    if (empty(trim($this->input))) {
        return;
    }

    $userQuestion = trim($this->input);
    
    // Registrar el mensaje del usuario
    $this->messages[] = [
        'role' => 'user',
        'text' => $userQuestion,
        'sql' => null,
        'data' => null,
        'success' => true
    ];

    $this->input = '';
    
    try {
        // 1. Obtener la sugerencia de SQL de Ollama local
        $schema = $ollama->getSicoeSchemaPrompt();
        $rawResponse = $ollama->generate($userQuestion, $schema);

        if (!$rawResponse) {
            $this->messages[] = [
                'role' => 'assistant',
                'text' => 'Disculpa, no logré comunicarme con el servidor local de Inteligencia Artificial (Ollama) en la IP 192.168.3.4. Por favor, verifica que el servicio esté encendido.',
                'sql' => null,
                'data' => null,
                'success' => false
            ];
            return;
        }

        // 2. Procesar y ejecutar la consulta de manera ultra segura
        $queryResult = $ollama->executeSecureQuery($rawResponse);

        if (!$queryResult['success']) {
            $this->messages[] = [
                'role' => 'assistant',
                'text' => $queryResult['message'],
                'sql' => $queryResult['sql'],
                'data' => null,
                'success' => false
            ];
            return;
        }

        // 3. Formular una respuesta ejecutiva descriptiva
        $rowsCount = count($queryResult['rows']);
        $sqlUsed = $queryResult['sql'];
        $dataResult = $queryResult['rows'];

        if ($rowsCount === 0) {
            $assistantText = "Ejecuté la consulta estadística en la base de datos local de forma segura, pero **no se encontraron registros** que coincidan con los criterios especificados.";
        } else {
            $assistantText = "¡Excelente! He ejecutado la analítica con éxito en nuestra base de datos. Se obtuvieron **{$rowsCount} fila(s)** de resultados:";
        }

        $this->messages[] = [
            'role' => 'assistant',
            'text' => $assistantText,
            'sql' => $sqlUsed,
            'data' => $dataResult,
            'success' => true
        ];

    } catch (\Exception $e) {
        $this->messages[] = [
            'role' => 'assistant',
            'text' => 'Ocurrió un error inesperado al procesar la analítica: ' . $e->getMessage(),
            'sql' => null,
            'data' => null,
            'success' => false
        ];
    }
};

$clearChat = function () {
    $this->messages = [
        [
            'role' => 'assistant',
            'text' => '¡Hola! Soy **SICOE-IA**, tu copiloto de analítica local. Puedo generar y ejecutar estadísticas avanzadas de matrícula, géneros, planteles y recursos directamente desde nuestra base de datos.',
            'sql' => null,
            'data' => null,
            'success' => true
        ]
    ];
};

?>

<div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col h-[550px]" wire:key="sicoe-copiloto-container">
    <!-- Encabezado de la IA -->
    <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-700 pb-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 relative">
                <flux:icon name="cpu-chip" class="w-5 h-5 animate-pulse" />
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-zinc-800 rounded-full"></span>
            </div>
            <div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white uppercase tracking-tight font-black flex items-center gap-2">
                    SICOE Copiloto IA <flux:badge size="sm" color="emerald" variant="solid" class="text-[8px] tracking-widest font-black">LOCAL</flux:badge>
                </flux:heading>
                <p class="text-[10px] text-zinc-500 font-medium">Conectado a Ollama (192.168.3.4) • Qwen 2.5 Coder 7B</p>
            </div>
        </div>
        <flux:button variant="ghost" size="xs" icon="arrow-path" wire:click="clearChat" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" title="Reiniciar chat">Limpiar</flux:button>
    </div>

    <!-- Contenedor del Historial de Mensajes -->
    <div class="flex-1 overflow-y-auto space-y-4 pr-2 mb-4 scrollbar-thin" x-ref="chatContainer" x-init="$watch('messages', () => $nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight }))">
        @foreach ($messages as $msg)
            <div class="flex flex-col {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }} gap-1">
                <!-- Nombre y Rol -->
                <span class="text-[9px] text-zinc-400 font-bold uppercase tracking-wider px-2">
                    {{ $msg['role'] === 'user' ? 'Tú (Operador)' : 'SICOE-IA' }}
                </span>

                <!-- Burbuja de Mensaje -->
                <div class="max-w-[85%] rounded-2xl p-4 text-xs leading-relaxed {{ $msg['role'] === 'user' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-100 dark:border-zinc-700 rounded-tl-none' }}">
                    <p class="font-medium whitespace-pre-line">{{ $msg['text'] }}</p>

                    <!-- Tabla de Resultados Dinámica -->
                    @if (!empty($msg['data']))
                        <div class="mt-4 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden overflow-x-auto shadow-sm max-w-full">
                            <table class="w-full text-left border-collapse text-[10px]">
                                <thead>
                                    <tr class="bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                                        @foreach (array_keys($msg['data'][0]) as $column)
                                            <th class="px-3 py-2 font-black uppercase text-zinc-500 dark:text-zinc-400">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900/30">
                                    @foreach ($msg['data'] as $row)
                                        <tr>
                                            @foreach ($row as $value)
                                                <td class="px-3 py-1.5 font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ is_bool($value) ? ($value ? 'Activo' : 'Inactivo') : $value }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Consulta SQL Utilizada -->
                    @if (!empty($msg['sql']))
                        <div class="mt-3">
                            <details class="group bg-zinc-100/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
                                <summary class="flex justify-between items-center px-3 py-2 text-[9px] font-black uppercase tracking-wider text-zinc-500 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 select-none outline-none">
                                    <span>Consulta SQL Ejecutada</span>
                                    <flux:icon name="chevron-down" class="w-3 h-3 transition-transform group-open:rotate-180" />
                                </summary>
                                <div class="px-3 pb-3 pt-1 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                                    <pre class="font-mono text-[9px] text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap break-all bg-zinc-900 text-emerald-400 p-2.5 rounded-lg border border-zinc-800 select-all">{{ $msg['sql'] }}</pre>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Burbuja de Carga con Animación -->
        <div wire:loading wire:target="ask" class="flex flex-col items-start gap-1">
            <span class="text-[9px] text-zinc-400 font-bold uppercase tracking-wider px-2">SICOE-IA</span>
            <div class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-100 dark:border-zinc-700 rounded-2xl rounded-tl-none p-4 max-w-[85%] flex items-center gap-3">
                <div class="flex gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.3s]"></span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.15s]"></span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce"></span>
                </div>
                <span class="text-xs font-bold text-zinc-500 animate-pulse italic">Consultando analítica con Ollama...</span>
            </div>
        </div>
    </div>

    <!-- Entrada del Operador (Formulario de Envío) -->
    <form wire:submit="ask" class="flex gap-2 items-center border-t border-zinc-100 dark:border-zinc-700 pt-3" wire:loading.attr="disabled" wire:target="ask">
        <div class="flex-1">
            <flux:input wire:model="input" placeholder="Pregunta a la IA (ej: ¿Cuántos hombres y mujeres tenemos capacitados?)..." class="font-medium" required autocomplete="off" />
        </div>
        <flux:button type="submit" variant="primary" class="font-black uppercase tracking-wider text-[10px] px-4 py-2 flex items-center gap-1" wire:loading.attr="disabled" wire:target="ask">
            <span>Consultar</span>
            <flux:icon name="paper-airplane" variant="mini" />
        </flux:button>
    </form>
</div>
