# 🎯 Plan Estratégico: Rediseño de Identidad Universal SICOE (PostgreSQL)

Este documento detalla la nueva táctica de "Borrón y Cuenta Nueva" para la infraestructura de datos en PostgreSQL, integrando los tres niveles operativos: **Estatal**, **Municipal** y **Fiscalía**.

---

## 1. 🏛️ Arquitectura de Identidad Unificada

En lugar de tener tablas separadas, utilizaremos una tabla `users` enriquecida que servirá como el núcleo de identidad para todos los niveles, complementada con un campo de **Metadatos Dinámicos**.

### Estructura del Modelo `User`:
- **Campos Core (Fijos):**
  - `id`, `nombre`, `paterno`, `materno`
  - `curp` (Identificador único nacional)
  - `cuip`, `cup` (Identificadores policiales)
  - `sexo`, `fecha_nacimiento`
  - `email`, `password`, `tipo` (admin, docente, alumno)
- **Campos de Jurisdicción:**
  - `nivel` (Enum: `estatal`, `municipal`, `fiscalia`)
  - `plantel_id` (Relación con el plantel físico)
- **Campos de Perfil Flexible (JSONB):**
  - `perfil_data`: Columna tipo JSONB para almacenar datos específicos según el nivel sin necesidad de crear nuevas columnas:
    - *Municipal:* Municipio, Corporación, Distrito.
    - *Fiscalía:* Unidad Administrativa, Área Especializada, Cargo.
    - *Estatal:* Dependencia, Adscripción.

---

## 2. 📑 Reestructuración de Expedientes y Documentación

El sistema de expedientes se adaptará para que los documentos requeridos dependan del `nivel` del usuario.

- **`expedientes`:** Seguirá siendo 1:1 con `users`, pero con un `folio` único que identifique la procedencia (ej: `EST-001`, `MUN-001`, `FIS-001`).
- **`documentos_requeridos`:** Tabla de configuración donde definiremos qué documentos pide la Fiscalía vs qué documentos pide un Municipio.

---

## 3. 🕒 Plan de Ejecución Inmediata (30 min)

### Fase A: Limpieza Total
1. Resetear la base de datos `sicoe_pg` para eliminar artefactos de intentos previos.
2. Unificar las migraciones base (`users`, `planteles`, `expedientes`).

### Fase B: Despliegue de Esquema
1. Aplicar la nueva migración de `users` con el campo `nivel` y `perfil_data` (JSONB).
2. Crear la tabla `municipios` como catálogo base.

### Fase C: Sembrado de Datos Core
1. Insertar roles y permisos esenciales.
2. Insertar planteles actuales.
3. Crear el primer usuario administrador con perfiles mixtos.

---

## 4. ✅ Beneficios de esta Táctica
- **Cero Redundancia:** No repetimos datos personales en tablas distintas.
- **Flexibilidad Total:** Si mañana la Fiscalía pide un campo "Rh Negativo", no necesitamos migrar la base de datos, solo lo guardamos en el JSONB.
- **Reporteo Centralizado:** Un solo reporte puede filtrar personal de los 3 niveles simultáneamente.
