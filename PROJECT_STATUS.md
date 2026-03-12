# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes
1.  **Migración de Módulo de Grupos (Volt):** Se rediseñó completamente la gestión de grupos. Ahora incluye inscripción dinámica de alumnos, asignación de docentes desde API externa (SAD) y gestión de expedientes digitales, todo bajo la arquitectura Volt y Flux UI.
2.  **Consolidación de Catálogo Académico:** Los módulos de Cursos y Materias fueron migrados a Volt, permitiendo la edición inline de tiras académicas y la generación profesional de reportes PDF institucionalizados.
3.  **Refinamiento de Control de Acceso (RBAC):** Se implementó la restricción basada en el permiso `manage-personal`. Ahora, funciones críticas como la creación de nuevos elementos/usuarios están limitadas a Super Admins y Administradores de Entidad autorizados.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
El proyecto está construido sobre el ecosistema modern de Laravel y requiere los siguientes componentes clave:
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **API Externa:** Sincronización con el sistema `SAD` para datos de docentes por plantel.
- **Tablas:** [PowerGrid v6](https://livewire-powergrid.com/) para el manejo de tablas dinámicas.
- **Reportes:** `Barryvdh\DomPDF` para exportaciones institucionales PDF.

## 🚀 Pendientes y Próximos Pasos (Priorizados)
1.  **[COMPLETADO] Migración a Laravel 12 & Flux UI:** Core y Layout actualizados.
2.  **[COMPLETADO] Módulo de Configuración (Volt):** Migrado a Volt/Flux.
3.  **[COMPLETADO] Módulo de Roles (Volt):** Migrado a Volt/Flux con gestión unificada.
4.  **[COMPLETADO] Módulo de Planteles (Volt):** Migrado a Volt/Flux.
5.  **[COMPLETADO] Módulo de Usuarios e Importación (Volt):** Migrado a Volt/Flux.
6.  **[COMPLETADO] Módulo de Expedientes (Volt):** Gestión de documentos y validaciones.
7.  **[COMPLETADO] Módulo de Docentes (Volt):** Sincronización con API SAD.
8.  **[COMPLETADO] Módulo de Cursos y Materias (Volt):** Catálogo académico completo.
9.  **[COMPLETADO] Módulo de Grupos (Volt):** Gestión de inscripciones, docentes y expedientes de grupo.
10. **Refactorización de Asistencias:** Migrar la lógica de carga y validación de listas de asistencia a componentes Volt para mayor interactividad.

---
*Última actualización: 2026-03-12 15:45:00*

## ⏭️ Siguientes Pasos Priorizados
1.  **Limpieza de Controladores:** Eliminar `GrupoController`, `MateriaController`, `CursoController` y `PanelMateriasController` tras verificar estabilidad de rutas Volt.
2.  **Migración de Asistencias:** Crear componente Volt para la carga procesada de listas.
3.  **Optimización de Búsqueda SAD:** Implementar caché para las respuestas de la API de docentes.
