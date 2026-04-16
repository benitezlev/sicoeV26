# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-04-16 11:30)

### 🚀 ESTADO ACTUAL: PRODUCCIÓN V1 (ESTABLE - HARDENING COMPLETADO)
**Última actualización:** 16 de Abril de 2026

### ✅ Hitos Completados Hoy
- **Módulo de Docentes (Search Optimization):** Implementación de **Filtrado de Doble Capa** (API + PHP Local) que garantiza resultados inmediatos independientemente de la respuesta del SAD. Rediseño Premium de tarjetas y modales.
- **Hardening de Seguridad (Perfil Operador):** 
    - **Jurisdicción:** Filtro obligatorio por `plantel_id` para operadores en la vista de grupos.
    - **Restricción de Acciones:** Bloqueo (UI y Backend) para asignación de docentes, inscripciones masivas y bajas de grupos.
    - **Navegación Selectiva:** Sidebar simplificado; los operadores solo ven lo estrictamente necesario para la labor de aula.
- **Gestión de Personal:** Integración de los tipos de usuario **"Operador de Grupo"** y **"Control Escolar"** en el formulario de registro con mapeo automático de roles.

### 🚧 Próximos Pasos (Kraken Server)
- **Migración a Kraken:** Traslado de base de datos y media a entorno final. (Listo para comando de dump).
- **Firma Electrónica:** Generación de folios, firmas digitales y QR en actas.
- **Reporte FGJEM:** Formatos específicos para fiscalía.

### 📊 Optimización de Reportes y PDF
1.  **Formato Compacto de Asistencia:** Se redujeron las cabeceras de días a iniciales únicas (L, M, M, J, V) y se eliminaron las fechas por columna para maximizar espacio.
2.  **Identidad Visual (Plecas):** Reajuste de logos institucionales (35px) con alineación izquierda (R1) y centrada (R2), apilados verticalmente de forma equilibrada.
3.  **Lógica de Calificaciones:** Estandarización a 1 decimal para todas las notas (ej: 9.0), exceptuando el 10, que se muestra como entero puro.

### 👥 Gestión de Usuarios y Registro
1.  **Registro Manual Condicional:** El formulario de creación ahora discrimina entre "Aspirante" (solo pide CURP) y "Elemento Activo" (pide CURP y CUIP), simplificando la captura de datos.
2.  **Detección de Perfil:** Al editar, el sistema autodetecta el estatus del elemento basándose en la existencia del CUIP.

## 🚀 Pendientes y Próximos Pasos
1.  **Firma Electrónica Simple:** Implementar estampado de Hash de validación y Código QR en actas de calificación.
2.  **Reporte Fiscalía:** Desarrollar el reporte específico con reglas de negocio FGJEM.

---
*Última actualización: 2026-04-16 11:30:00*
