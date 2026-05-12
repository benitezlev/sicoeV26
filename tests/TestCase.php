<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Boot the testing application.
     * 
     * Fuerza la eliminación de la configuración cacheada antes de levantar la app para pruebas,
     * garantizando que Pest/PHPUnit usen siempre 'sicoe_test' y no limpien la base de datos local ('sicoe_pg').
     */
    public function createApplication()
    {
        $configCachePath = __DIR__ . '/../bootstrap/cache/config.php';
        
        if (file_exists($configCachePath)) {
            @unlink($configCachePath);
        }

        return parent::createApplication();
    }
}
