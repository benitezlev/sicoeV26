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
