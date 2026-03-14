# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-13 18:00)
1.  **Importación Masiva de Alumnos (v2):** Refactorización del motor de importación CSV con soporte para múltiples delimitadores, auto-detección de encoding y sanitización profunda de UTF-8 para nombres con acentos.
2.  **Adscripción Dinámica a Plantel:** Se eliminó la obligación de asignar plantel en la creación. Ahora el sistema vincula automáticamente al alumno con el plantel del grupo activo en el momento de la inscripción, permitiendo la rotación histórica.
3.  **Troubleshooting de Infraestructura:** Optimización de permisos en `storage/` y `bootstrap/cache`, y ajuste de lógica de carga de archivos para el límite de 2MB del entorno PHP actual.
4.  **UX de Carga de Archivos:** Implementación de barra de progreso en tiempo real con Alpine.js y feedback dinámico durante el procesamiento de registros.
5.  **Detección de Conflictos Académicos:** Alerta preventiva (Badge) al intentar inscribir alumnos que ya cuentan con un grupo activo en el ciclo escolar actual.

## 🛠️ Stack y Decisiones Técnicas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Blade` + `Tailwind CSS`.
- **UI Architecture:** Uso de `Flux Free` para elementos básicos y componentes personalizados. Se adoptó el **API Funcional de Volt** en lugar de clases anónimas para mayor estabilidad en el entorno de producción.
- **Identidad:** Centralización de la identidad UMS para estampado dinámico en reportes.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Resolución de Restricción Unique (CUP/CUIP):** Corregir error de Postgres que detecta duplicados cuando los campos `cup` o `cuip` vienen vacíos en la importación.
2.  **Módulo de Calificaciones:** Mejorar la interfaz de captura masiva de notas para coordinadores.
3.  **Continuar Refactorización UI:** Extender el nuevo diseño a los módulos de Materias y Asistencias.

### 🟡 Prioridad Media
4.  **Firma Electrónica Simple:** Preparar el estampado de firmas digitales en actas de calificación.

---
*Última actualización: 2026-03-13 18:00:00*
