# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-04-15 16:20)

### 👥 Gestión de Usuarios y Permisos [FIX]
1.  **Rol Administrador TI:** Creación y vinculación del rol técnico `admin_ti` en el seeder y base de datos para corregir errores de edición de perfiles administrativos.
2.  **Sincronización de Roles:** Ajuste en el componente Livewire para asegurar que la asignación de roles sea consistente con la estructura de Spatie Permission.

### 🏫 Identidad Institucional y Branding
1.  **Doble Pleca Recurso:** Soporte para encabezados divididos (Recurso 1 y 2), permitiendo composiciones complejas de logos institucionales en documentos PDF.
2.  **Persistencia de Configuración:** Corrección de errores de guardado en el módulo UMS; todos los campos (RFC, Titular, Siglas) ahora se sincronizan correctamente con PostgreSQL.

### 📝 Gestión Académica de Grupos [REFINADO]
1.  **Estabilización de Selectores:** Sustitución de componentes Flux Pro por selectores nativos estilizados, eliminando fallos de sincronización con Livewire en el modal de apertura.
2.  **Carga Horaria Automática:** Motor de cálculo que deduce automáticamente la (-1hr de comida) basado en horarios de entrada/salida y días de cátedra.
3.  **Pagination de Asistencias:** Los reportes de más de 40 horas ahora se generan por semanas académicas con detección automática de días inhábiles (Holidays).

### 🎓 Gestión de Matrícula y Trazabilidad
1.  **Bajas con Trazabilidad:** Proceso no destructivo con registro de fecha, motivo y responsable.
2.  **Reincorporación Directa:** Botón de reactivación que restaura el flujo académico sin pérdida de datos.

## 🚀 Pendientes y Próximos Pasos (Retroalimentación Miércoles 25/Mar)

### 🔴 Prioridad Alta
1.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación.
2.  **Reporte Fiscalía:** Desarrollar el reporte específico con reglas de negocio FGJEM.
3.  **Hardening de Seguridad:** Revisar políticas de acceso para perfiles de Operador y Docente.

---
*Última actualización: 2026-03-25 10:20:00*
