# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 12:40)
1.  **Módulo de Estado de Fuerza Real:** Implementación del control de asistencia individual manual. Ahora Control Escolar puede registrar la presencia (P), falta (F) o permiso (J) de cada elemento, permitiendo un monitoreo de operatividad en tiempo real.
2.  **Monitor Operativo en Dashboard:** Se añadió un indicador visual por nivel (Estatal, Municipal, Fiscalía) que muestra el % de Fuerza Real (elementos presentes hoy vs teóricos), con barras de progreso dinámicas.
3.  **Gestión de Bajas en Lista:** El sistema permite visualizar a los elementos que han sido dados de baja del grupo, manteniéndolos en la lista de asistencia con estatus de "BAJA" para fines de registro histórico y auditoría.
4.  **Estabilización y Datos de Demo:** Carga masiva de datos académicos (alumnos, cursos, materias, calificaciones) para asegurar una operatividad total del sistema en entornos de demostración.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Base de Datos:** `PostgreSQL 17` (Aislamiento por esquema y jurisdicción).
- **Módulo de Asistencias:** `AsistenciaIndividual` (manual) vinculado a `Asistencia` (lote/archivo).

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Reporte Mensual de Fuerza:** Generar un PDF que consolide todas las asistencias del mes para firma de los mandos.
2.  **Módulo de Notificaciones:** Sistema de alertas para expedientes "Observados" y para inasistencias recurrentes.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Implementar la visualización y estampado de la `firma_digital` en las actas de calificación generadas.

---
*Última actualización: 2026-03-13 12:40:00*
