# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-17 14:20)
1.  **Flexibilidad de Formatos [UPGRADE]:** Se añadió un interruptor manual en la configuración de grupos para forzar el "Formato Especial" (Diagnóstica/Final), permitiendo que cursos de más de 40 horas también utilicen este esquema si Control Escolar lo requiere.
2.  **Cursos de 40 Horas [SOPORTE]:** Se mantiene la detección automática para cursos cortos, pero ahora con anulación manual disponible.
3.  **Libreta Digital Adaptativa:** Sincronización completa entre el switch de formato y las opciones de calificación mostradas al coordinador.
2.  **Catálogo de Cursos [FIX]:** Corrección de error `Not null violation` en la columna `categoria`. Se integró el campo al componente Livewire, validaciones y formulario de alta/edición.
3.  **Importación Masiva de Alumnos (v2) [COMPLETADO]:** Procesamiento exitoso de toda la matrícula. Se resolvieron conflictos de unicidad en Postgres, sanitización de UTF-8 y manejo de BOM en encabezados.
3.  **Optimización de Calificaciones y Asistencias:** Corrección de errores SQL de ordenamiento dinámico. Estandarización de nombres en la interfaz usando el campo persistido `nombre_completo`.
4.  **Robustecimiento de Lógica de Negocio (Grupos):** Solución al error de tipo en `diasHabilesEntreFechas` activando validaciones defensivas para la decodificación de JSON mixtos en la base de datos.

## 🛠️ Stack y Decisiones Técnicas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Blade` + `Tailwind CSS`.
- **UI Architecture:** Uso de `Flux Free` para elementos básicos y componentes personalizados. Se adoptó el **API Funcional de Volt** en lugar de clases anónimas para mayor estabilidad en el entorno de producción.
- **Identidad:** Centralización de la identidad UMS para estampado dinámico en reportes.

## 🚀 Pendientes y Próximos Pasos (Priorizados)

### 🔴 Prioridad Alta
1.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación para autenticidad.
2.  **Captura de Calificaciones:** Añadir validación de periodos de captura (fechas límite por coordinación).

### 🟡 Prioridad Media
3.  **Refactorización UI:** Continuar con el rediseño de reportes históricos de alumnos.

---
*Última actualización: 2026-03-17 13:45:00*
