# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes (2026-03-13 10:45)
1.  **Aislamiento de Datos Maestro (Multi-tenancy):** Se implementó el trait `HasJurisdiction` que restringe automáticamente la visualización y creación de datos según el nivel, plantel o municipio del administrador. Se promovió `municipio_id` a columna estructural en `users` para optimizar este filtrado.
2.  **Dashboard de Estadísticas Jurisdiccionales:** Nuevo panel de control con indicadores clave (Total usuarios, estatus de expedientes, promedios) que se adapta dinámicamente a la zona de competencia del usuario conectado.
3.  **Gestión Académica y Documental:** Operatividad total del módulo de Calificaciones con actas PDF generables, Kárdex integrado en expedientes y herramienta de carga masiva de documentos por CURP.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Base de Datos:** `PostgreSQL 17` (Uso de columnas JSONB e índices GIN).
- **Control de Acceso:** `Spatie Permissions` + `Jurisdiction Trait` (Aislamiento lógico).
- **Reporteo:** `DomPDF` para actas y kárdex.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Refactorización de Asistencias:** Integrar la toma de asistencia con el nuevo esquema de grupos y generar reportes mensuales automatizados.
2.  **Módulo de Notificaciones:** Sistema de alertas para expedientes "Observados" para que el usuario o el plantel sepa qué corregir.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Implementar la visualización y estampado de la `firma_digital` en las actas de calificación generadas.
4.  **Optimización JSONB:** Crear índices específicos para las búsquedas dentro del perfil dinámico.

---
*Última actualización: 2026-03-13 10:45:00*
