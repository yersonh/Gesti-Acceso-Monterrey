<?php
/**
 * sync_core.php — Sincroniza dependencias_cache, funcionarios_cache y
 * ciudadanos_cache desde Core Institucional.
 *
 * Uso:
 *   php scripts/sync_core.php              -> sincroniza todo
 *   php scripts/sync_core.php dependencias  -> solo dependencias
 *   php scripts/sync_core.php funcionarios  -> solo funcionarios (+ funcionario_dependencia)
 *   php scripts/sync_core.php ciudadanos    -> solo ciudadanos
 *
 * Pensado para correr por cron, o manualmente para el primer llenado.
 */

require_once __DIR__ . '/../config/database.php';

class CoreSyncClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(getenv('CORE_API_URL') ?: '', '/');
        $this->token   = getenv('CORE_API_TOKEN') ?: '';

        if (!$this->baseUrl || !$this->token) {
            throw new Exception('CORE_API_URL o CORE_API_TOKEN no configurados en .env');
        }
    }

    public function get(string $path, array $query = []): array
    {
        $url = "{$this->baseUrl}/{$path}";
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->token}",
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error de conexión a Core ({$path}): {$error}");
        }
        if ($httpCode >= 400) {
            throw new Exception("Core devolvió HTTP {$httpCode} en {$path}: " . substr($body, 0, 300));
        }

        $data = json_decode($body, true);
        if ($data === null) {
            throw new Exception("Respuesta de Core no es JSON válido en {$path}");
        }

        return $data;
    }

    // Trae TODAS las páginas de un endpoint paginado (funcionarios, ciudadanos, etc.)
    public function getTodasLasPaginas(string $path, array $query = []): array
    {
        $todos = [];
        $page = 1;

        do {
            $query['page'] = $page;
            $query['per_page'] = 100;
            $respuesta = $this->get($path, $query);

            $data = $respuesta['data'] ?? [];
            $todos = array_merge($todos, $data);

            $ultimaPagina = $respuesta['last_page'] ?? $page;
            $page++;
        } while ($page <= $ultimaPagina);

        return $todos;
    }
}

class SyncRunner
{
    private PDO $db;
    private CoreSyncClient $core;
    private array $mapaTipoIdentificacion = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->core = new CoreSyncClient();
    }

    private function log(string $mensaje): void
    {
        $fecha = date('Y-m-d H:i:s');
        echo "[{$fecha}] {$mensaje}\n";
    }

    // Carga el catálogo de tipos de identificación de Core una sola vez
    // y arma el mapa id -> codigo (ej. 1 -> 'CC')
    private function cargarMapaTipoIdentificacion(): void
    {
        if ($this->mapaTipoIdentificacion) {
            return;
        }
        $tipos = $this->core->get('tipos-identificacion');
        foreach ($tipos as $tipo) {
            $this->mapaTipoIdentificacion[$tipo['id']] = $tipo['codigo'];
        }
    }

    private function codigoTipoIdentificacion(?int $id): string
    {
        $this->cargarMapaTipoIdentificacion();
        return $this->mapaTipoIdentificacion[$id] ?? 'CC'; // fallback defensivo, no debería pasar
    }

    public function syncDependencias(): int
    {
        $this->log('Sincronizando dependencias...');
        $dependencias = $this->core->get('dependencias');

        $stmt = $this->db->prepare("
            INSERT INTO dependencias_cache (id_dependencia, nombre, descripcion, activo, synced_at)
            VALUES (:id, :nombre, :descripcion, :activo, CURRENT_TIMESTAMP)
            ON CONFLICT (id_dependencia) DO UPDATE SET
                nombre = EXCLUDED.nombre,
                descripcion = EXCLUDED.descripcion,
                activo = EXCLUDED.activo,
                synced_at = CURRENT_TIMESTAMP
        ");

        $contador = 0;
        foreach ($dependencias as $dep) {
            $stmt->execute([
                ':id' => $dep['id'],
                ':nombre' => $dep['nombre'],
                ':descripcion' => $dep['descripcion'] ?? null,
                ':activo' => $dep['activo'] ?? true,
            ]);
            $contador++;
        }

        $this->log("Dependencias sincronizadas: {$contador}");
        return $contador;
    }

    public function syncFuncionarios(): int
    {
        $this->log('Sincronizando funcionarios (puede tardar varias páginas)...');
        $funcionarios = $this->core->getTodasLasPaginas('funcionarios');

        $stmtFuncionario = $this->db->prepare("
            INSERT INTO funcionarios_cache
                (id_funcionario, nombres, apellidos, tipo_identificacion, numero_identificacion,
                 telefono, email, cargo, activo, synced_at)
            VALUES
                (:id, :nombres, :apellidos, :tipo_id, :numero_id,
                 :telefono, :email, :cargo, :activo, CURRENT_TIMESTAMP)
            ON CONFLICT (id_funcionario) DO UPDATE SET
                nombres = EXCLUDED.nombres,
                apellidos = EXCLUDED.apellidos,
                tipo_identificacion = EXCLUDED.tipo_identificacion,
                numero_identificacion = EXCLUDED.numero_identificacion,
                telefono = EXCLUDED.telefono,
                email = EXCLUDED.email,
                cargo = EXCLUDED.cargo,
                activo = EXCLUDED.activo,
                synced_at = CURRENT_TIMESTAMP
        ");

        // funcionario_dependencia: gobernada 100% por Core. Un funcionario = una
        // dependencia. Si cambió en Core, se actualiza; si no existía, se crea.
        $stmtDependenciaUpsert = $this->db->prepare("
            INSERT INTO funcionario_dependencia (funcionario_id, dependencia_id)
            VALUES (:funcionario_id, :dependencia_id)
            ON CONFLICT (funcionario_id, dependencia_id) DO NOTHING
        ");
        $stmtDependenciaLimpiar = $this->db->prepare("
            DELETE FROM funcionario_dependencia
            WHERE funcionario_id = :funcionario_id AND dependencia_id != :dependencia_id
        ");

        $contador = 0;
        foreach ($funcionarios as $func) {
            $persona = $func['persona'] ?? [];

            $stmtFuncionario->execute([
                ':id' => $func['id'],
                ':nombres' => $persona['nombres'] ?? '',
                ':apellidos' => $persona['apellidos'] ?? '',
                ':tipo_id' => $this->codigoTipoIdentificacion($persona['tipo_identificacion_id'] ?? null),
                ':numero_id' => $persona['numero_identificacion'] ?? '',
                ':telefono' => $persona['telefono'] ?? null,
                ':email' => $persona['email'] ?? null,
                ':cargo' => $func['cargo'] ?? null,
                ':activo' => $func['activo'] ?? true,
            ]);

            if (!empty($func['dependencia_id'])) {
                // Primero elimina asignaciones viejas a otra dependencia (si cambió en Core)
                $stmtDependenciaLimpiar->execute([
                    ':funcionario_id' => $func['id'],
                    ':dependencia_id' => $func['dependencia_id'],
                ]);
                // Luego asegura que exista la asignación actual
                $stmtDependenciaUpsert->execute([
                    ':funcionario_id' => $func['id'],
                    ':dependencia_id' => $func['dependencia_id'],
                ]);
            }

            $contador++;
        }

        $this->log("Funcionarios sincronizados: {$contador}");
        return $contador;
    }

    public function syncCiudadanos(): int
    {
        $this->log('Sincronizando ciudadanos (puede tardar varias páginas)...');
        $ciudadanos = $this->core->getTodasLasPaginas('ciudadanos');

        $stmt = $this->db->prepare("
            INSERT INTO ciudadanos_cache
                (id_ciudadano, nombres, apellidos, tipo_identificacion, numero_identificacion,
                 telefono, email, direccion, proveniencia, whatsapp, activo, synced_at)
            VALUES
                (:id, :nombres, :apellidos, :tipo_id, :numero_id,
                 :telefono, :email, :direccion, :proveniencia, :whatsapp, :activo, CURRENT_TIMESTAMP)
            ON CONFLICT (id_ciudadano) DO UPDATE SET
                nombres = EXCLUDED.nombres,
                apellidos = EXCLUDED.apellidos,
                tipo_identificacion = EXCLUDED.tipo_identificacion,
                numero_identificacion = EXCLUDED.numero_identificacion,
                telefono = EXCLUDED.telefono,
                email = EXCLUDED.email,
                direccion = EXCLUDED.direccion,
                proveniencia = EXCLUDED.proveniencia,
                whatsapp = EXCLUDED.whatsapp,
                activo = EXCLUDED.activo,
                synced_at = CURRENT_TIMESTAMP
        ");

        $contador = 0;
        foreach ($ciudadanos as $ciu) {
            $stmt->execute([
                ':id' => $ciu['id'],
                ':nombres' => $ciu['nombres'] ?? '',
                ':apellidos' => $ciu['apellidos'] ?? '',
                ':tipo_id' => $this->codigoTipoIdentificacion($ciu['tipo_identificacion_id'] ?? null),
                ':numero_id' => $ciu['numero_identificacion'] ?? '',
                ':telefono' => $ciu['telefono'] ?? null,
                ':email' => $ciu['email'] ?? null,
                ':direccion' => $ciu['direccion'] ?? null,
                ':proveniencia' => $ciu['proveniencia'] ?? null,
                ':whatsapp' => $ciu['whatsapp'] ?? null,
                ':activo' => $ciu['activo'] ?? true,
            ]);
            $contador++;
        }

        $this->log("Ciudadanos sincronizados: {$contador}");
        return $contador;
    }

    public function syncTodo(): void
    {
        $inicio = microtime(true);
        $this->syncDependencias();
        $this->syncFuncionarios();
        $this->syncCiudadanos();
        $duracion = round(microtime(true) - $inicio, 2);
        $this->log("Sync completo en {$duracion}s");
    }
}

// ── Entrypoint CLI ────────────────────────────────────────────────
try {
    $runner = new SyncRunner();
    $tarea = $argv[1] ?? 'todo';

    switch ($tarea) {
        case 'dependencias':
            $runner->syncDependencias();
            break;
        case 'funcionarios':
            $runner->syncFuncionarios();
            break;
        case 'ciudadanos':
            $runner->syncCiudadanos();
            break;
        case 'todo':
            $runner->syncTodo();
            break;
        default:
            echo "Uso: php sync_core.php [dependencias|funcionarios|ciudadanos|todo]\n";
            exit(1);
    }
} catch (Exception $e) {
    error_log('sync_core.php ERROR: ' . $e->getMessage());
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}