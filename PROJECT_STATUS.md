# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 13:25)
1.  **Dashboard Operativo Tabular:** Rediseño interior de planteles para mostrar grupos en formato de tabla compacta (Presentes vs Faltantes).
2.  **Migración Inteligente de Datos:** Sistema asistido que permite trasladar usuarios y grupos a un nuevo plantel de forma segura antes de eliminar un plantel (PostgreSQL 17).
3.  **Flexibilidad de Horarios:** Soporte nativo para regímenes intensivos (sábados y domingos).
4.  **Estado de Fuerza Real:** Monitoreo de asistencias con comparativo real vs teórico en tres dimensiones.

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
*Última actualización: 2026-03-13 13:25:00*
