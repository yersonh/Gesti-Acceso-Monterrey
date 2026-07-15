<?php

declare(strict_types=1);

class ReporteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function execute(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                $stmt->bindValue($key, null, PDO::PARAM_NULL);
            } elseif (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function baseParams(array $f): array
    {
        return [
            ':fecha_inicio'   => $f['fecha_inicio'],
            ':fecha_fin'      => $f['fecha_fin'],
            ':dependencia_id' => !empty($f['dependencia_id']) ? (int)$f['dependencia_id'] : null,
            ':funcionario_id' => !empty($f['funcionario_id']) ? (int)$f['funcionario_id'] : null,
        ];
    }

    // ─── 1. Ocupación por dependencia ─────────────────────────────────────────

    public function ocupacionPorDependencia(array $f): array
    {
        $sql = "
            SELECT
                v.dependencia,
                COUNT(*)                                                AS total_visitas,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'cita')       AS total_citas,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'espontanea') AS total_espontaneas
            FROM vista_reportes_visitas v
            WHERE v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
            AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
            AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY v.dependencia
            ORDER BY total_visitas DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 2. Horas pico ────────────────────────────────────────────────────────

    public function horasPico(array $f): array
    {
        $sql = "
            SELECT
                EXTRACT(HOUR FROM v.hora_ingreso)::integer             AS hora,
                COUNT(*)                                                AS total_visitas,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'cita')       AS citas,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'espontanea') AS espontaneas
            FROM vista_reportes_visitas v
            WHERE v.hora_ingreso IS NOT NULL
            AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
            AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
            AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY hora
            ORDER BY hora
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 3. Tiempo de espera promedio ─────────────────────────────────────────

    public function tiempoEsperaPromedio(array $f): array
    {
        $sql = "
            SELECT
                v.nombres_funcionario || ' ' || v.apellidos_funcionario     AS funcionario,
                COUNT(*)                                                     AS total_citas,
                AVG(
                    EXTRACT(EPOCH FROM (
                        v.hora_ingreso - (v.fecha_cita + v.hora_inicio)::timestamp
                    )) / 60
                )::numeric(10,1)                                            AS espera_promedio_minutos,
                MIN(
                    EXTRACT(EPOCH FROM (
                        v.hora_ingreso - (v.fecha_cita + v.hora_inicio)::timestamp
                    )) / 60
                )::numeric(10,1)                                            AS espera_minima,
                MAX(
                    EXTRACT(EPOCH FROM (
                        v.hora_ingreso - (v.fecha_cita + v.hora_inicio)::timestamp
                    )) / 60
                )::numeric(10,1)                                            AS espera_maxima
            FROM vista_reportes_visitas v
            WHERE v.tipo_registro = 'cita'
            AND v.hora_ingreso IS NOT NULL
            AND v.hora_inicio  IS NOT NULL
            AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
            AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
            AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY v.id_funcionario, v.nombres_funcionario, v.apellidos_funcionario
            ORDER BY espera_promedio_minutos DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 4. Duración de visitas ───────────────────────────────────────────────

    public function duracionVisitas(array $f): array
    {
        $sql = "
            SELECT
                v.dependencia,
                v.tipo_registro,
                COUNT(*)                                                AS total_visitas,
                AVG(
                    EXTRACT(EPOCH FROM (v.hora_salida - v.hora_ingreso)) / 60
                )::numeric(10,1)                                        AS duracion_promedio_minutos,
                MIN(
                    EXTRACT(EPOCH FROM (v.hora_salida - v.hora_ingreso)) / 60
                )::numeric(10,1)                                        AS duracion_minima,
                MAX(
                    EXTRACT(EPOCH FROM (v.hora_salida - v.hora_ingreso)) / 60
                )::numeric(10,1)                                        AS duracion_maxima
            FROM vista_reportes_visitas v
            WHERE v.hora_ingreso IS NOT NULL
              AND v.hora_salida   IS NOT NULL
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
              AND EXTRACT(EPOCH FROM (v.hora_salida - v.hora_ingreso)) > 0
            GROUP BY v.dependencia, v.tipo_registro
            ORDER BY v.dependencia, v.tipo_registro
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 5. Ranking de motivos ────────────────────────────────────────────────

    public function rankingMotivos(array $f): array
    {
        $sql = "
            SELECT
                TRIM(LOWER(v.motivo))                                       AS motivo,
                COUNT(*)                                                     AS frecuencia,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'cita')            AS desde_citas,
                COUNT(*) FILTER (WHERE v.tipo_registro = 'espontanea')      AS desde_espontaneas,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 1)         AS porcentaje
            FROM vista_reportes_visitas v
            WHERE v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
            AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
            AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY TRIM(LOWER(v.motivo))
            ORDER BY frecuencia DESC
            LIMIT 20
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 6. Inasistencias (No-Show) ───────────────────────────────────────────

    public function inasistencias(array $f): array
    {
        $sql = "
            SELECT
                v.dependencia,
                v.nombres_funcionario || ' ' || v.apellidos_funcionario AS funcionario,
                COUNT(*)                                                  AS total_programadas,
                COUNT(*) FILTER (WHERE v.estado = 'no_asistio')          AS no_shows,
                ROUND(
                    COUNT(*) FILTER (WHERE v.estado = 'no_asistio') * 100.0
                    / NULLIF(COUNT(*), 0), 1
                )                                                         AS porcentaje_no_show
            FROM vista_reportes_visitas v
            WHERE v.tipo_registro = 'cita'
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND v.estado NOT IN ('cancelada', 'pendiente')
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY v.id_dependencia, v.dependencia,
                     v.id_funcionario, v.nombres_funcionario, v.apellidos_funcionario
            ORDER BY porcentaje_no_show DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 7. Trazabilidad rechazadas / reprogramadas ───────────────────────────

    public function trazabilidadCitas(array $f): array
    {
        $sql = "
            SELECT
                v.id                                                     AS id_cita,
                v.nombres_ciudadano || ' ' || v.apellidos_ciudadano     AS ciudadano,
                v.numero_identificacion,
                v.nombres_funcionario || ' ' || v.apellidos_funcionario AS funcionario,
                v.dependencia,
                v.fecha_cita                                             AS fecha_original,
                v.hora_inicio                                            AS hora_original,
                v.estado,
                v.motivo_cancelacion,
                v.cancelado_por_ciudadano,
                v.motivo_reprogramacion,
                v.fecha_propuesta,
                v.hora_propuesta,
                v.updated_at
            FROM vista_reportes_visitas v
            WHERE v.tipo_registro = 'cita'
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND v.estado IN (
                  'cancelada',
                  'propuesta_reprogramacion',
                  'contrapropuesta_ciudadano'
              )
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            ORDER BY v.updated_at DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 8. Exportación plana ─────────────────────────────────────────────────

    public function exportacionPlana(array $f): array
    {
        $sql = "
            SELECT
                v.tipo_registro,
                v.nombres_ciudadano || ' ' || v.apellidos_ciudadano     AS ciudadano,
                v.numero_identificacion,
                v.nombres_funcionario || ' ' || v.apellidos_funcionario AS funcionario,
                v.dependencia,
                v.motivo,
                v.fecha_cita                                             AS fecha,
                v.hora_inicio                                            AS hora_programada,
                v.hora_ingreso,
                v.hora_salida,
                v.estado,
                CASE
                    WHEN v.hora_ingreso IS NOT NULL AND v.hora_salida IS NOT NULL
                    THEN ROUND(
                        EXTRACT(EPOCH FROM (v.hora_salida - v.hora_ingreso)) / 60
                    )::integer
                END                                                      AS duracion_minutos
            FROM vista_reportes_visitas v
            WHERE v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            ORDER BY v.fecha_cita DESC, v.hora_ingreso DESC NULLS LAST
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 9. Satisfacción general (distribución de estrellas) ──────────────────

    public function satisfaccionGeneral(array $f): array
    {
        $sql = "
            SELECT
                val.estrellas                                      AS estrellas,
                COUNT(*)                                           AS cantidad,
                ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER (), 1) AS porcentaje
            FROM valoraciones val
            JOIN vista_reportes_visitas v
              ON v.id = val.visita_id AND v.tipo_registro = val.tipo_visita
            WHERE val.respondido = true
              AND val.estrellas IS NOT NULL
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY val.estrellas
            ORDER BY val.estrellas DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── 10. Satisfacción por funcionario ─────────────────────────────────────

    public function satisfaccionPorFuncionario(array $f): array
    {
        $sql = "
            SELECT
                v.nombres_funcionario || ' ' || v.apellidos_funcionario AS funcionario,
                v.dependencia,
                COUNT(*)                                                AS total_valoraciones,
                ROUND(AVG(val.estrellas)::numeric, 2)                   AS promedio_estrellas,
                ROUND(
                    100.0 * COUNT(*) FILTER (WHERE val.solucionado) / COUNT(*)
                , 1)                                                    AS pct_solucionado
            FROM valoraciones val
            JOIN vista_reportes_visitas v
              ON v.id = val.visita_id AND v.tipo_registro = val.tipo_visita
            WHERE val.respondido = true
              AND val.estrellas IS NOT NULL
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
            GROUP BY v.id_funcionario, v.nombres_funcionario, v.apellidos_funcionario, v.dependencia
            ORDER BY promedio_estrellas DESC, total_valoraciones DESC
        ";

        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── Tasa de asistencia real ──────────────────────────────────────────────

    public function tasaAsistencia(array $f): array
    {
        $sql = "
            SELECT
                COUNT(*) FILTER (WHERE v.estado IN ('completada', 'finalizada', 'en_curso')) AS asistidas,
                COUNT(*) FILTER (WHERE v.estado = 'no_asistio')                              AS no_asistio,
                COUNT(*) FILTER (WHERE v.estado IN ('completada', 'finalizada', 'en_curso', 'no_asistio')) AS total
            FROM vista_reportes_visitas v
            WHERE v.tipo_registro = 'cita'
              AND v.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
              AND (:dependencia_id::integer IS NULL OR v.id_dependencia = :dependencia_id::integer)
              AND (:funcionario_id::integer IS NULL OR v.id_funcionario = :funcionario_id::integer)
        ";
        return $this->execute($sql, $this->baseParams($f));
    }

    // ─── Auxiliares para filtros ──────────────────────────────────────────────

    public function getDependencias(): array
    {
        return $this->db
            ->query("
                SELECT id_dependencia, nombre
                FROM dependencias
                WHERE activo = true
                ORDER BY nombre
            ")
            ->fetchAll();
    }

    public function getFuncionarios(?int $dependenciaId = null): array
    {
        if ($dependenciaId) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT
                    v.id_funcionario,
                    v.nombres_funcionario || ' ' || v.apellidos_funcionario AS nombre
                FROM vista_reportes_visitas v
                WHERE v.id_dependencia = :dep_id
                ORDER BY nombre
            ");
            $stmt->execute([':dep_id' => $dependenciaId]);
            return $stmt->fetchAll();
        }

        return $this->db
            ->query("
                SELECT id_funcionario,
                       nombres || ' ' || apellidos AS nombre
                FROM funcionarios
                WHERE activo = true
                ORDER BY nombres
            ")
            ->fetchAll();
    }
}