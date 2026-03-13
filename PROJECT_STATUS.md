# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes (2026-03-13 11:00)
1.  **Estabilización de Jurisdicción (Bug Fix):** Se resolvió un error de recursión infinita en el trait `HasJurisdiction` y se implementó seguridad "null-safe" en el dashboard, permitiendo un funcionamiento fluido para administradores con cualquier tipo de adscripción.
2.  **Dashboard de Estadísticas Jurisdiccionales:** Panel de control operativo con indicadores clave (Total usuarios, estatus de expedientes, promedios) que respeta estrictamente la jurisdicción del usuario.
3.  **Kit de Presentación Ejecutiva:** Se consolidó el resumen del sistema (`RESUMEN_PRESENTACION_SICOE.md`) con diagramas de arquitectura y modelado de datos para soporte institucional.

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
*Última actualización: 2026-03-13 11:00:00*
