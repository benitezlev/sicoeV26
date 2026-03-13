# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 13:00)
1.  **Dashboard Operativo Detallado:** Se integró un desglose de "Estado de Fuerza" tanto por Nivel de Seguridad (Fiscalía, Estatal, Municipal) como por **Grupo Académico**. Ahora el Dashboard muestra en tiempo real cuántos elementos están presentes en cada salón de clases.
2.  **Flexibilidad de Horarios (Sábados/Domingos):** Implementación de días de clase configurables por grupo.
3.  **Módulo de Estado de Fuerza Real:** Control de asistencia individual manual con gestión de bajas.
4.  **Estabilización de PDF (PostgreSQL):** Corrección de errores de ordenamiento al generar listas de asistencia.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Métricas:** Consultas optimizadas con `withCount` y filtrado por `AsistenciaIndividual`.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Reporte Mensual de Fuerza:** Generar un PDF consolidado.
2.  **Módulo de Notificaciones:** Alertas para inasistencias.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Estampado de firmas en actas.

---
*Última actualización: 2026-03-13 13:00:00*
