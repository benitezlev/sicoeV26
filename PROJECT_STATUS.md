# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-04-15 17:58)

### 📊 Optimización de Reportes y PDF [REFINADO]
1.  **Formato Compacto de Asistencia:** Se redujeron las cabeceras de días a iniciales únicas (L, M, M, J, V) y se eliminaron las fechas por columna para maximizar espacio.
2.  **Identidad Visual (Plecas):** Reajuste de logos institucionales (35px) con alineación izquierda (R1) y centrada (R2), apilados verticalmente de forma equilibrada.
3.  **Lógica de Calificaciones:** Estandarización a 1 decimal para todas las notas (ej: 9.0), exceptuando el 10, que se muestra como entero puro.
4.  **Identidad Predominante:** Implementación de jerarquía de datos: Si el alumno tiene CUIP (activo) se muestra ese; si no, se muestra el CURP (aspirante).

### 📝 Control Escolar y Asistencia [NEW]
1.  **Ciclo de Asistencia 3-Estados:** Transición interactiva en la vista de grupo: Ausente (F) -> Presente (Check) -> Justificado (J).
2.  **Justificación de Faltas:** Nuevo flujo con modal para capturar motivos oficiales (Médico, Institucional, Personal, etc.), vinculando observaciones al registro de inasistencia.
3.  **Indicadores Visuales en Web:** Sincronización de la web con el PDF para mostrar 'F' (Falta) o 'J' (Justificado) según corresponda.

### 👥 Gestión de Usuarios y Registro [REFINADO]
1.  **Registro Manual Condicional:** El formulario de creación ahora discrimina entre "Aspirante" (solo pide CURP) y "Elemento Activo" (pide CURP y CUIP), simplificando la captura de datos.
2.  **Detección de Perfil:** Al editar, el sistema autodetecta el estatus del elemento basándose en la existencia del CUIP.

## 🚀 Pendientes y Próximos Pasos
1.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación.
2.  **Reporte Fiscalía:** Desarrollar el reporte específico con reglas de negocio FGJEM.
3.  **Hardening de Seguridad:** Revisar políticas de acceso para perfiles de Operador y Docente.

---
*Última actualización: 2026-04-15 17:58:00*
