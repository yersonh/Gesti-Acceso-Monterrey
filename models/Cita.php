<?php
require_once __DIR__ . '/../config/database.php';

class Cita {

    private static function getConfiguracion(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT * FROM configuracion_sistema LIMIT 1");
            $config = $stmt->fetch();
            if (!$config) {
                // Valores por defecto si no hay config
                return [
                    'manana_inicio' => '07:00',
                    'manana_fin'    => '12:00',
                    'tarde_inicio'  => '14:00',
                    'tarde_fin'     => '17:00',
                    'intervalo_min' => 15,
                    'lunes'         => true,
                    'martes'        => true,
                    'miercoles'     => true,
                    'jueves'        => true,
                    'viernes'       => true,
                    'sabado'        => false,
                    'domingo'       => false,
                ];
            }
            return $config;
        } catch (Exception $e) {
            error_log('Cita::getConfiguracion - ' . $e->getMessage());
            return [
                'manana_inicio' => '07:00',
                'manana_fin'    => '12:00',
                'tarde_inicio'  => '14:00',
                'tarde_fin'     => '17:00',
                'intervalo_min' => 15,
                'lunes'         => true,
                'martes'        => true,
                'miercoles'     => true,
                'jueves'        => true,
                'viernes'       => true,
                'sabado'        => false,
                'domingo'       => false,
            ];
        }
    }

    private static function esDiaLaborable(string $fecha, array $config): bool {
        $mapaDias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ];
        $numeroDia = (int)date('N', strtotime($fecha));
        $campoDia  = $mapaDias[$numeroDia];
        return (bool)$config[$campoDia];
    }

    private static function generarSlots(): array {
        $config   = self::getConfiguracion();
        $slots    = [];
        $intervalo = (int)$config['intervalo_min'];

        $h = strtotime($config['manana_inicio']);
        while ($h < strtotime($config['manana_fin'])) {
            $slots[] = date('H:i', $h);
            $h = strtotime("+{$intervalo} minutes", $h);
        }

        $h = strtotime($config['tarde_inicio']);
        while ($h < strtotime($config['tarde_fin'])) {
            $slots[] = date('H:i', $h);
            $h = strtotime("+{$intervalo} minutes", $h);
        }

        return $slots;
    }

    /**
     * Stats de citas de un ciudadano agrupadas por estado.
     */
    public static function getStats(int $ciudadanoId): array {
        $stats = ['total' => 0, 'pendiente' => 0, 'confirmada' => 0,
                  'cancelada' => 0, 'finalizada' => 0, 'no_asistio' => 0,
                  'propuesta_reprogramacion' => 0, 'contrapropuesta_ciudadano' => 0];
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT estado, COUNT(*) AS total
                FROM citas
                WHERE ciudadano_id = :id
                GROUP BY estado
            ");
            $stmt->execute([':id' => $ciudadanoId]);
            while ($row = $stmt->fetch()) {
                $key = $row['estado'];
                if (isset($stats[$key])) $stats[$key] = (int)$row['total'];
                $stats['total'] += (int)$row['total'];
            }
            // Propuestas/contrapropuestas cuentan como pendientes para el ciudadano
            $stats['pendiente'] += $stats['propuesta_reprogramacion'] + $stats['contrapropuesta_ciudadano'];
        } catch (Exception $e) {
            error_log('Cita::getStats - ' . $e->getMessage());
        }
        return $stats;
    }

    /**
     * Total de citas de un ciudadano (para paginación).
     */
    public static function countMisCitas(int $ciudadanoId, string $filtroEstado = 'todas'): int {
        try {
            $pdo = Database::getConnection();
            $whereEstado = ($filtroEstado !== 'todas') ? "AND estado = :estado" : "";
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM citas
                WHERE ciudadano_id = :id
                $whereEstado
            ");
            $params = [':id' => $ciudadanoId];
            if ($filtroEstado !== 'todas') $params[':estado'] = $filtroEstado;
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('Cita::countMisCitas - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lista paginada de citas de un ciudadano.
     */
    public static function getMisCitas(int $ciudadanoId, string $filtroEstado = 'todas', int $page = 1, int $perPage = 25): array {
        try {
            $pdo = Database::getConnection();

            $whereEstado = ($filtroEstado !== 'todas') ? "AND c.estado = :estado" : "";
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare("
                SELECT
                    c.id_cita,
                    c.fecha,
                    c.hora_inicio::text      AS hora_inicio,
                    c.hora_fin::text         AS hora_fin,
                    c.fecha_propuesta,
                    c.hora_propuesta::text   AS hora_propuesta,
                    c.motivo,
                    c.estado,
                    c.nota_gestion,
                    c.token_respuesta,
                    c.created_at,
                    d.nombre                 AS dependencia,
                    f.nombres                AS func_nombres,
                    f.apellidos              AS func_apellidos,
                    f.cargo                  AS func_cargo
                FROM citas c
                JOIN dependencias d  ON d.id_dependencia = c.dependencia_id
                JOIN funcionarios f  ON f.id_funcionario = c.funcionario_id
                WHERE c.ciudadano_id = :id
                $whereEstado
                ORDER BY c.created_at DESC, c.fecha DESC, c.hora_inicio DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");

            $params = [':id' => $ciudadanoId];
            if ($filtroEstado !== 'todas') $params[':estado'] = $filtroEstado;

            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log('Cita::getMisCitas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Horarios disponibles de un funcionario en una fecha.
     */
    public static function getHorariosDisponibles(int $funcionarioId, string $fecha): array {
        try {
            $pdo    = Database::getConnection();
            $config = self::getConfiguracion();

            // Verificar si es día laborable según configuración
            if (!self::esDiaLaborable($fecha, $config)) {
                return ['error' => 'No se atiende este día.'];
            }
            if (self::esFestivo($fecha)) {
                return ['error' => 'Este día es festivo y no hay atención.'];
            }
            // Generar slots dinámicamente desde configuración
            $intervalo  = (int)$config['intervalo_min'];
            $horas      = [];

            $h = strtotime($config['manana_inicio']);
            while ($h < strtotime($config['manana_fin'])) {
                $horas[] = date('H:i', $h);
                $h = strtotime("+{$intervalo} minutes", $h);
            }
            $h = strtotime($config['tarde_inicio']);
            while ($h < strtotime($config['tarde_fin'])) {
                $horas[] = date('H:i', $h);
                $h = strtotime("+{$intervalo} minutes", $h);
            }

            // Citas ocupadas
            $stmtCitas = $pdo->prepare("
                SELECT hora_inicio::text AS hora_inicio
                FROM citas
                WHERE funcionario_id = :func
                AND fecha = :fecha
                AND estado IN ('confirmada', 'pendiente', 'propuesta_reprogramacion')
            ");
            $stmtCitas->execute([':func' => $funcionarioId, ':fecha' => $fecha]);
            $citasOcupadas = array_map(
                fn($r) => substr($r['hora_inicio'], 0, 5),
                $stmtCitas->fetchAll()
            );

            // Bloqueos
            $stmtBloqueos = $pdo->prepare("
                SELECT hora_inicio, hora_fin
                FROM horarios_bloqueados
                WHERE funcionario_id = :func AND fecha = :fecha
            ");
            $stmtBloqueos->execute([':func' => $funcionarioId, ':fecha' => $fecha]);
            $bloqueos = $stmtBloqueos->fetchAll();

            $horaToMinutos = fn($h) => (int)explode(':', $h)[0] * 60 + (int)explode(':', $h)[1];

            $slotsManana = [];
            $slotsTarde  = [];

            foreach ($horas as $hora) {
                $disponible = !in_array($hora, $citasOcupadas);

                if ($disponible) {
                    $horaActualMin = $horaToMinutos($hora);
                    foreach ($bloqueos as $b) {
                        $ini = $horaToMinutos(substr($b['hora_inicio'], 0, 5));
                        $fin = $horaToMinutos(substr($b['hora_fin'],    0, 5));
                        if ($horaActualMin >= $ini && $horaActualMin <= $fin) {
                            $disponible = false;
                            break;
                        }
                    }
                }

                $slot = ['hora' => $hora, 'disponible' => $disponible];
                if (strtotime($hora) < strtotime($config['manana_fin']))
                    $slotsManana[] = $slot;
                else
                    $slotsTarde[] = $slot;
            }

            return ['manana' => $slotsManana, 'tarde' => $slotsTarde];

        } catch (Exception $e) {
            error_log('Cita::getHorariosDisponibles - ' . $e->getMessage());
            return ['error' => 'Error al consultar horarios.'];
        }
    }
    private static function esFestivo(string $fecha): bool
    {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM dias_festivos
                WHERE activo = true
                AND (
                    fecha = :fecha
                    OR (recurrente = true
                        AND EXTRACT(MONTH FROM fecha) = EXTRACT(MONTH FROM :fecha::date)
                        AND EXTRACT(DAY   FROM fecha) = EXTRACT(DAY   FROM :fecha::date))
                )
            ");
            $stmt->execute([':fecha' => $fecha]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log('Cita::esFestivo - ' . $e->getMessage());
            return false; // ante la duda, no bloquear
        }
    }
    /**
     * Crea una nueva cita.
     */
    public static function crear(
        int    $ciudadanoId,
        int    $funcionarioId,
        int    $dependenciaId,
        string $fecha,
        string $horaInicio,
        string $motivo
    ): array {
        if (!in_array($horaInicio, self::generarSlots()))
            return ['error' => 'Hora no válida.'];
        if (strtotime($fecha) < strtotime(date('Y-m-d')))
            return ['error' => 'No puedes agendar en una fecha pasada.'];

        $config = self::getConfiguracion();
        if (!self::esDiaLaborable($fecha, $config))
            return ['error' => 'No se atiende este día.'];
        if (self::esFestivo($fecha))
            return ['error' => 'No se pueden agendar citas en días festivos.'];
        try {
            $pdo = Database::getConnection();

            $chkCita = $pdo->prepare("
                SELECT COUNT(*) FROM citas
                WHERE funcionario_id = :func
                AND fecha = :fecha
                AND hora_inicio = :hora
                AND estado IN ('confirmada', 'pendiente', 'propuesta_reprogramacion')
            ");
            $chkCita->execute([':func' => $funcionarioId, ':fecha' => $fecha, ':hora' => $horaInicio]);

            $chkBloqueo = $pdo->prepare("
                SELECT COUNT(*) FROM horarios_bloqueados
                WHERE funcionario_id = :func
                  AND fecha = :fecha
                  AND :hora::time >= hora_inicio
                  AND :hora::time <= hora_fin
            ");
            $chkBloqueo->execute([':func' => $funcionarioId, ':fecha' => $fecha, ':hora' => $horaInicio]);

            if ($chkCita->fetchColumn() > 0 || $chkBloqueo->fetchColumn() > 0)
                return ['error' => 'Ese horario no está disponible. Por favor selecciona otro.'];
            $intervalo = (int)$config['intervalo_min'];
            $horaFin   = date('H:i', strtotime("+{$intervalo} minutes", strtotime($horaInicio)));
            $ins = $pdo->prepare("
                INSERT INTO citas
                    (ciudadano_id, funcionario_id, dependencia_id,
                    fecha, hora_inicio, hora_fin, motivo, estado)
                VALUES
                    (:ciudadano, :func, :dep,
                    :fecha, :hora, :hora_fin, :motivo, 'pendiente')
                RETURNING id_cita
            ");
            $ins->execute([
                ':ciudadano' => $ciudadanoId,
                ':func'      => $funcionarioId,
                ':dep'       => $dependenciaId,
                ':fecha'     => $fecha,
                ':hora'      => $horaInicio,
                ':hora_fin'  => $horaFin,
                ':motivo'    => $motivo,
            ]);

            return ['ok' => true, 'id_cita' => $ins->fetchColumn()];

        } catch (Exception $e) {
            error_log('Cita::crear - ' . $e->getMessage());
            return ['error' => 'Error al guardar la cita. Intenta de nuevo.'];
        }
    }

    /**
     * Cancela una cita pendiente.
     */
    public static function cancelar(int $citaId, int $ciudadanoId, string $motivo = ''): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                UPDATE citas
                SET estado                  = 'cancelada',
                    cancelado_por_ciudadano = true,
                    motivo_cancelacion      = :motivo,
                    updated_at              = NOW()
                WHERE id_cita      = :id
                  AND ciudadano_id = :ciudadano
                  AND estado       = 'pendiente'
            ");
            $stmt->execute([
                ':id'        => $citaId,
                ':ciudadano' => $ciudadanoId,
                ':motivo'    => $motivo ?: 'Cancelada por el ciudadano',
            ]);

            if ($stmt->rowCount() === 0)
                return ['error' => 'No se pudo cancelar. La cita no existe o ya no está pendiente.'];

            return ['ok' => true];

        } catch (Exception $e) {
            error_log('Cita::cancelar - ' . $e->getMessage());
            return ['error' => 'Error al cancelar la cita.'];
        }
    }

    /**
     * Obtiene una cita por ID con todos los detalles.
     */
    public static function getById(int $citaId): ?array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    c.id_cita,
                    c.fecha,
                    c.hora_inicio::text   AS hora_inicio,
                    c.hora_fin::text      AS hora_fin,
                    c.motivo,
                    c.estado,
                    c.nota_gestion,
                    c.created_at,
                    d.id_dependencia,
                    d.nombre              AS dependencia,
                    f.id_funcionario,
                    f.nombres             AS func_nombres,
                    f.apellidos           AS func_apellidos,
                    f.cargo               AS func_cargo,
                    f.email               AS func_email,
                    ci.id_ciudadano,
                    ci.nombres            AS ciudadano_nombres,
                    ci.apellidos          AS ciudadano_apellidos,
                    ci.email              AS ciudadano_email
                FROM citas c
                JOIN dependencias d  ON d.id_dependencia = c.dependencia_id
                JOIN funcionarios f  ON f.id_funcionario = c.funcionario_id
                JOIN ciudadanos   ci ON ci.id_ciudadano  = c.ciudadano_id
                WHERE c.id_cita = :id
            ");
            $stmt->execute([':id' => $citaId]);
            $result = $stmt->fetch();
            return $result ?: null;

        } catch (Exception $e) {
            error_log('Cita::getById - ' . $e->getMessage());
            return null;
        }
    }
}