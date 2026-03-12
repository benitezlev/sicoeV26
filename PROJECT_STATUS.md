# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes
1.  **Módulo de Asistencias (Volt):** Se implementó un dashboard centralizado para la gestión de listas de asistencia escaneadas. Incluye validación con límite de 3 horas, filtrado por plantel/estado y carga directa desde el detalle del grupo.
2.  **Limpieza y Consolidación de Código:** Se eliminó el código heredado (controladores y vistas obsoletas) de los módulos de Grupos, Materias, Cursos y Alumnos. La lógica ahora reside al 100% en componentes Volt/Flux.
3.  **PDF de Asistencia Profesional:** Se rediseñó el formato horizontal de la lista de asistencia añadiendo columna de firmas para alumnos, espacios para sellos institucionales y ajuste dinámico de calendarios.

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
10. **[COMPLETADO] Módulo de Asistencias (Volt):** Dashboard de validación y carga interactiva.
11. **[COMPLETADO] Limpieza de Código Heredado:** Eliminación de controladores y vistas redundantes.

---
*Última actualización: 2026-03-12 16:45:00*

## ⏭️ Siguientes Pasos Priorizados
1.  **Optimización de Búsqueda SAD:** Implementar caché para las respuestas de la API de docentes para mejorar latencia.
2.  **Módulo de Calificaciones:** Iniciar el diseño del componente Volt para la captura de calificaciones por materia/grupo.
3.  **Refinamiento de UI:** Revisar el modo oscuro en todos los nuevos componentes para asegurar contraste óptimo.
