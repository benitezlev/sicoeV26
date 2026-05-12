# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-05-12 11:00)

### 🚀 ESTADO ACTUAL: PRODUCCIÓN V1 (ESTABLE - READY)
**Última actualización:** 12 de Mayo de 2026

### ✅ Hitos Completados Hoy
- **Módulo de Recursos Oficiales (Financiamiento):**
    - **Esquema de Base de Datos:** Creación de la tabla `recursos` y llave foránea opcional `recurso_id` en `grupos` (`nullOnDelete` habilitado).
    - **Sembrado (Seeder):** Recursos oficiales base disponibles: `FASP`, `FORTAMUN`, `Recurso Propio`, `Recurso Estatal` y `Recurso Federal`.
    - **Modelo Eloquent:** Modelo `Recurso` con relación `hasMany` hacia `Grupo` y relación inversa en `Grupo` integrada y registrada en fillable.
    - **UI Livewire Volt:** Dropdown en el modal de apertura/edición de grupo para asignar el fondo, y un badge distintivo (💰) en el directorio de grupos que indica el origen presupuestal.
    - **Testing (Pest PHP):** Suite de pruebas unitarias/integración robusta cubriendo la creación de recursos y su correcta vinculación con grupos académica y financieramente.
- **Cuadro Estadístico y Dashboard Financiero-Poblacional:**
    - **Analítica de Capacitados:** Panel ejecutivo que muestra la matrícula total de alumnos capacitados vigentes (excluyendo bajas de grupos).
    - **Desglose de Género en Tiempo Real:** Métricas proporcionales de hombres y mujeres inscritos en los programas curriculares.
    - **Desglose por Recurso Financiero:** Tabulación interactiva de capacitados fondeados por cada partida presupuestal (`FASP`, `FORTAMUN`, etc.), desglosados adicionalmente por sexo de cada elemento.
    - **Desglose por Plantel/Campus:** Tabulación proporcional que distribuye la matrícula de capacitados por cada sede operativa del sistema.
- **Correcciones de Autenticación y Sincronización de Base de Datos (PostgreSQL):**
    - **Sincronización de Secuencias:** Incorporación de un comando SQL nativo en `DatabaseSeeder` para sincronizar la secuencia de IDs de usuarios (`users_id_seq`) tras la inserción manual del administrador de sistema (`id: 1`). Esto previene colisiones de unicidad en Postgres.
    - **Orquestación de Seeders:** Vinculación directa del sembrador maestro `DatabaseSetupSeeder` en lugar del obsoleto `RolesAndPermissionsSeeder`, permitiendo poblar correctamente todos los roles operativos, permisos de sistema y los accesos predeterminados del usuario operador, control escolar, y superadmin.
    - **Robustez en CLI:** Corrección del comando informativo en el seeder para evitar excepciones de puntero nulo (`null pointer`) en ambientes virtuales sin interfaz de salida (Tinker y Pest Testing).

### 🚧 Próximos Pasos (Kraken Server)
- **Firma Electrónica:** Generación de folios, firmas digitales y QR en actas.
- **Migración a Kraken:** Traslado de base de datos y media a entorno final.

---
*Última actualización: 2026-05-12 11:00:00*
