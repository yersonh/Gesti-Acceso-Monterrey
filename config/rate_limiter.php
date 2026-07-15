<?php

/**
 * Rate limiting para el formulario de login, basado en IP.
 *
 * Tabla requerida (ejecutar una vez en Railway):
 *   CREATE TABLE IF NOT EXISTS login_attempts (
 *       id         SERIAL       PRIMARY KEY,
 *       ip         VARCHAR(45)  NOT NULL,
 *       email      VARCHAR(255),
 *       created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
 *   );
 *   CREATE INDEX IF NOT EXISTS idx_login_attempts_ip
 *       ON login_attempts (ip, created_at);
 */
class RateLimiter {

    private const MAX_ATTEMPTS    = 5;
    private const WINDOW_SECONDS  = 900; // 15 minutos

    // Detecta la IP real del cliente, considerando el proxy de Railway.
    private static function getIp(): string {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded) {
            $ip = trim(explode(',', $forwarded)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Verifica si la IP está bloqueada.
     * Retorna ['blocked' => bool, 'minutos_restantes' => int]
     */
    public static function check(): array {
        try {
            $pdo = Database::getConnection();
            $ip  = self::getIp();

            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS intentos,
                       EXTRACT(EPOCH FROM (
                           MIN(created_at) + INTERVAL '900 seconds' - NOW()
                       )) AS segundos_restantes
                FROM login_attempts
                WHERE ip = ?
                  AND created_at > NOW() - INTERVAL '900 seconds'
            ");
            $stmt->execute([$ip]);
            $row = $stmt->fetch();

            $intentos = (int)$row['intentos'];

            if ($intentos >= self::MAX_ATTEMPTS) {
                $minutos = (int)ceil(max(0, (float)$row['segundos_restantes']) / 60);
                return ['blocked' => true, 'minutos_restantes' => $minutos];
            }

            return ['blocked' => false, 'minutos_restantes' => 0];

        } catch (Exception $e) {
            error_log('RateLimiter::check error: ' . $e->getMessage());
            return ['blocked' => false, 'minutos_restantes' => 0];
        }
    }

    /**
     * Registra un intento fallido de login.
     */
    public static function registrarFallo(string $email = ''): void {
        try {
            $pdo = Database::getConnection();
            $pdo->prepare("INSERT INTO login_attempts (ip, email) VALUES (?, ?)")
                ->execute([self::getIp(), $email ?: null]);
        } catch (Exception $e) {
            error_log('RateLimiter::registrarFallo error: ' . $e->getMessage());
        }
    }

    /**
     * Limpia los intentos de la IP tras un login exitoso.
     */
    public static function limpiar(): void {
        try {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")
                ->execute([self::getIp()]);
        } catch (Exception $e) {
            error_log('RateLimiter::limpiar error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina registros viejos (> 1 día). Llamar ocasionalmente para evitar
     * que la tabla crezca indefinidamente.
     */
    public static function gc(): void {
        try {
            Database::getConnection()
                ->exec("DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL '1 day'");
        } catch (Exception $e) {
            error_log('RateLimiter::gc error: ' . $e->getMessage());
        }
    }
}
