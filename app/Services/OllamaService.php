<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OllamaService
{
    protected string $host;
    protected string $model;

    public function __construct()
    {
        $this->host = rtrim(config('services.ollama.host', 'http://192.168.3.4:11434'), '/');
        $this->model = config('services.ollama.model', 'qwen2.5-coder:7b');
    }

    /**
     * Realiza un healthcheck rápido al servidor Ollama con un timeout bajo.
     */
    public function isServerOnline(int $timeoutSeconds = 2): bool
    {
        try {
            $response = Http::timeout($timeoutSeconds)->get($this->host);
            return $response->status() === 200 || $response->successful();
        } catch (\Exception $e) {
            Log::warning("Ollama Server Healthcheck fallido en {$this->host}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía una petición síncrona a la API de Ollama (/api/generate).
     */
    public function generate(string $prompt, string $systemPrompt = ''): ?string
    {
        try {
            $response = Http::timeout(35)->post("{$this->host}/api/generate", [
                'model'  => $this->model,
                'prompt' => $prompt,
                'system' => $systemPrompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.1, // Baja temperatura para precisión en código/SQL
                ]
            ]);

            if ($response->successful()) {
                return $response->json('response');
            }
        } catch (\Exception $e) {
            Log::error("Error de comunicación con Ollama local (192.168.3.4): " . $e->getMessage());
        }

        return null;
    }

    /**
     * Obtiene el esquema resumido de la base de datos de SICOE para instruir a la IA.
     */
    public function getSicoeSchemaPrompt(): string
    {
        return <<<EOT
Eres SICOE-IA, el Copiloto Inteligente de Analítica de Capacitación del SICOE.
Tu misión es recibir una pregunta del usuario en español, entender la intención de negocio y generar ÚNICAMENTE una consulta SELECT de PostgreSQL para obtener el resultado matemático o estadístico.

### REGLAS ABSOLUTAS:
1. Retorna la consulta SQL encerrada en bloques de código triple comilla invertida con la etiqueta "sql", por ejemplo:
   ```sql
   SELECT ...
   ```
2. La consulta SQL debe ser estrictamente de lectura (SELECT). No uses INSERT, UPDATE, DELETE, DROP o ALTER.
3. Asegúrate de que las búsquedas de texto usen "ILIKE" para que sean insensibles a mayúsculas y acentos.
4. No asumas columnas que no existan. Sigue estrictamente la descripción de tablas abajo.
5. No des explicaciones en lenguaje natural dentro del bloque de SQL. Solo pon el SQL limpio.

### ESQUEMA DE TABLAS DISPONIBLES EN POSTGRESQL:

1. Tabla `users` (Elementos/Alumnos/Usuarios):
   - id (bigint, PK)
   - nombre (varchar)
   - paterno (varchar)
   - materno (varchar)
   - email (varchar)
   - sexo (varchar: 'H' para hombre, 'M' para mujer)
   - tipo (varchar: 'aspirante', 'empleado')
   - nivel (varchar: 'estatal', 'municipal', 'federal')

2. Tabla `grupos` (Cursos en curso/concluidos):
   - id (bigint, PK)
   - nombre (varchar: nombre del grupo)
   - plantel_id (bigint, FK a planteles)
   - recurso_id (bigint, FK a recursos, nullable)
   - fecha_inicio (date)
   - fecha_fin (date)

3. Tabla `grupo_user` (Inscripción / Relación de elementos en grupos):
   - grupo_id (bigint, FK a grupos)
   - user_id (bigint, FK a users)
   - estado (varchar: 'activo', 'baja', 'aprobado', 'reprobado')

4. Tabla `recursos` (Fuentes de Financiamiento):
   - id (bigint, PK)
   - nombre (varchar: denominación, ej. 'FASP', 'FORTAMUN', 'Recurso Propio')
   - clave (varchar: ej. 'FTMN-2026', 'FASP-2026')
   - activo (boolean)

5. Tabla `planteles` (Sedes operativas/Planteles):
   - id (bigint, PK)
   - name (varchar: nombre del plantel, ej. 'Toluca', 'Tlalnepantla', 'Nezahualcóyotl')

6. Tabla `metas_capacitacion` (Metas Anuales de Capacitación):
   - id (bigint, PK)
   - anio (integer, único: ej. 2026)
   - meta (integer, número programado de elementos a capacitar)

7. Tabla `roles` (Roles de Spatie):
   - id (bigint, PK)
   - name (varchar: 'superadmin', 'control_escolar', 'operador', 'admin_ti', 'docente', 'alumno')

8. Tabla `model_has_roles` (Asociación de usuarios y roles):
   - role_id (bigint, FK a roles)
   - model_id (bigint, FK a users)
   - model_type (varchar: 'App\Models\User')

### EJEMPLOS DE CONSULTAS:
- Alumnos totales en activo: `SELECT COUNT(DISTINCT user_id) FROM grupo_user WHERE estado != 'baja';`
- Alumnos desglosados por sexo: `SELECT users.sexo, COUNT(DISTINCT users.id) FROM users JOIN grupo_user ON users.id = grupo_user.user_id WHERE grupo_user.estado != 'baja' GROUP BY users.sexo;`
- Capacitados financiados por FASP: `SELECT COUNT(DISTINCT gu.user_id) FROM grupo_user gu JOIN grupos g ON gu.grupo_id = g.id JOIN recursos r ON g.recurso_id = r.id WHERE r.nombre ILIKE '%FASP%' AND gu.estado != 'baja';`
- Usuarios con rol operador: `SELECT COUNT(DISTINCT m.model_id) FROM model_has_roles m JOIN roles r ON m.role_id = r.id WHERE r.name = 'operador';`
- Listado de roles disponibles: `SELECT name FROM roles;`

Retorna únicamente el SQL rodeado por ```sql y ```.
EOT;
    }

    /**
     * Procesa y ejecuta de forma segura el SQL sugerido por el modelo.
     */
    public function executeSecureQuery(string $rawAiOutput): array
    {
        // 1. Extraer el bloque de código SQL
        if (preg_match('/```sql\s*(.*?)\s*```/is', $rawAiOutput, $matches)) {
            $sql = trim($matches[1]);
        } elseif (preg_match('/```\s*(.*?)\s*```/is', $rawAiOutput, $matches)) {
            $sql = trim($matches[1]);
        } else {
            // Intentar buscar un SELECT si no hay formato markdown
            if (stripos($rawAiOutput, 'SELECT') !== false) {
                $sql = trim($rawAiOutput);
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo identificar una consulta SQL válida en la respuesta del agente.',
                    'sql' => null,
                    'rows' => []
                ];
            }
        }

        // Remover punto y coma al final si existe
        $sql = rtrim(trim($sql), ';');

        // 2. Sanitización y validación estricta de seguridad
        // Remover un literal "sql" o "SQL" al principio de la cadena si el modelo lo incluyó erróneamente en el bloque de código
        $cleanSql = preg_replace('/^(sql|SQL)\s+/i', '', trim($sql));
        $cleanSql = trim($cleanSql);
        
        // Debe comenzar estrictamente con SELECT
        if (stripos($cleanSql, 'SELECT') !== 0) {
            return [
                'success' => false,
                'message' => 'Acción denegada por seguridad: La consulta generada no es de solo lectura.',
                'sql' => $cleanSql,
                'rows' => []
            ];
        }

        // Denegar comandos destructivos o maliciosos
        $forbiddenKeywords = ['insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'grant', 'revoke', 'pg_', 'schema', 'information_schema'];
        foreach ($forbiddenKeywords as $keyword) {
            if (preg_match("/\b{$keyword}\b/i", $cleanSql)) {
                return [
                    'success' => false,
                    'message' => "Acción denegada por seguridad: Palabra reservada prohibida '{$keyword}' detectada.",
                    'sql' => $cleanSql,
                    'rows' => []
                ];
            }
        }

        // 3. Ejecución segura con manejo de excepciones
        try {
            $results = DB::select($cleanSql);
            
            // Convertir stdClass a array asociativo
            $rows = array_map(function ($item) {
                return (array) $item;
            }, $results);

            return [
                'success' => true,
                'message' => 'Consulta ejecutada con éxito de forma local.',
                'sql' => $cleanSql,
                'rows' => $rows
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de sintaxis SQL al ejecutar: ' . $e->getMessage(),
                'sql' => $cleanSql,
                'rows' => []
            ];
        }
    }
}
