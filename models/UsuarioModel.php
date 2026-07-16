<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ClienteCore.php';

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

            // 2. Ciudadano
            if (!empty($data['id_ciudadano_existente'])) {
                // 2a. Ya existe en ciudadanos_cache (espejo de Core) → solo vincular la cuenta.
                //     Los datos personales los sincroniza Core, no se editan aquí.
                $stmt2 = $this->db->prepare("
                    UPDATE ciudadanos_cache SET usuario_id = ? WHERE id_ciudadano = ?
                ");
                $stmt2->execute([
                    $idUsuario,
                    $data['id_ciudadano_existente'],
                ]);

            } else {
                // 2b. No existe localmente → crear en Core (fuente de verdad) y reflejar en la cache
                $core = new ClienteCore();

                try {
                    $ciudadanoCore = $core->crearCiudadano([
                        'tipo_identificacion_id' => $core->idTipoIdentificacion($data['tipo_identificacion']),
                        'numero_identificacion'  => $data['numero_identificacion'],
                        'nombres'                => $data['nombres'],
                        'apellidos'              => $data['apellidos'],
                        'telefono'               => $data['telefono']     ?? null,
                        'email'                  => $data['email'],
                        'whatsapp'               => $data['whatsapp']     ?? null,
                        'direccion'              => $data['direccion']    ?? null,
                        'proveniencia'           => $data['proveniencia'] ?? null,
                    ]);

                } catch (CoreValidationException $eCore) {
                    // Core dice que la cédula ya existe allá, aunque local no la teníamos sincronizada
                    $ciudadanoCore = $core->buscarCiudadanoPorIdentificacion(
                        $data['tipo_identificacion'],
                        $data['numero_identificacion']
                    );

                    if (!$ciudadanoCore) {
                        throw $eCore;
                    }

                } catch (Exception $eConexion) {
                    // Core caído, timeout, etc. — mensaje claro para el usuario, no un 500 genérico
                    throw new Exception(
                        'No se pudo verificar tu identificación con el sistema central. Intenta nuevamente en unos minutos.',
                        0,
                        $eConexion
                    );
                }

                $stmt2 = $this->db->prepare("
                    INSERT INTO ciudadanos_cache
                        (id_ciudadano, nombres, apellidos, tipo_identificacion, numero_identificacion,
                         telefono, email, direccion, proveniencia, whatsapp, usuario_id, activo, synced_at)
                    VALUES
                        (:id_ciudadano, :nombres, :apellidos, :tipo_identificacion, :numero_identificacion,
                         :telefono, :email, :direccion, :proveniencia, :whatsapp, :usuario_id, :activo, NOW())
                    ON CONFLICT (id_ciudadano) DO UPDATE SET
                        nombres                = EXCLUDED.nombres,
                        apellidos              = EXCLUDED.apellidos,
                        tipo_identificacion     = EXCLUDED.tipo_identificacion,
                        numero_identificacion   = EXCLUDED.numero_identificacion,
                        telefono                = EXCLUDED.telefono,
                        email                   = EXCLUDED.email,
                        direccion               = EXCLUDED.direccion,
                        proveniencia            = EXCLUDED.proveniencia,
                        whatsapp                = EXCLUDED.whatsapp,
                        usuario_id              = EXCLUDED.usuario_id,
                        activo                  = EXCLUDED.activo,
                        synced_at               = NOW()
                ");
                $stmt2->execute([
                    ':id_ciudadano'           => $ciudadanoCore['id'],
                    ':nombres'                => $ciudadanoCore['nombres']               ?? $data['nombres'],
                    ':apellidos'              => $ciudadanoCore['apellidos']             ?? $data['apellidos'],
                    ':tipo_identificacion'    => $data['tipo_identificacion'],
                    ':numero_identificacion'  => $ciudadanoCore['numero_identificacion'] ?? $data['numero_identificacion'],
                    ':telefono'               => $ciudadanoCore['telefono']              ?? $data['telefono']     ?? null,
                    ':email'                  => $ciudadanoCore['email']                 ?? $data['email'],
                    ':direccion'              => $ciudadanoCore['direccion']              ?? $data['direccion']    ?? null,
                    ':proveniencia'           => $ciudadanoCore['proveniencia']           ?? $data['proveniencia'] ?? null,
                    ':whatsapp'               => $ciudadanoCore['whatsapp']               ?? $data['whatsapp']     ?? null,
                    ':usuario_id'             => $idUsuario,
                    ':activo'                 => $ciudadanoCore['activo'] ?? true,
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