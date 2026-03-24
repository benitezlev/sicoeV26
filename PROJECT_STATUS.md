# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-03-24 16:15)

### 📥 Importación Masiva v2 [NUEVO]
1.  **Identidad Dinámica:** El proceso de importación ahora distingue entre **Activo (CUIP)** y **Aspirante (CURP)** para la validación de duplicados.
2.  **Auto-Inscripción:** Columna `grupo` integrada en el CSV para vincular automáticamente alumnos a grupos existentes durante la carga.
3.  **Plantilla Inteligente:** Nueva plantilla descargable con ejemplos para ambos perfiles de usuario.

### 📄 Formatos de Reporte [REFINADO]
1.  **Uniformidad Tipográfica:** Ajuste de fuentes en reportes PDF para alinear CUIP, Perfil y Adscripción con el estilo de nombres (8px Bold).

### 📊 Analítica y Rendimiento Académico
1.  **Dashboard de Aprobación:** Métricas de aprobados/reprobados con criterio dinámico ajustable.
2.  **Corrección de Género:** Sincronización de códigos `H/M` en controladores de PDF para estadísticas precisas.

### 🎓 Gestión de Matrícula y Trazabilidad
1.  **Bajas con Trazabilidad:** Proceso no destructivo con registro de fecha, motivo y responsable.
2.  **Reincorporación Directa:** Botón de reactivación que restaura el flujo académico sin pérdida de datos.

### 🚀 Pendientes y Próximos Pasos (Retroalimentación Martes 24/Mar)

### 🔴 Prioridad Alta
1.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación.
2.  **Reporte Fiscalía:** Desarrollar el reporte específico una vez que se entreguen las reglas de negocio.

---
*Última actualización: 2026-03-24 16:15:00*
