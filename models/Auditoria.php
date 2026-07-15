<?php
require_once __DIR__ . '/../config/database.php';

class Auditoria
{
    public static function registrar(
        string  $accion,
        string  $descripcion,
        ?string $tablaAfectada = null,
        ?int    $registroId    = null
    ): void {
        try {
            $pdo = Database::getConnection();

            // Tomar datos del usuario desde la sesión
            $usuarioId     = $_SESSION['usuario_id']     ?? null;
            $usuarioNombre = $_SESSION['usuario_nombre'] ?? null;
            $usuarioRol    = $_SESSION['usuario_rol']    ?? null;
            $ip            = $_SERVER['REMOTE_ADDR']     ?? null;

            $stmt = $pdo->prepare("
                INSERT INTO auditoria_logs
                    (usuario_id, usuario_nombre, usuario_rol,
                     accion, descripcion, tabla_afectada, registro_id, ip)
                VALUES
                    (:usuario_id, :usuario_nombre, :usuario_rol,
                     :accion, :descripcion, :tabla_afectada, :registro_id, :ip)
            ");

            $stmt->execute([
                ':usuario_id'     => $usuarioId,
                ':usuario_nombre' => $usuarioNombre,
                ':usuario_rol'    => $usuarioRol,
                ':accion'         => $accion,
                ':descripcion'    => $descripcion,
                ':tabla_afectada' => $tablaAfectada,
                ':registro_id'    => $registroId,
                ':ip'             => $ip,
            ]);

        } catch (Exception $e) {
            // No interrumpir el flujo si falla la auditoría
            error_log('Auditoria::registrar - ' . $e->getMessage());
        }
    }
}