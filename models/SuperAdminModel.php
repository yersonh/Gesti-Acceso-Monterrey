<?php

declare(strict_types=1);

class SuperAdminModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function execute(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Listar SuperAdmins y Administradores ─────────────────────────────────

    public function listarUsuarios(): array
    {
        return $this->execute("
            SELECT
                u.id_usuario,
                u.email,
                u.rol,
                u.activo,
                u.fecha_registro,
                p.nombres,
                p.apellidos,
                p.tipo_identificacion,
                p.numero_identificacion,
                p.telefono,
                p.cargo
            FROM usuarios u
            JOIN personal p ON p.usuario_id = u.id_usuario
            WHERE u.rol IN ('Superadmin', 'Administrador')
            ORDER BY u.rol ASC, p.nombres ASC
        ");
    }

    // ─── Verificar duplicados ─────────────────────────────────────────────────

    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $params = [':email' => $email];

        if ($excluirId) {
            $sql    .= " AND id_usuario != :id";
            $params[':id'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function identificacionExiste(string $numero, ?int $excluirId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM personal WHERE numero_identificacion = :numero";
        $params = [':numero' => $numero];

        if ($excluirId) {
            $sql    .= " AND usuario_id != :id";
            $params[':id'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─── Crear usuario (SuperAdmin o Administrador) ───────────────────────────

    public function crearUsuario(array $datos): int
    {
        // Validar que el rol sea uno de los permitidos
        if (!in_array($datos['rol'], ['Superadmin', 'Administrador'])) {
            throw new InvalidArgumentException('Rol no permitido.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (email, password_hash, rol, activo, fecha_registro)
                VALUES (:email, :password_hash, :rol, true, NOW())
                RETURNING id_usuario
            ");
            $stmt->execute([
                ':email'         => $datos['email'],
                ':password_hash' => password_hash($datos['password'], PASSWORD_BCRYPT),
                ':rol'           => $datos['rol'],
            ]);
            $usuarioId = (int)$stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO personal (
                    nombres, apellidos, tipo_identificacion,
                    numero_identificacion, telefono, email,
                    cargo, usuario_id, activo, created_at, updated_at
                ) VALUES (
                    :nombres, :apellidos, :tipo_identificacion,
                    :numero_identificacion, :telefono, :email,
                    :cargo, :usuario_id, true, NOW(), NOW()
                )
            ");
            $stmt->execute([
                ':nombres'               => $datos['nombres'],
                ':apellidos'             => $datos['apellidos'],
                ':tipo_identificacion'   => $datos['tipo_identificacion'],
                ':numero_identificacion' => $datos['numero_identificacion'],
                ':telefono'              => $datos['telefono'] ?: null,
                ':email'                 => $datos['email'],
                ':cargo'                 => $datos['cargo'] ?: $datos['rol'],
                ':usuario_id'            => $usuarioId,
            ]);

            $this->db->commit();
            return $usuarioId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ─── Activar / desactivar ─────────────────────────────────────────────────

    public function toggleActivo(int $usuarioId, bool $activo): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET activo = :activo
            WHERE id_usuario = :id
              AND rol IN ('Superadmin', 'Administrador')
        ");
        $stmt->execute([
            ':activo' => $activo ? 'true' : 'false',
            ':id'     => $usuarioId,
        ]);
    }

    // ─── Reset contraseña ─────────────────────────────────────────────────────

    public function resetPassword(int $usuarioId, string $nuevaPassword): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET password_hash = :hash
            WHERE id_usuario = :id
              AND rol IN ('Superadmin', 'Administrador')
        ");
        $stmt->execute([
            ':hash' => password_hash($nuevaPassword, PASSWORD_BCRYPT),
            ':id'   => $usuarioId,
        ]);
    }
}