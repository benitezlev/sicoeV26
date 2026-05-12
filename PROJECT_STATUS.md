# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-05-12 12:20)

### 🚀 ESTADO ACTUAL: PRODUCCIÓN V1 (ESTABLE - READY)
**Última actualización:** 12 de Mayo de 2026

### ✅ Hitos Completados Hoy
- **SICOE Copiloto IA Local (Ollama 192.168.3.4):**
    - **Servicio Conector:** Creación de `OllamaService` para comunicar Laravel de forma síncrona y segura con la API local de Ollama en el puerto `11434`.
    - **Motor de Inferencia Text-to-SQL:** Definición de un System Prompt sumamente robusto con la especificación exacta de las tablas de PostgreSQL (`users`, `grupos`, `recursos`, `planteles`, `metas_capacitacion`) para guiar la generación de queries de solo lectura.
    - **Blindaje y Sanitización:** Motor de seguridad en Laravel que limpia, extrae y analiza sintácticamente el SQL generado por la IA, denegando comandos destructivos (`INSERT`, `UPDATE`, `DROP`, `DELETE`, `ALTER`) para garantizar una ejecución 100% segura.
    - **Componente Conversacional en Vivo:** Interfaz de chat ejecutiva en el Dashboard que renderiza el historial de mensajes, muestra estados de carga interactivos y dibuja tablas dinámicas estructuradas con los resultados arrojados por PostgreSQL.
    - **Interruptor de Activación Global (Superadmin):** Implementación de un control on-the-fly en la cabecera del chat exclusivo para el Administrador General (`superadmin`), permitiendo suspender o habilitar la IA para Control Escolar y operadores instantáneamente. Se diseñó una tarjeta premium de aviso de suspensión para el personal cuando el servicio está inactivo.
    - **Pruebas de Seguridad (Pest PHP):** Suite de pruebas en `tests/Feature/IA/OllamaServiceTest.php` validando la sanitización correcta del SQL y bloqueando inyecciones sospechosas.
- **Módulo de Recursos Oficiales (Financiamiento):**
    - **Esquema de Base de Datos:** Creación de la tabla `recursos` y llave foránea opcional `recurso_id` en `grupos`.
    - **Modelo Eloquent:** Modelo `Recurso` con relación `hasMany` hacia `Grupo` y relación inversa en `Grupo` integrada.
    - **UI Livewire Volt:** Dropdown en el modal de apertura/edición de grupo para asignar el fondo, y un badge distintivo (💰) en el directorio de grupos que indica el origen presupuestal.
- **Cuadro Estadístico y Dashboard Financiero-Poblacional:**
    - **Analítica de Capacitados:** Panel ejecutivo que muestra la matrícula total de alumnos capacitados vigentes (excluyendo bajas de grupos).
    - **Desglose de Género en Tiempo Real:** Métricas proporcionales de hombres y mujeres inscritos en los programas curriculares.
- **Módulo de Metas de Capacitación Anuales:**
    - **Esquema de Base de Datos:** Creación de la tabla `metas_capacitacion` con restricción de unicidad para el campo `anio`.
    - **Panel Comparativo en Dashboard:** Incorporación de un bloque visual interactivo que realiza la comparación paralela de la Meta Programada contra el Avance Registrado real de cada año.
- **Correcciones de Autenticación y Sincronización de Base de Datos (PostgreSQL):**
    - **Sincronización de Secuencias:** Sincronización de la secuencia de IDs de usuarios (`users_id_seq`) tras la inserción manual del administrador de sistema (`id: 1`).
    - **Blindaje de Entorno de Tests:** Automatización de borrado de caché de configuración al inicio de cualquier prueba para evitar la limpieza accidental de la base de datos de desarrollo.

### 🚧 Próximos Pasos (Kraken Server)
- **Firma Electrónica:** Generación de folios, firmas digitales y QR en actas.
- **Migración a Kraken:** Traslado de base de datos y media a entorno final.

---
*Última actualización: 2026-05-12 12:20:00*
