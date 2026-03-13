# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 11:30)
1.  **Aislamiento y Scopes Municipales:** Se implementaron scopes locales (`scopeDelMunicipio`, `scopeDelNivel`) y se formalizó la relación `users()` en el modelo `Municipio`, permitiendo consultas optimizadas para la administración descentralizada.
2.  **Estabilización de Jurisdicción:** Resolución de bucles de recursión en el motor de filtrado global (HasJurisdiction) y blindaje del Dashboard ante valores nulos.
3.  **Módulo Académico y Kit de Presentación:** Finalización de Actas PDF, Kárdex y resumen ejecutivo para la presentación institucional.

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
