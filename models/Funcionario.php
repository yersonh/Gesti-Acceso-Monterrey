<?php
require_once __DIR__ . '/../config/database.php';

class Funcionario {

    /**
     * Retorna los funcionarios activos asignados a una dependencia.
     */
    public static function getPorDependencia(int $dependenciaId): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    f.id_funcionario,
                    f.nombres,
                    f.apellidos,
                    f.cargo
                FROM funcionarios_cache f
                JOIN funcionario_dependencia fd ON fd.funcionario_id = f.id_funcionario
                WHERE fd.dependencia_id = :dep
                  AND f.activo = true
                ORDER BY f.nombres, f.apellidos
            ");
            $stmt->execute([':dep' => $dependenciaId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Funcionario::getPorDependencia - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna un funcionario por su ID.
     */
    public static function getPorId(int $id): ?array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    f.id_funcionario,
                    f.nombres,
                    f.apellidos,
                    f.cargo,
                    f.telefono,
                    f.email,
                    f.activo,
                    STRING_AGG(d.nombre, ', ' ORDER BY d.nombre) AS dependencias
                FROM funcionarios_cache f
                LEFT JOIN funcionario_dependencia fd ON fd.funcionario_id = f.id_funcionario
                LEFT JOIN dependencias_cache d ON d.id_dependencia = fd.dependencia_id
                WHERE f.id_funcionario = :id
                GROUP BY f.id_funcionario, f.nombres, f.apellidos, f.cargo,
                         f.telefono, f.email, f.activo
            ");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            error_log('Funcionario::getPorId - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener funcionario por ID de usuario (para login).
     */
    public static function getPorUsuarioId(int $usuarioId): ?array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    f.id_funcionario,
                    f.nombres,
                    f.apellidos,
                    f.cargo,
                    f.telefono,
                    f.email,
                    f.activo,
                    f.usuario_id
                FROM funcionarios_cache f
                WHERE f.usuario_id = ? AND f.activo = true
            ");
            $stmt->execute([$usuarioId]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            error_log('Funcionario::getPorUsuarioId - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener dependencias del funcionario.
     */
    public static function getDependencias(int $funcionarioId): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT d.*
                FROM dependencias_cache d
                JOIN funcionario_dependencia fd ON fd.dependencia_id = d.id_dependencia
                WHERE fd.funcionario_id = ? AND d.activo = true
            ");
            $stmt->execute([$funcionarioId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Funcionario::getDependencias - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resumen de actividades del funcionario para el dashboard.
     */
    public static function getResumenActividades(int $funcionarioId): array {
    try {
        $pdo = Database::getConnection();
        $hoy = date('Y-m-d');

        // Pendientes: incluye contrapropuestas del ciudadano también
        $stmtPend = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM citas
            WHERE funcionario_id = ?
            AND estado IN ('pendiente', 'contrapropuesta_ciudadano', 'propuesta_reprogramacion')
        ");
        $stmtPend->execute([$funcionarioId]);

        $stmtHoy = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM citas
            WHERE funcionario_id = ? AND fecha = ?
            AND estado IN ('confirmada', 'propuesta_reprogramacion')
        ");
        $stmtHoy->execute([$funcionarioId, $hoy]);

        // Próximas: incluir confirmadas Y las que tienen propuesta pendiente (sin límite)
        $stmtProx = $pdo->prepare("
            SELECT
                c.*,
                ci.nombres   AS ciudadano_nombres,
                ci.apellidos AS ciudadano_apellidos,
                ci.telefono  AS ciudadano_telefono,
                ci.email     AS ciudadano_email
            FROM citas c
            JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
            WHERE c.funcionario_id = ?
            AND c.fecha >= ?
            AND c.estado = 'confirmada'
            ORDER BY c.fecha ASC, c.hora_inicio ASC
        ");
        $stmtProx->execute([$funcionarioId, $hoy]);

        return [
            'pendientes'      => (int)$stmtPend->fetch()['total'],
            'hoy_confirmadas' => (int)$stmtHoy->fetch()['total'],
            'proximas'        => $stmtProx->fetchAll(),
        ];
    } catch (Exception $e) {
        error_log('Funcionario::getResumenActividades - ' . $e->getMessage());
        return ['pendientes' => 0, 'hoy_confirmadas' => 0, 'proximas' => []];
    }
}

    /**
     * Obtener solicitudes pendientes del funcionario.
     */
    public static function getSolicitudesPendientes(int $funcionarioId): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    c.id_cita,
                    c.fecha,
                    c.hora_inicio,
                    c.hora_fin,
                    c.motivo,
                    c.estado,
                    c.fecha_propuesta,
                    c.hora_propuesta,
                    c.motivo_reprogramacion,
                    c.created_at,
                    ci.id_ciudadano  AS ciudadano_id,
                    ci.nombres       AS ciudadano_nombres,
                    ci.apellidos     AS ciudadano_apellidos,
                    ci.email         AS ciudadano_email,
                    ci.telefono      AS ciudadano_telefono,
                    d.nombre         AS dependencia
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.funcionario_id = :funcionario_id
                AND c.estado IN ('pendiente', 'contrapropuesta_ciudadano', 'propuesta_reprogramacion')
                ORDER BY c.fecha ASC, c.hora_inicio ASC
            ");
            $stmt->execute([':funcionario_id' => $funcionarioId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Funcionario::getSolicitudesPendientes - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aprobar una cita pendiente.
     */
    public static function aprobarCita(int $citaId, int $funcionarioId): array {
        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT
                    c.*,
                    ci.nombres   AS ciudadano_nombres,
                    ci.apellidos AS ciudadano_apellidos,
                    ci.email     AS ciudadano_email,
                    d.nombre     AS dependencia_nombre
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.id_cita = ? AND c.funcionario_id = ? AND c.estado IN ('pendiente', 'contrapropuesta_ciudadano')
            ");
            $stmt->execute([$citaId, $funcionarioId]);
            $cita = $stmt->fetch();

            if (!$cita) {
                $pdo->rollBack();
                throw new Exception("Cita no encontrada o ya no está pendiente");
            }

            $update = $pdo->prepare("
                UPDATE citas
                SET estado         = 'confirmada',
                    gestionado_por = ?,
                    fecha          = CASE 
                                        WHEN estado = 'contrapropuesta_ciudadano' AND fecha_propuesta IS NOT NULL 
                                        THEN fecha_propuesta 
                                        ELSE fecha 
                                    END,
                    hora_inicio    = CASE 
                                        WHEN estado = 'contrapropuesta_ciudadano' AND hora_propuesta IS NOT NULL 
                                        THEN hora_propuesta 
                                        ELSE hora_inicio 
                                    END,
                    fecha_propuesta = NULL,
                    hora_propuesta  = NULL,
                    updated_at     = NOW()
                WHERE id_cita = ? AND estado IN ('pendiente', 'contrapropuesta_ciudadano')
            ");
            $update->execute([$funcionarioId, $citaId]);

            if ($update->rowCount() === 0) {
                $pdo->rollBack();
                throw new Exception("La cita ya no está pendiente");
            }

            $pdo->commit();

            // Si era contrapropuesta, los valores efectivos son los propuestos por el ciudadano.
            // El UPDATE ya los aplicó en la BD; reflejamos lo mismo en el array para que
            // el correo de confirmación muestre la fecha/hora correcta.
            if ($cita['estado'] === 'contrapropuesta_ciudadano') {
                if (!empty($cita['fecha_propuesta']))
                    $cita['fecha']       = $cita['fecha_propuesta'];
                if (!empty($cita['hora_propuesta']))
                    $cita['hora_inicio'] = $cita['hora_propuesta'];
            }

            return ['success' => true, 'cita' => $cita];

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log('Funcionario::aprobarCita - ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Rechazar una cita con motivo obligatorio.
     */
    public static function rechazarCita(int $citaId, int $funcionarioId, string $motivoRechazo): array {
        if (empty(trim($motivoRechazo))) {
            return ['success' => false, 'error' => 'Debe proporcionar un motivo de rechazo'];
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT
                    c.*,
                    ci.nombres   AS ciudadano_nombres,
                    ci.apellidos AS ciudadano_apellidos,
                    ci.email     AS ciudadano_email,
                    d.nombre     AS dependencia_nombre
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.id_cita = ? AND c.funcionario_id = ? AND c.estado IN ('pendiente', 'contrapropuesta_ciudadano')
            ");
            $stmt->execute([$citaId, $funcionarioId]);
            $cita = $stmt->fetch();

            if (!$cita) {
                $pdo->rollBack();
                throw new Exception("Cita no encontrada");
            }

            $update = $pdo->prepare("
                UPDATE citas
                SET estado             = 'cancelada',
                    gestionado_por     = ?,
                    nota_gestion       = ?,
                    motivo_cancelacion = ?,
                    updated_at         = NOW()
                WHERE id_cita = ? AND estado IN ('pendiente', 'contrapropuesta_ciudadano')
            ");
            $update->execute([
                $funcionarioId,
                'Rechazada: ' . $motivoRechazo,
                $motivoRechazo,
                $citaId,
            ]);

            if ($update->rowCount() === 0) {
                $pdo->rollBack();
                throw new Exception("La cita ya no está pendiente");
            }

            $pdo->commit();
            return ['success' => true, 'cita' => $cita];

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log('Funcionario::rechazarCita - ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reprogramar una cita (ofrecer nueva fecha/hora).
     */
    public static function reprogramarCita(int $citaId, int $funcionarioId, string $nuevaFecha, string $nuevaHora, string $motivo): array {
        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT
                    c.*,
                    ci.nombres   AS ciudadano_nombres,
                    ci.apellidos AS ciudadano_apellidos,
                    ci.email     AS ciudadano_email,
                    ci.telefono  AS ciudadano_telefono,
                    d.nombre     AS dependencia_nombre
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.id_cita = ? AND c.funcionario_id = ? AND c.estado = 'pendiente'
            ");
            $stmt->execute([$citaId, $funcionarioId]);
            $cita = $stmt->fetch();

            if (!$cita) {
                throw new Exception("Cita no encontrada o ya no está pendiente");
            }

            $check = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM citas
                WHERE funcionario_id = ?
                  AND fecha = ?
                  AND hora_inicio = ?
                  AND estado IN ('confirmada', 'pendiente')
                  AND id_cita != ?
            ");
            $check->execute([$funcionarioId, $nuevaFecha, $nuevaHora, $citaId]);
            if ($check->fetch()['total'] > 0) {
                throw new Exception("El horario seleccionado no está disponible");
            }

            $checkBloqueo = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM horarios_bloqueados
                WHERE funcionario_id = ?
                  AND fecha = ?
                  AND ?::time >= hora_inicio
                  AND ?::time <= hora_fin
            ");
            $checkBloqueo->execute([$funcionarioId, $nuevaFecha, $nuevaHora, $nuevaHora]);
            if ($checkBloqueo->fetch()['total'] > 0) {
                throw new Exception("El horario seleccionado está bloqueado en tu agenda");
            }

            $motivoCompleto = "REPROGRAMACIÓN: {$motivo} (fecha original: "
                . date('d/m/Y', strtotime($cita['fecha'])) . ' '
                . date('h:i A', strtotime($cita['hora_inicio'])) . ')';

            $configStmt = $pdo->query("SELECT intervalo_min FROM configuracion_sistema LIMIT 1");
            $intervalo  = (int)($configStmt->fetchColumn() ?: 15);

            $update = $pdo->prepare("
                UPDATE citas
                SET fecha        = ?,
                    hora_inicio  = ?,
                    hora_fin     = ?::time + (? || ' minutes')::interval,
                    estado       = 'pendiente',
                    nota_gestion = CONCAT(COALESCE(nota_gestion, ''), ' | ', ?::text),
                    updated_at   = NOW()
                WHERE id_cita = ? AND funcionario_id = ?
            ");
            $update->execute([
                $nuevaFecha,
                $nuevaHora,
                $nuevaHora,
                $intervalo,
                $motivoCompleto,
                $citaId,
                $funcionarioId,
            ]);

            $pdo->commit();
            return [
                'success'               => true,
                'cita'                  => $cita,
                'nueva_fecha'           => $nuevaFecha,
                'nueva_hora'            => $nuevaHora,
                'motivo_reprogramacion' => $motivo,
            ];

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log('Funcionario::reprogramarCita - ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Bloquear un horario en la agenda.
     */
    public static function bloquearHorario(int $funcionarioId, string $fecha, string $horaInicio, string $horaFin, string $motivo): array {
        try {
            $pdo = Database::getConnection();

            $checkCita = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM citas
                WHERE funcionario_id = ?
                  AND fecha = ?
                  AND hora_inicio = ?
                  AND estado IN ('confirmada', 'pendiente')
            ");
            $checkCita->execute([$funcionarioId, $fecha, $horaInicio]);
            if ($checkCita->fetch()['total'] > 0) {
                return ['success' => false, 'error' => 'Ya hay una cita agendada en ese horario'];
            }

            $checkBloqueo = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM horarios_bloqueados
                WHERE funcionario_id = ? AND fecha = ? AND hora_inicio = ?
            ");
            $checkBloqueo->execute([$funcionarioId, $fecha, $horaInicio]);
            if ($checkBloqueo->fetch()['total'] > 0) {
                return ['success' => false, 'error' => 'Este horario ya está bloqueado'];
            }

            $stmt = $pdo->prepare("
                INSERT INTO horarios_bloqueados (funcionario_id, fecha, hora_inicio, hora_fin, motivo)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$funcionarioId, $fecha, $horaInicio, $horaFin, $motivo]);

            return ['success' => true];

        } catch (Exception $e) {
            error_log('Funcionario::bloquearHorario - ' . $e->getMessage());
            return ['success' => false, 'error' => 'Error al bloquear horario: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener horarios bloqueados del funcionario (próximos 30 días por defecto).
     */
    public static function getHorariosBloqueados(int $funcionarioId, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        try {
            $pdo = Database::getConnection();
            if (!$fechaInicio) $fechaInicio = date('Y-m-d');
            if (!$fechaFin)   $fechaFin   = date('Y-m-d', strtotime('+30 days'));

            $stmt = $pdo->prepare("
                SELECT id_bloqueo, fecha, hora_inicio, hora_fin, motivo
                FROM horarios_bloqueados
                WHERE funcionario_id = ?
                  AND fecha BETWEEN ? AND ?
                ORDER BY fecha, hora_inicio
            ");
            $stmt->execute([$funcionarioId, $fechaInicio, $fechaFin]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Funcionario::getHorariosBloqueados - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar un bloqueo de agenda.
     */
    public static function eliminarBloqueo(int $bloqueoId, int $funcionarioId): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                DELETE FROM horarios_bloqueados
                WHERE id_bloqueo = ? AND funcionario_id = ?
            ");
            $stmt->execute([$bloqueoId, $funcionarioId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Bloqueo no encontrado'];

        } catch (Exception $e) {
            error_log('Funcionario::eliminarBloqueo - ' . $e->getMessage());
            return ['success' => false, 'error' => 'Error al eliminar bloqueo'];
        }
    }

    /**Horarios disponibles de un funcionario en una fecha (para portal web).

    public static function getHorariosDisponibles(int $funcionarioId, string $fecha): array {
        try {
            $pdo = Database::getConnection();

            $citas = $pdo->prepare("
                SELECT hora_inicio
                FROM citas
                WHERE funcionario_id = ? AND fecha = ?
                  AND estado IN ('confirmada', 'pendiente', 'propuesta_reprogramacion')
            ");
            $citas->execute([$funcionarioId, $fecha]);
            $ocupados = array_column($citas->fetchAll(), 'hora_inicio');

            $bloqueos = $pdo->prepare("
                SELECT hora_inicio
                FROM horarios_bloqueados
                WHERE funcionario_id = ? AND fecha = ?
            ");
            $bloqueos->execute([$funcionarioId, $fecha]);
            $bloqueados = array_column($bloqueos->fetchAll(), 'hora_inicio');

            $noDisponibles = array_merge($ocupados, $bloqueados);

            $horas = [
                '07:00','07:30','08:00','08:30','09:00','09:30',
                '10:00','10:30','11:00','11:30',
                '14:00','14:30','15:00','15:30','16:00','16:30',
            ];

            return array_map(fn($h) => [
                'hora'       => $h,
                'disponible' => !in_array($h, $noDisponibles),
            ], $horas);

        } catch (Exception $e) {
            error_log('Funcionario::getHorariosDisponibles - ' . $e->getMessage());
            return [];
        }
    }     */
    public static function proponerReprogramacion(int $citaId, int $funcionarioId, string $nuevaFecha, string $nuevaHora, string $motivo): array {
        try {
            $pdo = Database::getConnection();

            // Verificar que la cita pertenece al funcionario
            $stmt = $pdo->prepare("
                SELECT c.*, 
                    u.email AS ciudadano_email,
                    ci.nombres AS ciudadano_nombres,
                    ci.apellidos AS ciudadano_apellidos,
                    d.nombre AS dependencia_nombre
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                JOIN usuarios u ON u.id_usuario = ci.usuario_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.id_cita = :id AND c.funcionario_id = :fid
            ");
            $stmt->execute([':id' => $citaId, ':fid' => $funcionarioId]);
            $cita = $stmt->fetch();

            if (!$cita) return ['success' => false, 'error' => 'Cita no encontrada'];

            // Generar token único para la respuesta
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+48 hours'));

            // Guardar propuesta SIN cambiar fecha/hora aún
            $upd = $pdo->prepare("
                UPDATE citas SET
                    estado                = 'propuesta_reprogramacion',
                    fecha_propuesta       = :nueva_fecha,
                    hora_propuesta        = :nueva_hora,
                    motivo_reprogramacion = :motivo,
                    token_respuesta       = :token,
                    token_expira          = :expira,
                    updated_at            = NOW()
                WHERE id_cita = :id
            ");
            $upd->execute([
                ':nueva_fecha' => $nuevaFecha,
                ':nueva_hora'  => $nuevaHora,
                ':motivo'      => $motivo,
                ':token'       => $token,
                ':expira'      => $expira,
                ':id'          => $citaId,
            ]);

            return [
                'success'               => true,
                'cita'                  => $cita,
                'nueva_fecha'           => $nuevaFecha,
                'nueva_hora'            => $nuevaHora,
                'motivo_reprogramacion' => $motivo,
                'token'                 => $token,
            ];

        } catch (Exception $e) {
            error_log('proponerReprogramacion: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Error interno'];
        }
    }
    /**
     * Obtener citas en curso del funcionario.
     */
    public static function getCitasEnCurso(int $funcionarioId): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    'cita'                        AS tipo,
                    c.id_cita                     AS id,
                    c.fecha,
                    c.hora_inicio::text           AS hora_inicio,
                    c.motivo,
                    c.hora_ingreso,
                    ci.nombres                    AS ciudadano_nombres,
                    ci.apellidos                  AS ciudadano_apellidos,
                    ci.telefono                   AS ciudadano_telefono,
                    d.nombre                      AS dependencia
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.funcionario_id = ?
                AND c.estado = 'en_curso'

                UNION ALL

                SELECT
                    'espontanea'                  AS tipo,
                    ve.id_visita                  AS id,
                    CURRENT_DATE                  AS fecha,
                    ve.hora_ingreso::text         AS hora_inicio,
                    ve.motivo,
                    ve.hora_ingreso,
                    ci.nombres                    AS ciudadano_nombres,
                    ci.apellidos                  AS ciudadano_apellidos,
                    ci.telefono                   AS ciudadano_telefono,
                    d.nombre                      AS dependencia
                FROM visitas_espontaneas ve
                LEFT JOIN ciudadanos_cache ci ON ci.id_ciudadano = ve.ciudadano_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = ve.dependencia_id
                WHERE ve.funcionario_id = ?
                AND ve.estado = 'en_curso'

                ORDER BY hora_ingreso ASC
            ");
            $stmt->execute([$funcionarioId, $funcionarioId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Funcionario::getCitasEnCurso - ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Marca como 'no_asistio' todas las citas pendientes/confirmadas cuya fecha ya pasó.
     * Se llama al cargar el dashboard para mantener estados coherentes.
     */
    public static function expirarCitasPasadas(int $funcionarioId): void {
        try {
            $pdo = Database::getConnection();
            $pdo->prepare("
                UPDATE citas
                SET estado     = 'no_asistio',
                    updated_at = NOW()
                WHERE funcionario_id = ?
                  AND fecha < CURRENT_DATE
                  AND estado IN ('pendiente', 'confirmada')
            ")->execute([$funcionarioId]);
        } catch (Exception $e) {
            error_log('Funcionario::expirarCitasPasadas - ' . $e->getMessage());
        }
    }

    /**
     * Registrar salida de una cita en curso.
     */
    public static function registrarSalida(int $id, int $funcionarioId, string $tipo = 'cita', string $resultado = ''): array {
        try {
            $pdo = Database::getConnection();

            if ($tipo === 'espontanea') {
                $sql = "UPDATE visitas_espontaneas
                        SET estado           = 'finalizada',
                            hora_salida      = NOW(),
                            resultado_visita = ?,
                            updated_at       = NOW()
                        WHERE id_visita      = ?
                        AND funcionario_id = ?
                        AND estado         = 'en_curso'";
            } else {
                $sql = "UPDATE citas
                        SET estado           = 'finalizada',
                            hora_salida      = NOW(),
                            resultado_visita = ?,
                            updated_at       = NOW()
                        WHERE id_cita        = ?
                        AND funcionario_id = ?
                        AND estado         = 'en_curso'";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$resultado ?: null, $id, $funcionarioId]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'El registro no está en curso o no pertenece a este funcionario'];
            }
            return ['success' => true];

        } catch (Exception $e) {
            error_log('Funcionario::registrarSalida - ' . $e->getMessage());
            return ['success' => false, 'error' => 'Error al registrar salida'];
        }
    }
}