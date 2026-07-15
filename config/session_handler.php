<?php

/**
 * Guarda las sesiones PHP en PostgreSQL en lugar de archivos.
 * Esto las hace persistentes entre reinicios del contenedor en Railway.
 *
 * Tabla requerida (ejecutar una vez en Railway):
 *   CREATE TABLE IF NOT EXISTS php_sessions (
 *       id         VARCHAR(128) PRIMARY KEY,
 *       data       TEXT         NOT NULL DEFAULT '',
 *       expires_at TIMESTAMPTZ  NOT NULL
 *   );
 *   CREATE INDEX IF NOT EXISTS idx_php_sessions_expires ON php_sessions (expires_at);
 */
class DatabaseSessionHandler implements SessionHandlerInterface {

    private ?PDO $pdo = null;

    public function open(string $path, string $name): bool {
        try {
            $this->pdo = Database::getConnection();
            return true;
        } catch (Exception $e) {
            error_log('Session open error: ' . $e->getMessage());
            return false;
        }
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        if (!$this->pdo) return '';
        try {
            $stmt = $this->pdo->prepare(
                "SELECT data FROM php_sessions WHERE id = ? AND expires_at > NOW()"
            );
            $stmt->execute([$id]);
            return $stmt->fetchColumn() ?: '';
        } catch (Exception $e) {
            error_log('Session read error: ' . $e->getMessage());
            return '';
        }
    }

    public function write(string $id, string $data): bool {
        if (!$this->pdo) return false;
        try {
            $lifetime = (int)ini_get('session.gc_maxlifetime') ?: 1440;
            $this->pdo->prepare("
                INSERT INTO php_sessions (id, data, expires_at)
                VALUES (?, ?, NOW() + (? || ' seconds')::INTERVAL)
                ON CONFLICT (id) DO UPDATE SET
                    data       = EXCLUDED.data,
                    expires_at = EXCLUDED.expires_at
            ")->execute([$id, $data, $lifetime]);
            return true;
        } catch (Exception $e) {
            error_log('Session write error: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $id): bool {
        if (!$this->pdo) return false;
        try {
            $this->pdo->prepare("DELETE FROM php_sessions WHERE id = ?")
                      ->execute([$id]);
            return true;
        } catch (Exception $e) {
            error_log('Session destroy error: ' . $e->getMessage());
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log('Session gc error: ' . $e->getMessage());
            return false;
        }
    }
}

session_set_save_handler(new DatabaseSessionHandler(), true);
