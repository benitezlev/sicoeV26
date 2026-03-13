# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 13:05)
1.  **Dashboard Operativo Multi-Nivel:** Se rediseñó la sección de Estado de Fuerza para mostrar un comparativo real vs teórico en tres dimensiones: por **Nivel de Seguridad**, por **Plantel** y por **Grupo Académico**.
2.  **Flexibilidad de Horarios:** Implementación de días de clase configurables por grupo (incluyendo sábados/domingos).
3.  **Módulo de Estado de Fuerza Real:** Control de asistencia individual manual con gestión de historial y bajas.
4.  **Estabilización de PDF (PostgreSQL):** Corrección de errores de ordenamiento al generar listas de asistencia.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Relaciones:** Se añadió `users()` al modelo `Plantel` y `grupos()` al modelo `User` para las métricas del dashboard.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Reporte Mensual de Fuerza:** Generar un PDF consolidado.
2.  **Módulo de Notificaciones:** Alertas para inasistencias.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Estampado de firmas en actas.

---
*Última actualización: 2026-03-13 13:05:00*
