# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-18 13:20)

### 📈 Reportes e Identidad Institucional [FINALIZADO]
1.  **Ingeniería de Encabezados (v3):** Se consolidó el uso de tablas estructurales para los logotipos (140px) y nombre del plantel, garantizando estabilidad absoluta en DomPDF.
2.  **Estandarización de Impresión:** Configuración de márgenes uniformes (1cm) y tamaño `letter landscape`. Se habilitó la repetición automática de encabezados (`thead`) para reportes de varias páginas.
3.  **Optimización de Espacio:** Ajuste dinámico de filas de relleno (máximo 12) para asegurar que grupos pequeños queden contenidos en una sola hoja sin desplazar firmas.

### 🎓 Evaluaciones y Grupos Especiales [COMPLETADO]
1.  **Módulo de Calificación Integral (40-120 hrs):** Implementación de la captura para "Diagnóstica" y "Final" con guardado automático (*blur*).
2.  **Soporte de Base de Datos:** Se hizo nullable el campo `materia_id` en la tabla `calificaciones` para permitir registros de cursos basados en temarios/syllabus sin materias segmentadas.
3.  **Inteligencia de Interfaz:** Activación automática del formato especial al detectar cargas horarias de 40, 60, 80, 100 o 120 horas.
4.  **Formateo de Notas:** Homologación visual de calificaciones ("9.5" para parciales, "10" entero para perfectas) tanto en interfaz web como en PDF.

### 🏛️ Estructura Administrativa [COMPLETADO]
1.  **Tipificación de Grupos:** Inclusión del campo `tipo_grupo` (Municipal, Estatal y Fiscalía) en el ciclo de apertura y edición de cursos.
2.  **Analítica Demográfica:** Integración de estadísticas automáticas de género (Hombres/Mujeres) en la vista de detalle del grupo.

## 🛠️ Stack y Decisiones Técnicas
- **Estándar:** `Laravel 12` + `Livewire 3 (Volt)` + `Blade` + `Tailwind CSS`.
- **Database:** PostgreSQL (Manejo de integridades nullable para reportes holísticos).
- **Formatos:** DomPDF con soporte para `table-header-group` y `page-break-inside`.

## 🚀 Pendientes y Próximos Pasos (Retroalimentación Martes 24/Mar)

### 🔴 Prioridad Alta
1.  **Retro de Áreas (Planteles):** Validar la visualización de los nuevos reportes de 40 y 80 horas impresos físicamente.
2.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación.
3.  **Formato Fiscalía:** Desarrollar el reporte específico una vez que se entreguen las reglas de negocio de dicha adscripción.

### 🟡 Prioridad Media
4.  **Encuesta de Término:** Implementar el módulo de carga de resultados de encuestas de satisfacción física (para llenado por control escolar).

---
*Última actualización: 2026-03-18 13:20:00*
