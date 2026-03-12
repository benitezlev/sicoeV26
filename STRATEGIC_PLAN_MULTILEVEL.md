# 🎯 Plan Estratégico: Expansión Multi-Nivel SICOE

Este documento detalla la estrategia técnica y arquitectónica para integrar los tres niveles de capacitación solicitados: **Estatal**, **Municipal** y **Certificaciones CONOCER**, asegurando escalabilidad y eficiencia.

---

## 1. 📊 Recomendación de Base de Datos: Migración a PostgreSQL

**Recomendación:** **SÍ, migrar a PostgreSQL.**

### ¿Por qué PostgreSQL para este nuevo alcance?
1. **JSONB de Alto Rendimiento:** Dado que aún no tenemos los campos definidos para Municipios y CONOCER, PostgreSQL permite almacenar datos dinámicos en formato `JSONB` con la capacidad de crear índices (GIN) sobre esos campos. Esto nos permite agregar campos "al vuelo" sin alterar la estructura física de la tabla cada semana.
2. **Consultas Complejas y Concurrencia:** Al manejar múltiples niveles con procesos distintos, las consultas serán más pesadas. PostgreSQL gestiona mejor el bloqueo de filas y la escritura concurrente.
3. **Escalabilidad Geográfica:** Si a futuro se requiere mapear planteles municipales, PostGIS (extensión de Postgres) es el estándar de la industria.

---

## 2. 🏛️ Estrategia de Arquitectura de Datos (Usuario Unificado)

Para evitar duplicar tablas o tener una tabla por cada nivel, implementaremos un modelo de **Identidad Universal con Perfiles Flexibles**.

### Estructura Propuesta:
| Tabla | Propósito | Comentarios |
| :--- | :--- | :--- |
| `users` | **Identidad Core** | Datos permanentes e iguales: CURP, Nombre, Email, Password, Sexo, Foto. |
| `expedientes` | **Contexto de Nivel** | Relación 1:1 con `users`. Contendrá campos comunes de seguridad. |
| `metadata` (JSONB) | **Campos Dinámicos** | Dentro de la tabla `users` o `expedientes`. Aquí guardaremos: |
| | *Municipal:* | Municipio, Clave de Ayuntamiento, Distrito. |
| | *CONOCER:* | ID de Estándar, Folio de Certificación, Fecha de Renovación. |

---

## 3. 🗺️ Mapa de Ruta Estratégico (Roadmap)

### Fase 1: Infraestructura (Mañana)
- [ ] **Configuración PostgreSQL:** Preparar el entorno para recibir la migración.
- [ ] **Migración de Datos:** Pasar la base actual de MySQL a Postgres.
- [ ] **Actualización de Eloquent:** Ajustar el modelo `User` para manejar columnas JSON.

### Fase 2: Modularización del Front-end
- [ ] **Componentes Base Reutilizables:** Crearemos una librería de inputs de Flux UI que se adapten al nivel del usuario.
- [ ] **Lógica de "Nivel":** Implementar un `Middleware` que identifique si el administrador está operando en nivel Estatal, Municipal o CONOCER.

### Fase 3: Integración de Campos Específicos
- [ ] Una vez recibidos los campos de Municipios y CONOCER, solo necesitaremos actualizar el esquema JSON, no las migraciones de base de datos.

---

## 4. 📝 Acción Inmediata (Bitácora)

Se ha actualizado la bitácora de estado con el compromiso de iniciar la migración y la reestructuración de identidad el día de mañana.

> **Nota para el equipo:** El objetivo es que SICOE sea una "Multi-tenancy" interna, donde el sistema detecte el nivel del usuario y presente las interfaces correspondientes sin cambiar la base del código.
