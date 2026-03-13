# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 15:05)
1.  **Refactorización UI Total (Expedientes, Dashboard, Usuarios, Roles):** Eliminación completa de componentes `flux:table` y modales Pro en los módulos principales, sustituyéndolos por HTML estándar, Tailwind y Alpine.js para una estética premium y carga ultrarrápida.
2.  **Organización de Sidebar:** Agrupamiento de endpoints por función operativa (Control Escolar, Academia, Operatividad y Configuración) para mejorar la navegación.
3.  **Identidad UMS en Configuración:** Adaptación del módulo de configuración con los campos oficiales de la Universidad Mundial de Sonora (Titular, Puesto, Objetivo) y logo institucional dinámico.
4.  **Corrección de Roles:** Se habilitó la edición de roles con reactividad mejorada y validación de duplicados.
5.  **Integración de Spatie Media Library:** Estandarización de toda la gestión documental y carga masiva ZIP con Queues.

## 🛠️ Stack y Decisiones Técnicas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Blade` + `Tailwind CSS`.
- **UI Architecture:** Uso de `Flux Free` para elementos básicos y componentes personalizados para elementos complejos (Modales/Tablas) garantizando independencia total de versiones de pago.
- **Identidad:** Centralización de la identidad UMS para estampado dinámico en reportes.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Continuar Refactorización UI:** Extender el nuevo diseño a los módulos de Grupos, Materias, Asistencias y Calificaciones.
2.  **Reporte Mensual de Fuerza:** Implementación de generación de PDF consolidado basado en los nuevos datos institucionales.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Preparar el estampado de firmas digitales en actas de calificación.

---
*Última actualización: 2026-03-13 15:05:00*
