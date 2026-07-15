<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Funcionario') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$funcionarioId = (int)($_GET['funcionario_id'] ?? 0);
$ultimoId      = (int)($_GET['ultimo_id']      ?? 0);
$ultimoCheck   = (int)($_GET['ultimo_check']   ?? 0); // Unix timestamp del último poll

if (!$funcionarioId) {
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

try {
    $db = Database::getConnection();

    // 1. Citas nuevas pendientes (detección por ID)
    $stmtNuevas = $db->prepare("
        SELECT
            c.id_cita,
            c.fecha,
            c.hora_inicio::text AS hora_inicio,
            c.motivo,
            ci.nombres   AS ciudadano_nombres,
            ci.apellidos AS ciudadano_apellidos,
            ci.telefono  AS ciudadano_telefono,
            d.nombre     AS dependencia
        FROM citas c
        JOIN ciudadanos ci ON ci.id_ciudadano = c.ciudadano_id
        JOIN dependencias d ON d.id_dependencia = c.dependencia_id
        JOIN funcionario_dependencia fd ON fd.dependencia_id = c.dependencia_id
        WHERE fd.funcionario_id = :fid
          AND c.id_cita > :ultimo_id
          AND c.estado = 'pendiente'
        ORDER BY c.id_cita ASC
    ");
    $stmtNuevas->execute([':fid' => $funcionarioId, ':ultimo_id' => $ultimoId]);
    $citas = $stmtNuevas->fetchAll(PDO::FETCH_ASSOC);

    $nuevoUltimoId = !empty($citas) ? max(array_column($citas, 'id_cita')) : $ultimoId;

    // 2. Contrapropuestas nuevas (detección por updated_at > último poll)
    $contrapropuestas = [];
    if ($ultimoCheck > 0) {
        $stmtContra = $db->prepare("
            SELECT
                c.id_cita,
                c.fecha,
                c.hora_inicio::text    AS hora_inicio,
                c.motivo,
                c.fecha_propuesta,
                c.hora_propuesta::text AS hora_propuesta,
                ci.nombres   AS ciudadano_nombres,
                ci.apellidos AS ciudadano_apellidos,
                ci.telefono  AS ciudadano_telefono,
                d.nombre     AS dependencia
            FROM citas c
            JOIN ciudadanos ci ON ci.id_ciudadano = c.ciudadano_id
            JOIN dependencias d ON d.id_dependencia = c.dependencia_id
            WHERE c.funcionario_id = :fid
              AND c.estado = 'contrapropuesta_ciudadano'
              AND c.updated_at > TO_TIMESTAMP(:ts)
            ORDER BY c.updated_at ASC
        ");
        $stmtContra->execute([':fid' => $funcionarioId, ':ts' => $ultimoCheck]);
        $contrapropuestas = $stmtContra->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Visitas en curso (snapshot completo — JS reconcilia el DOM)
    $stmtCurso = $db->prepare("
        SELECT
            c.id_cita                   AS id,
            'cita'                      AS tipo,
            c.fecha::text               AS fecha,
            c.hora_ingreso::text        AS hora_ingreso,
            c.motivo,
            ci.nombres                  AS ciudadano_nombres,
            ci.apellidos                AS ciudadano_apellidos,
            ci.telefono                 AS ciudadano_telefono,
            d.nombre                    AS dependencia
        FROM citas c
        JOIN ciudadanos ci ON ci.id_ciudadano  = c.ciudadano_id
        JOIN dependencias d ON d.id_dependencia = c.dependencia_id
        WHERE c.funcionario_id = :fid
          AND c.estado = 'en_curso'
        UNION ALL
        SELECT
            ve.id_visita                AS id,
            'espontanea'                AS tipo,
            DATE(ve.hora_ingreso)::text AS fecha,
            ve.hora_ingreso::text       AS hora_ingreso,
            ve.motivo,
            ci.nombres                  AS ciudadano_nombres,
            ci.apellidos                AS ciudadano_apellidos,
            ci.telefono                 AS ciudadano_telefono,
            d.nombre                    AS dependencia
        FROM visitas_espontaneas ve
        JOIN ciudadanos  ci ON ci.id_ciudadano   = ve.ciudadano_id
        JOIN dependencias d ON d.id_dependencia  = ve.dependencia_id
        WHERE ve.funcionario_id = :fid2
          AND ve.estado = 'en_curso'
        ORDER BY hora_ingreso ASC
    ");
    $stmtCurso->execute([':fid' => $funcionarioId, ':fid2' => $funcionarioId]);
    $enCurso = $stmtCurso->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'               => true,
        'citas'            => $citas,
        'ultimo_id'        => $nuevoUltimoId,
        'contrapropuestas' => $contrapropuestas,
        'en_curso'         => $enCurso,
    ]);

} catch (Exception $e) {
    error_log('check_nuevas_citas.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al verificar nuevas citas.']);
}