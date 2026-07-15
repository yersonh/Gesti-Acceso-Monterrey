<?php
// models/ValoracionModel.php

class ValoracionModel {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Crea un registro pendiente de valoración con token único.
     * Retorna el token generado para enviarlo por WhatsApp.
     */
    public function crear(int $visitaId, string $tipoVisita): string {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare("
            INSERT INTO valoraciones
                (visita_id, tipo_visita, token, respondido, expires_at)
            VALUES
                (?, ?, ?, false, NOW() + INTERVAL '7 days')
        ");
        $stmt->execute([$visitaId, $tipoVisita, $token]);

        return $token;
    }

    /**
     * Busca una valoración activa por token.
     * Retorna null si no existe, ya fue respondida o expiró.
     */
    public function getByToken(string $token): ?array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM   valoraciones
            WHERE  token      = ?
              AND  respondido = false
              AND  expires_at > NOW()
            LIMIT  1
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Guarda la respuesta del ciudadano y marca la valoración como respondida.
     */
    public function responder(string $token, int $estrellas, bool $solucionado, string $comentario = ''): bool {
        $stmt = $this->db->prepare("
            UPDATE valoraciones SET
                estrellas   = ?,
                solucionado = ?,
                comentario  = NULLIF(?, ''),
                respondido  = true
            WHERE token      = ?
              AND respondido = false
              AND expires_at > NOW()
        ");
        $stmt->execute([
            $estrellas,
            $solucionado ? 'true' : 'false',
            $comentario,
            $token,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtiene los datos del visitante y la visita para mostrar en el formulario.
     * Funciona tanto para citas como para visitas espontáneas.
     */
    public function getDatosVisita(int $visitaId, string $tipoVisita): ?array {
        if ($tipoVisita === 'cita') {
            $stmt = $this->db->prepare("
                SELECT
                    ci.nombres,
                    ci.apellidos,
                    u.email,
                    f.nombres   AS func_nombres,
                    f.apellidos AS func_apellidos,
                    d.nombre    AS dependencia,
                    c.motivo,
                    c.fecha
                FROM   citas c
                JOIN   ciudadanos   ci ON ci.id_ciudadano  = c.ciudadano_id
                JOIN   usuarios     u  ON u.id_usuario     = ci.usuario_id
                JOIN   funcionarios f  ON f.id_funcionario = c.funcionario_id
                JOIN   dependencias d  ON d.id_dependencia = c.dependencia_id
                WHERE  c.id_cita = ?
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT
                    ci.nombres,
                    ci.apellidos,
                    ci.email,
                    f.nombres   AS func_nombres,
                    f.apellidos AS func_apellidos,
                    d.nombre    AS dependencia,
                    ve.motivo,
                    DATE(ve.hora_ingreso) AS fecha
                FROM   visitas_espontaneas ve
                JOIN   ciudadanos   ci ON ci.id_ciudadano   = ve.ciudadano_id
                LEFT JOIN funcionarios f  ON f.id_funcionario = ve.funcionario_id
                JOIN   dependencias d  ON d.id_dependencia = ve.dependencia_id
                WHERE  ve.id_visita = ?
            ");
        }
        $stmt->execute([$visitaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}