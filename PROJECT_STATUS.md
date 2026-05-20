# 📋 Bitácora de Estado del Proyecto - SICOE

Este documento resume el estado actual del proyecto, los cambios recientes y las tareas pendientes para facilitar la continuidad del desarrollo.

## 🕒 Últimos Cambios Importantes (2026-05-20 12:54)

### 🚀 ESTADO ACTUAL: PRODUCCIÓN V1 (ESTABLE - READY)
**Última actualización:** 20 de Mayo de 2026

### 🔒 ESTATUS DEL COPILOTO IA: CHAT CON MEMORIA CONTEXTUAL & LÍMITES DE SEGURIDAD (COMPLETO - OPTIMIZADO)
*El copiloto IA ahora incluye memoria conversacional completa, inyección de límites de rendimiento en queries y prevención activa de colisión de columnas duplicadas. Se ha integrado un Healthcheck Dinámico con timeout de 2s en el arranque del Dashboard. Si el servidor Ollama no responde, el widget entra en suspensión de forma automática impidiendo peticiones fallidas o retrasos de carga de página. La IP/Endpoint de Ollama ahora es dinámica mediante la variable de entorno `OLLAMA_API_BASE` para producción (Kraken Server).*

### ✅ Hitos Completados Hoy
- **Optimización y Estabilización de Carga Masiva (ZIP):**
    - **Corrección de Subida de Archivos:** Solución definitiva de la excepción `MissingFileUploadsTraitException` en el componente Volt `expedientes.import-zip` integrando el helper `usesFileUploads()`.
    - **Soporte Dinámico de Identificadores (CURP/CUIP):** Reestructuración del Job `ProcessZipImport` para procesar archivos de manera asíncrona mediante longitud de cadena: detecta si es una CURP (18 caracteres) o CUIP (22 caracteres) para realizar la consulta correspondiente en base de datos.
    - **Búsqueda Robusta Fallback:** Integración de un fallback que busca en ambas columnas en caso de formatos no estándar.
    - **Prevención de Acumulación y Limpieza:** Implementación de limpieza automática que elimina del expediente físico (Spatie MediaLibrary) y base de datos cualquier documento anterior del mismo tipo antes de guardar el nuevo archivo importado, conservando la integridad de almacenamiento.
- **Infraestructura de Git y Sincronización Remota:**
    - Configuración y generación de llave segura SSH Ed25519 para autenticación sin contraseña en WSL.
    - Reconfiguración del repositorio remoto `origin` para utilizar el protocolo SSH (`git@github.com:benitezlev/sicoeV26.git`).
    - Sincronización completa (push) de todos los 19 commits acumulados hacia la rama `main` en GitHub.
- **Healthcheck Dinámico e Interfaz Reactiva (Ollama):**
    - Creación del método `isServerOnline` en `OllamaService` con timeout bajo (2 segundos) para probar de forma ultraligera el servidor de IA.
    - Integración de validación automática en el arranque del dashboard en `copiloto.blade.php`.
    - Diseño ultra-premium en Livewire Volt y Alpine.js con badge de advertencia animada y botón de reintento si el servidor se encuentra fuera de línea.
    - Migración de las variables de entorno de `OLLAMA_HOST` a la variable estándar `OLLAMA_API_BASE` en `.env.example`, `.env` y `config/services.php`.
- **SICOE Copiloto IA Local (Ollama 192.168.3.4):**
    - **Servicio Conector:** Creación de `OllamaService` para comunicar Laravel de forma síncrona y segura con la API local de Ollama en el puerto `11434` (utilizando el modelo `llama3:latest` presente en el servidor).
    - **Optimización de Memoria Conversacional (Endpoint `/api/chat`):** Migración del servicio síncrono al endpoint `/api/chat`, permitiendo mantener un contexto y memoria completos del chat. Mapeo de todo el historial de Livewire a roles (`system`, `user`, `assistant`), incluyendo el System Prompt dinámico.
    - **Inyección Automática de Límites (`LIMIT 100`):** Blindaje preventivo que intercepta y formatea consultas SELECT de PostgreSQL. Si la consulta generada no define un límite, se le inyecta automáticamente un `LIMIT 100` por seguridad del servidor, alertando en la interfaz al operador con un badge premium en color ámbar.
    - **Prevención de Alias Duplicados:** Modificación del System Prompt para forzar al modelo a evitar consultas ambiguas o duplicadas (`SELECT *`) en relaciones JOIN y exigir alias claros (`AS`) en cada columna para impedir errores de mapeo en PHP.
    - **Motor de Inferencia Text-to-SQL:** Definición de un System Prompt sumamente robusto con la especificación exacta de las tablas de PostgreSQL, incluyendo ahora las tablas de seguridad de Spatie (`roles`, `model_has_roles`), permitiendo consultas analíticas sobre permisos y roles de usuarios.
    - **Blindaje y Sanitización:** Motor de seguridad en Laravel que limpia, extrae y analiza sintácticamente el SQL generado por la IA, eliminando prefijos redundantes (como bloques con literal `sql`) y denegando comandos destructivos (`INSERT`, `UPDATE`, `DROP`, `DELETE`, `ALTER`) para garantizar una ejecución 100% segura.
    - **Asistente de Chat Flotante Global:** Transformación del chat en un widget flotante (estilo Intercom) disponible en todas las pantallas de SICOE a través de un Botón de Acción Flotante (FAB) animado con Alpine.js.
    - **Control y Modo de Standby (Cero Consumo):** Habilitación del componente para los roles `superadmin` y `control_escolar` (para que el equipo de Control Escolar pueda realizar sus pruebas). Implementación de un modo Standby que, al apagarse el servicio, suspende por completo toda conexión de red o consulta a la API de Ollama en tu servidor de Ollama para conservar recursos. El botón interactivo de encendido/apagado está reservado exclusivamente para el `superadmin`, mientras que el equipo de `control_escolar` visualiza un badge informativo estático del estatus ("Activo"/"Inactivo").
    - **Pruebas de Seguridad (Pest PHP):** Suite de pruebas en `tests/Feature/IA/OllamaServiceTest.php` validando la sanitización correcta del SQL, bloques redundantes, inyección de límites de queries (`LIMIT 100`) y bloqueando inyecciones de queries no autorizadas.
- **Módulo de Recursos Oficiales (Financiamiento):**
    - **Esquema de Base de Datos:** Creación de la tabla `recursos` y llave foránea opcional `recurso_id` en `grupos`.
    - **Modelo Eloquent:** Modelo `Recurso` con relación `hasMany` hacia `Grupo` y relación inversa en `Grupo` integrada.
    - **UI Livewire Volt:** Dropdown en el modal de apertura/edición de grupo para asignar el fondo, y un badge distintivo (💰) en el directorio de grupos que indica el origen presupuestal.
- **Cuadro Estadístico y Dashboard Financiero-Poblacional:**
    - **Analítica de Capacitados:** Panel ejecutivo que muestra la matrícula total de alumnos capacitados vigentes (excluyendo bajas de grupos).
    - **Desglose de Género en Tiempo Real:** Métricas proporcionales de hombres y mujeres inscritos en los programas curriculares.
- **Módulo de Metas de Capacitación Anuales:**
    - **Esquema de Base de Datos:** Creación de la tabla `metas_capacitacion` con restricción de unicidad para el campo `anio`.
    - **Panel Comparativo en Dashboard:** Incorporación de un bloque visual interactivo que realiza la comparación paralela de la Meta Programada contra el Avance Registrado real de cada año.
- **Correcciones de Autenticación y Sincronización de Base de Datos (PostgreSQL):**
    - **Sincronización de Secuencias:** Sincronización de la secuencia de IDs de usuarios (`users_id_seq`) tras la inserción manual del administrador de sistema (`id: 1`).
    - **Blindaje de Entorno de Tests:** Automatización de borrado de caché de configuración al inicio de cualquier prueba para evitar la limpieza accidental de la base de datos de desarrollo.

### 🚧 Próximos Pasos (Kraken Server)
- **Firma Electrónica:** Generación de folios, firmas digitales y QR en actas.
- **Migración a Kraken:** Traslado de base de datos y media a entorno final.

---
*Última actualización: 2026-05-20 09:41:00*
