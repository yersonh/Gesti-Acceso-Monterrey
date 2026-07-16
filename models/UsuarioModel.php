<?php
require_once __DIR__ . '/../config/database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // ── Consulta base: usuarios + datos personales según rol ──────────

    /**
     * SQL base que resuelve nombres/apellidos/telefono via JOIN
     * a personal, funcionarios o ciudadanos según rol.
     */
    private function sqlBase(): string {
        return "
            SELECT
                u.id_usuario,
                u.email,
                u.rol,
                u.activo,
                u.fecha_registro,
                COALESCE(p.nombres,   f.nombres,   c.nombres)   AS nombres,
                COALESCE(p.apellidos, f.apellidos, c.apellidos) AS apellidos,
                COALESCE(p.tipo_identificacion,  f.tipo_identificacion,  c.tipo_identificacion)  AS tipo_identificacion,
                COALESCE(p.numero_identificacion,f.numero_identificacion,c.numero_identificacion) AS numero_identificacion,
                COALESCE(p.telefono,  f.telefono,  c.telefono)  AS telefono,
                COALESCE(p.cargo,     f.cargo,     NULL)        AS cargo,
                p.id_personal,
                f.id_funcionario,
                c.id_ciudadano
            FROM usuarios u
            LEFT JOIN personal     p ON p.usuario_id = u.id_usuario
            LEFT JOIN funcionarios_cache f ON f.usuario_id = u.id_usuario
            LEFT JOIN ciudadanos_cache   c ON c.usuario_id = u.id_usuario
        ";
    }

    // ── Getters ───────────────────────────────────────────────────────

    public function getAll() {
        $stmt = $this->db->query($this->sqlBase() . " ORDER BY u.fecha_registro DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare($this->sqlBase() . " WHERE u.id_usuario = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail($email) {
        $stmt = $this->db->prepare($this->sqlBase() . " WHERE u.email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Verifica si el email ya existe en la tabla usuarios (solo auth).
     * Usado en registro de ciudadanos para no confundir con emails
     * en tablas de personal o funcionarios.
     */
    public function emailExisteEnUsuarios($email): bool {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM usuarios WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    /**
     * Busca por número de identificación en la tabla ciudadanos.
     * Solo aplica para ciudadanos registrados en el portal web.
     */
    public function getByIdentificacion($numero_identificacion) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.id_usuario, u.email, u.rol, u.activo
            FROM ciudadanos_cache c
            LEFT JOIN usuarios u ON u.id_usuario = c.usuario_id
            WHERE c.numero_identificacion = ?
        ");
        $stmt->execute([$numero_identificacion]);
        return $stmt->fetch();
    }

    // ── Crear usuario ciudadano (registro web) ────────────────────────

    /**
     * Crea el usuario en tabla usuarios y el ciudadano en tabla ciudadanos.
     * Transacción — si falla uno, revierte todo.
     */
    public function create($data) {
        $this->db->beginTransaction();
        try {
            // 1. Insertar en usuarios (solo auth)
            $stmt = $this->db->prepare("
                INSERT INTO usuarios
                    (email, password_hash, rol, activo, fecha_registro)
                VALUES (?, ?, 'Ciudadano', true, NOW())
                RETURNING id_usuario
            ");
            $stmt->execute([
                $data['email'],
                $data['password_hash']
            ]);
            $idUsuario = $stmt->fetchColumn();

            // 2. Ciudadano existente sin cuenta → vincular, actualizar nombres/apellidos
            //    siempre (el usuario conoce mejor su nombre completo), y completar
            //    campos opcionales solo si estaban vacíos en BD.
            if (!empty($data['id_ciudadano_existente'])) {
                $stmt2 = $this->db->prepare("
                    UPDATE ciudadanos SET
                        usuario_id   = ?,
                        nombres      = ?,
                        apellidos    = ?,
                        telefono     = COALESCE(NULLIF(?, ''), telefono),
                        email        = COALESCE(NULLIF(?, ''), email),
                        whatsapp     = COALESCE(NULLIF(?, ''), whatsapp),
                        direccion    = COALESCE(NULLIF(?, ''), direccion),
                        proveniencia = COALESCE(NULLIF(?, ''), proveniencia),
                        updated_at   = NOW()
                    WHERE id_ciudadano = ?
                ");
                $stmt2->execute([
                    $idUsuario,
                    $data['nombres'],
                    $data['apellidos'],
                    $data['telefono']     ?? '',
                    $data['email'],
                    $data['whatsapp']     ?? '',
                    $data['direccion']    ?? '',
                    $data['proveniencia'] ?? '',
                    $data['id_ciudadano_existente'],
                ]);
            } else {
                // 2b. Ciudadano nuevo → insertar registro completo
                $stmt2 = $this->db->prepare("
                    INSERT INTO ciudadanos
                        (usuario_id, nombres, apellidos, tipo_identificacion,
                         numero_identificacion, telefono, email,
                         whatsapp, direccion, proveniencia)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt2->execute([
                    $idUsuario,
                    $data['nombres'],
                    $data['apellidos'],
                    $data['tipo_identificacion'],
                    $data['numero_identificacion'],
                    $data['telefono']     ?? null,
                    $data['email'],
                    $data['whatsapp']     ?? null,
                    $data['direccion']    ?? null,
                    $data['proveniencia'] ?? null,
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('UsuarioModel::create - ' . $e->getMessage());
            throw $e;
        }
    }

    // ── Actualizar ────────────────────────────────────────────────────

    public function update($id, $data) {
        $fields = [];
        $params = [];

        // Solo campos de tabla usuarios
        foreach (['email'] as $campo) {
            if (isset($data[$campo])) {
                $fields[] = "$campo = ?";
                $params[] = $data[$campo];
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id_usuario = ?";
            $this->db->prepare($sql)->execute($params);
        }

        return true;
    }

    public function updatePassword($id, $password_hash) {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?"
        );
        return $stmt->execute([$password_hash, $id]);
    }

    public function setActivo($id, $activo) {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET activo = ? WHERE id_usuario = ?"
        );
        return $stmt->execute([$activo, $id]);
    }

    public function delete($id) {
        return $this->setActivo($id, false);
    }

    // ── Autenticación ─────────────────────────────────────────────────

    /**
     * Verifica email + password.
     * Retorna el array del usuario con todos sus datos personales (via JOIN)
     * o false si las credenciales son incorrectas.
     */
    public function verificarCredenciales($email, $password) {
        // Primero obtenemos el hash (tabla usuarios)
        $stmt = $this->db->prepare(
            "SELECT id_usuario, password_hash, activo FROM usuarios WHERE email = ?"
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !$row['activo']) return false;
        if (!password_verify($password, $row['password_hash'])) return false;

        // Credenciales correctas → devolver datos completos via JOIN
        $usuario = $this->getById($row['id_usuario']);
        unset($usuario['password_hash']);
        return $usuario;
    }

    // ── Utilidades ────────────────────────────────────────────────────

    public function countByRol($rol) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM usuarios WHERE rol = ?"
        );
        $stmt->execute([$rol]);
        return $stmt->fetch()['total'];
    }

    public function search($termino) {
        $term = "%$termino%";
        $stmt = $this->db->prepare("
            SELECT u.id_usuario,
                   COALESCE(p.nombres,   f.nombres,   c.nombres)   AS nombres,
                   COALESCE(p.apellidos, f.apellidos, c.apellidos) AS apellidos,
                   u.email, u.rol, u.activo
            FROM usuarios u
            LEFT JOIN personal     p ON p.usuario_id = u.id_usuario
            LEFT JOIN funcionarios_cache f ON f.usuario_id = u.id_usuario
            LEFT JOIN ciudadanos_cache   c ON c.usuario_id = u.id_usuario
            WHERE u.email ILIKE ?
               OR COALESCE(p.nombres,   f.nombres,   c.nombres)   ILIKE ?
               OR COALESCE(p.apellidos, f.apellidos, c.apellidos) ILIKE ?
            ORDER BY nombres, apellidos
        ");
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function getActivos() {
        $stmt = $this->db->query("
            SELECT u.id_usuario,
                   COALESCE(p.nombres,   f.nombres,   c.nombres)   AS nombres,
                   COALESCE(p.apellidos, f.apellidos, c.apellidos) AS apellidos,
                   u.email
            FROM usuarios u
            LEFT JOIN personal     p ON p.usuario_id = u.id_usuario
            LEFT JOIN funcionarios_cache f ON f.usuario_id = u.id_usuario
            LEFT JOIN ciudadanos_cache   c ON c.usuario_id = u.id_usuario
            WHERE u.activo = true
            ORDER BY nombres, apellidos
        ");
        return $stmt->fetchAll();
    }
}