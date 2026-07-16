<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ClienteCore.php';

class RecepcionModel {

    private const ORDEN_SQL = "
        CASE
            WHEN tipo_registro = 'cita' AND estado = 'pendiente'  THEN 1
            WHEN tipo_registro = 'cita' AND estado = 'confirmada' THEN 2
            WHEN tipo_registro = 'cita' AND estado = 'en_curso'   THEN 3
            WHEN tipo_registro = 'espontanea' AND estado = 'en_curso' THEN 4
            WHEN tipo_registro = 'cita' AND estado = 'finalizada' THEN 5
            WHEN tipo_registro = 'espontanea' AND estado = 'finalizada' THEN 6
            ELSE 7
        END,
        CASE
            WHEN tipo_registro = 'cita' THEN fecha_cita::text || ' ' || hora_inicio::text
            ELSE hora_ingreso::text
        END ASC
    ";

    public static function obtenerRegistrosDia(): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->query("
                SELECT * FROM vista_registro_visitas
                ORDER BY " . self::ORDEN_SQL . "
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('RecepcionModel::obtenerRegistrosDia - ' . $e->getMessage());
            return [];
        }
    }

    public static function buscarRegistros(string $termino): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM vista_registro_visitas
                WHERE LOWER(nombres_ciudadano || ' ' || apellidos_ciudadano) LIKE :t
                   OR LOWER(numero_identificacion) LIKE :t
                   OR id::TEXT = :exacto
                ORDER BY " . self::ORDEN_SQL . "
            ");
            $terminoLower = mb_strtolower(trim($termino));
            $stmt->execute([
                ':t'       => '%' . $terminoLower . '%',
                ':exacto'  => $terminoLower,
            ]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('RecepcionModel::buscarRegistros - ' . $e->getMessage());
            return [];
        }
    }

    public static function registrarIngreso(int $idCita): bool {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                UPDATE citas SET estado = 'en_curso', hora_ingreso = NOW(), updated_at = NOW()
                WHERE id_cita = :id AND estado = 'confirmada'
            ");
            $stmt->execute([':id' => $idCita]);

            if ($stmt->rowCount() > 0) {
                Auditoria::registrar(
                    'REGISTRAR_INGRESO',
                    "Recepción registró ingreso de la cita ID {$idCita}",
                    'citas',
                    $idCita
                );
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log('RecepcionModel::registrarIngreso - ' . $e->getMessage());
            return false;
        }
    }

    public static function obtenerAlertasDia(int $minutosAnticipacion = 15): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM vista_registro_visitas
                WHERE tipo_registro = 'cita' AND estado = 'confirmada'
                  AND (fecha_cita + hora_inicio) <= (NOW() + (:anticipo * INTERVAL '1 minute'))
                  AND (fecha_cita + hora_inicio) >= (NOW() - INTERVAL '30 minutes')
                ORDER BY fecha_cita, hora_inicio
            ");
            $stmt->execute([':anticipo' => $minutosAnticipacion]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('RecepcionModel::obtenerAlertasDia - ' . $e->getMessage());
            return [];
        }
    }

    public static function buscarCiudadanoLocal(string $numeroIdentificacion): ?array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM ciudadanos_cache WHERE numero_identificacion = :num LIMIT 1
            ");
            $stmt->execute([':num' => $numeroIdentificacion]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            error_log('RecepcionModel::buscarCiudadanoLocal - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra una visita espontánea. Si el ciudadano no existe localmente,
     * lo crea en Core (fuente de verdad) y refleja el resultado en la cache
     * — mismo patrón que UsuarioModel::create().
     */
    public static function registrarVisitaEspontanea(array $datos): int {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $ciudadanoId = (int)($datos['ciudadano_id'] ?? 0);

            if ($ciudadanoId === 0) {
                // a) Ciudadano nuevo → crear en Core y reflejar en la cache
                $core = new ClienteCore();

                try {
                    $ciudadanoCore = $core->crearCiudadano([
                        'tipo_identificacion_id' => $core->idTipoIdentificacion($datos['tipo_identificacion']),
                        'numero_identificacion'  => $datos['numero_identificacion'],
                        'nombres'                => $datos['nombres'],
                        'apellidos'              => $datos['apellidos'],
                        'telefono'               => $datos['telefono']     ?? null,
                        'email'                  => $datos['email']        ?? null,
                        'whatsapp'               => $datos['whatsapp']     ?? null,
                        'direccion'              => $datos['direccion']    ?? null,
                        'proveniencia'           => $datos['proveniencia'] ?? null,
                    ]);

                } catch (CoreValidationException $eCore) {
                    // Core dice que la cédula ya existe allá, aunque local no la teníamos sincronizada
                    $ciudadanoCore = $core->buscarCiudadanoPorIdentificacion(
                        $datos['tipo_identificacion'],
                        $datos['numero_identificacion']
                    );

                    if (!$ciudadanoCore) {
                        throw $eCore;
                    }

                } catch (Exception $eConexion) {
                    throw new Exception(
                        'No se pudo verificar la identificación con el sistema central. Intenta nuevamente en unos minutos.',
                        0,
                        $eConexion
                    );
                }

                // usuario_id se omite del ON CONFLICT DO UPDATE a propósito: un visitante
                // espontáneo no tiene cuenta de acceso, y si el registro ya existía con
                // una cuenta vinculada (usuario_id no nulo), no se debe sobreescribir con NULL.
                $stmtUpsert = $pdo->prepare("
                    INSERT INTO ciudadanos_cache
                        (id_ciudadano, nombres, apellidos, tipo_identificacion, numero_identificacion,
                         telefono, email, direccion, proveniencia, whatsapp, usuario_id, activo, synced_at)
                    VALUES
                        (:id_ciudadano, :nombres, :apellidos, :tipo_identificacion, :numero_identificacion,
                         :telefono, :email, :direccion, :proveniencia, :whatsapp, NULL, :activo, NOW())
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
                        activo                  = EXCLUDED.activo,
                        synced_at               = NOW()
                ");
                $stmtUpsert->execute([
                    ':id_ciudadano'           => $ciudadanoCore['id'],
                    ':nombres'                => $ciudadanoCore['nombres']               ?? $datos['nombres'],
                    ':apellidos'              => $ciudadanoCore['apellidos']             ?? $datos['apellidos'],
                    ':tipo_identificacion'    => $datos['tipo_identificacion'],
                    ':numero_identificacion'  => $ciudadanoCore['numero_identificacion'] ?? $datos['numero_identificacion'],
                    ':telefono'               => $ciudadanoCore['telefono']              ?? $datos['telefono']     ?? null,
                    ':email'                  => $ciudadanoCore['email']                 ?? $datos['email']        ?? null,
                    ':direccion'              => $ciudadanoCore['direccion']             ?? $datos['direccion']    ?? null,
                    ':proveniencia'           => $ciudadanoCore['proveniencia']          ?? $datos['proveniencia'] ?? null,
                    ':whatsapp'               => $ciudadanoCore['whatsapp']              ?? $datos['whatsapp']     ?? null,
                    ':activo'                 => $ciudadanoCore['activo'] ?? true,
                ]);

                $ciudadanoId = (int)$ciudadanoCore['id'];

            } else {
                // b) Ciudadano existente → completar datos vacíos en Core, solo si aporta algo nuevo
                $stmtLocal = $pdo->prepare("SELECT * FROM ciudadanos_cache WHERE id_ciudadano = :id");
                $stmtLocal->execute([':id' => $ciudadanoId]);
                $ciudadanoLocal = $stmtLocal->fetch();

                if ($ciudadanoLocal) {
                    $camposCompletar = ['telefono', 'email', 'whatsapp', 'direccion', 'proveniencia'];
                    $hayDatoNuevo = false;
                    foreach ($camposCompletar as $campo) {
                        if (!empty($datos[$campo]) && empty($ciudadanoLocal[$campo])) {
                            $hayDatoNuevo = true;
                            break;
                        }
                    }

                    if ($hayDatoNuevo) {
                        try {
                            $core = new ClienteCore();
                            $actualizado = $core->actualizarCiudadano($ciudadanoId, [
                                'telefono'     => $datos['telefono']     ?? $ciudadanoLocal['telefono']     ?? null,
                                'email'        => $datos['email']        ?? $ciudadanoLocal['email']        ?? null,
                                'whatsapp'     => $datos['whatsapp']     ?? $ciudadanoLocal['whatsapp']     ?? null,
                                'direccion'    => $datos['direccion']    ?? $ciudadanoLocal['direccion']    ?? null,
                                'proveniencia' => $datos['proveniencia'] ?? $ciudadanoLocal['proveniencia'] ?? null,
                            ]);

                            $pdo->prepare("
                                UPDATE ciudadanos_cache SET
                                    telefono     = :telefono,
                                    email        = :email,
                                    whatsapp     = :whatsapp,
                                    direccion    = :direccion,
                                    proveniencia = :proveniencia,
                                    synced_at    = NOW()
                                WHERE id_ciudadano = :id
                            ")->execute([
                                ':telefono'     => $actualizado['telefono']     ?? $ciudadanoLocal['telefono'],
                                ':email'        => $actualizado['email']        ?? $ciudadanoLocal['email'],
                                ':whatsapp'     => $actualizado['whatsapp']     ?? $ciudadanoLocal['whatsapp'],
                                ':direccion'    => $actualizado['direccion']    ?? $ciudadanoLocal['direccion'],
                                ':proveniencia' => $actualizado['proveniencia'] ?? $ciudadanoLocal['proveniencia'],
                                ':id'           => $ciudadanoId,
                            ]);

                        } catch (Exception $eCore) {
                            // No crítico — se continúa con los datos que ya había localmente
                            error_log('RecepcionModel::registrarVisitaEspontanea - actualizar en Core falló: ' . $eCore->getMessage());
                        }
                    }
                }
            }

            // c) Insertar la visita espontánea
            $stmtVisita = $pdo->prepare("
                INSERT INTO visitas_espontaneas
                    (ciudadano_id, funcionario_id, dependencia_id, motivo, atendido_por, notas, estado, hora_ingreso)
                VALUES
                    (:ciudadano_id, :funcionario_id, :dependencia_id, :motivo, :atendido_por, :notas, 'en_curso', NOW())
                RETURNING id_visita
            ");
            $stmtVisita->execute([
                ':ciudadano_id'   => $ciudadanoId,
                ':funcionario_id' => (int)$datos['funcionario_id'],
                ':dependencia_id' => (int)$datos['dependencia_id'],
                ':motivo'         => $datos['motivo'],
                ':atendido_por'   => $_SESSION['usuario_id'] ?? null,
                ':notas'          => $datos['notas'] ?? null,
            ]);
            $idVisita = (int)$stmtVisita->fetchColumn();

            $pdo->commit();

            Auditoria::registrar(
                'REGISTRAR_VISITA_ESPONTANEA',
                "Visita espontánea registrada: {$datos['nombres']} {$datos['apellidos']} " .
                "(ID {$datos['tipo_identificacion']} {$datos['numero_identificacion']}), " .
                "funcionario ID {$datos['funcionario_id']}, dependencia ID {$datos['dependencia_id']}. " .
                "Motivo: {$datos['motivo']}",
                'visitas_espontaneas',
                $idVisita
            );

            return $idVisita;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('RecepcionModel::registrarVisitaEspontanea - ' . $e->getMessage());
            throw $e;
        }
    }
}
