# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 17:00)
1.  **Dashboard de Métricas e Insights Operativos:** Creación de componente Volt para analítica de grupos, incluyendo indicadores de retención, matrícula activa, bajas y volumen documental con interfaz gráfica SVG.
2.  **Refactorización del Módulo de Grupos:** Migración completa a componentes funcionales de Volt, eliminando controladores legados y optimizando la carga de expedientes de grupo.
3.  **Gestión de Asistencias (PDF):** Corrección y habilitación del botón de Lista de Asistencia (PDF) con validación de alumnos inscritos y corrección de datos institucionales en el formato horizontal.
4.  **Búsqueda Dinámica de Docentes:** Optimización de la búsqueda de personal vía API SAD con sincronización real-time y debounce para evitar sobrecarga del servidor.
5.  **Estandarización UI (Flux Free):** Sustitución total de iconos y componentes Flux Pro en el panel de analítica para asegurar compatibilidad 100% con la versión base.

## 🛠️ Stack y Decisiones Técnicas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Blade` + `Tailwind CSS`.
- **UI Architecture:** Uso de `Flux Free` para elementos básicos y componentes personalizados. Se adoptó el **API Funcional de Volt** en lugar de clases anónimas para mayor estabilidad en el entorno de producción.
- **Identidad:** Centralización de la identidad UMS para estampado dinámico en reportes.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Continuar Refactorización UI:** Extender el nuevo diseño a los módulos de Materias, Asistencias y Calificaciones (Estatus: En progreso).
2.  **Módulo de Calificaciones:** Mejorar la interfaz de captura masiva de notas para coordinadores.

### 🟡 Prioridad Media
3.  **Firma Electrónica Simple:** Preparar el estampado de firmas digitales en actas de calificación.

---
*Última actualización: 2026-03-13 17:00:00*
