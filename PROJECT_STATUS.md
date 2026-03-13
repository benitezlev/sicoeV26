# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 12:55)
1.  **Flexibilidad de Horarios (Sábados/Domingos):** Se implementó una nueva lógica de días de clase configurables por grupo. Ahora el sistema permite definir específicamente qué días de la semana se imparten las clases (incluyendo fines de semana), impactando automáticamente en la generación de listas de asistencia y el Estado de Fuerza.
2.  **Módulo de Estado de Fuerza Real:** Implementación del control de asistencia individual manual. Ahora Control Escolar puede registrar la presencia (P), falta (F) o permiso (J) de cada elemento, permitiendo un monitoreo de operatividad en tiempo real.
3.  **Monitor Operativo en Dashboard:** Se añadió un indicador visual por nivel (Estatal, Municipal, Fiscalía) que muestra el % de Fuerza Real (elementos presentes hoy vs teóricos), con barras de progreso dinámicas.
4.  **Estabilización de PDF (PostgreSQL):** Corrección de errores de ordenamiento al generar listas de asistencia, asegurando compatibilidad total con el motor de base de datos actual.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Configuración de Grupos:** Columna `dias_clase` (JSONB) para control granular de calendario.
- **Base de Datos:** `PostgreSQL 17` (Uso de tipos complejos como JSONB).

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Reporte Mensual de Fuerza:** Generar un PDF que consolide todas las asistencias del mes para firma de los mandos.
2.  **Módulo de Notificaciones:** Sistema de alertas para expedientes "Observados" y para inasistencias recurrentes.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Implementar la visualización y estampado de la `firma_digital` en las actas de calificación generadas.

---
*Última actualización: 2026-03-13 12:55:00*
