# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos 3 Cambios Importantes (2026-03-12)
1.  **Refinamiento de Modals & Reactividad:** Se resolvió definitivamente la carga de datos en los modales de Usuarios y Planteles mediante señales de despacho (`dispatch`) y claves dinámicas (`wire:key`).
2.  **Infraestructura PostgreSQL:** Se inició la migración del motor de base de datos. Se depuraron las migraciones (eliminando duplicados y ordenando dependencias) y se migró con éxito la estructura y los datos de Roles, Permisos y Planteles.
3.  **Plan Estratégico Multi-Nivel:** Se documentó la visión para integrar niveles de Seguridad Estatal, Municipal y CONOCER utilizando perfiles flexibles (JSONB) en el modelo de Identidad Universal.

## 🛠️ Contexto de Ejecución: Dependencias Críticas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Flux UI`.
- **Base de Datos:** En transición de `MySQL` a `PostgreSQL 17`.
- **Lógica de Identidad:** Perfiles dinámicos mediante campos JSONB.
- **API Externa:** Sincronización con `SAD` para docentes.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta (Para Mañana)
1.  **Completar Migración de Datos:** Finalizar la transferencia de la tabla `users` y tablas de logs (materia_logs, profesor_logs) a PostgreSQL.
2.  **Switch Definitivo:** Cambiar la conexión predeterminada a `pgsql` y realizar pruebas de integridad en todo el sistema.
3.  **Módulo de Calificaciones:** Diseñar el componente Volt para la captura de calificaciones por unidad/materia.

### 🟡 Prioridad Media
4.  **Modelo de Perfil Flexible:** Implementar la lógica de "metadata" JSON para los campos específicos de Municipios y CONOCER.
5.  **Optimización SAD:** Implementar caché para las respuestas de la API institucional.

### 🟢 Prioridad Baja
6.  **Refinamiento UI:** Ajustes de accesibilidad y revisión de modo oscuro en componentes complejos.

---
*Última actualización: 2026-03-12 18:05:00*
