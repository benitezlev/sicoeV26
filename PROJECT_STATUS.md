# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes
1.  **CRUD Usuarios (Volt):** Se completó la funcionalidad de creación y edición mediante un modal unificado con sincronización de roles, validación de campos y manejo de contraseñas.
2.  **Módulo de Asistencias (Volt):** Se implementó un dashboard centralizado para la gestión de listas de asistencia escaneadas con validación de 3 horas y filtrado dinámico.
3.  **Limpieza y Consolidación de Código:** Se eliminó el código heredado de los módulos core, migrando la lógica al 100% a componentes Volt/Flux.

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
5.  **[COMPLETADO] Módulo de Usuarios (CRUD Funcional):** Corrección de modals y lógica de guardado/edición.
6.  **[COMPLETADO] Módulo de Expedientes (Volt):** Gestión de documentos y validaciones.
7.  **[COMPLETADO] Módulo de Docentes (Volt):** Sincronización con API SAD.
8.  **[COMPLETADO] Módulo de Cursos y Materias (Volt):** Catálogo académico completo.
9.  **[COMPLETADO] Módulo de Grupos (Volt):** Gestión de inscripciones, docentes y expedientes de grupo.
10. **[COMPLETADO] Módulo de Asistencias (Volt):** Dashboard de validación y carga interactiva.
11. **[COMPLETADO] Limpieza de Código Heredado:** Eliminación de controladores y vistas redundantes.

---
*Última actualización: 2026-03-12 17:10:00*

## ⏭️ Siguientes Pasos Priorizados
1.  **Módulo de Calificaciones:** Iniciar el diseño del componente Volt para la captura de calificaciones por materia/grupo.
2.  **Optimización de Búsqueda SAD:** Implementar caché para las respuestas de la API de docentes para mejorar latencia.
3.  **Refinamiento de UI:** Revisar el modo oscuro en todos los nuevos componentes para asegurar contraste óptimo.
