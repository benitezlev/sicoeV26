<?php

declare(strict_types=1);

use App\Services\OllamaService;

it('extracts and executes a safe SELECT query successfully', function () {
    $service = new OllamaService();
    
    // Simular salida estructurada de Ollama
    $aiOutput = "```sql\nSELECT COUNT(id) as total FROM users;\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['success'])->toBeTrue()
        ->and($result['sql'])->toBe('SELECT COUNT(id) as total FROM users')
        ->and($result['rows'])->toBeArray();
});

it('denies execution of non-SELECT queries for security', function () {
    $service = new OllamaService();
    
    $aiOutput = "```sql\nINSERT INTO users (nombre) VALUES ('Intruso');\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Acción denegada por seguridad');
});

it('blocks queries containing destructive keywords', function () {
    $service = new OllamaService();
    
    $aiOutput = "```sql\nSELECT * FROM users; DROP TABLE users;\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Acción denegada por seguridad');
});

it('handles raw sql blocks that start with literal sql string', function () {
    $service = new OllamaService();
    
    $aiOutput = "```sql\nsql\nSELECT COUNT(*) FROM grupos;\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['success'])->toBeTrue()
        ->and($result['sql'])->toBe('SELECT COUNT(*) FROM grupos');
});

it('automatically injects LIMIT 100 on queries without a LIMIT clause', function () {
    $service = new OllamaService();
    
    $aiOutput = "```sql\nSELECT id, nombre FROM users;\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['sql'])->toContain('LIMIT 100')
        ->and($result['limited'])->toBeTrue();
});

it('does not modify queries that already have a LIMIT clause', function () {
    $service = new OllamaService();
    
    $aiOutput = "```sql\nSELECT id, nombre FROM users LIMIT 50;\n```";
    
    $result = $service->executeSecureQuery($aiOutput);
    
    expect($result['sql'])->toBe('SELECT id, nombre FROM users LIMIT 50')
        ->and($result['limited'])->toBeFalse();
});

it('sends structured chat history to Ollama and returns assistant content', function () {
    \Illuminate\Support\Facades\Http::fake([
        '*/api/chat' => \Illuminate\Support\Facades\Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => '```sql\nSELECT COUNT(*) FROM users;\n```'
            ]
        ], 200)
    ]);
    
    $service = new OllamaService();
    $messages = [
        ['role' => 'system', 'content' => 'Prompt del sistema'],
        ['role' => 'user', 'content' => '¿Cuántos alumnos hay?']
    ];
    
    $response = $service->chat($messages);
    
    expect($response)->toContain('SELECT COUNT(*) FROM users');
});
