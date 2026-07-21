<?php

class CitaController {

    private function requireCiudadano(): void {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Ciudadano') {
            redirect('/login');
        }
    }

    public function dashboard(): void {
        $this->requireCiudadano();

        $mensajeExito = $_SESSION['flash_mensaje'] ?? $_SESSION['mensaje_exito'] ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['mensaje_exito']);

        $ciudadanoId        = (int)($_SESSION['ciudadano_id'] ?? 0);
        $ciudadanoNombre    = $_SESSION['usuario_nombre'] ?? '';
        $partes             = explode(' ', trim($ciudadanoNombre));
        $ciudadanoIniciales = strtoupper(substr($partes[0] ?? '', 0, 1) . substr($partes[1] ?? '', 0, 1));
        $primerNombre       = $partes[0] ?? $ciudadanoNombre;

        $filtroEstado  = $_GET['estado'] ?? 'todas';
        $mensajeOk     = isset($_GET['nueva'])     ? 'Tu cita fue agendada exitosamente. Está pendiente de confirmación.' : '';
        $mensajeCancel = isset($_GET['cancelada']) ? 'Cita cancelada exitosamente.' : '';
        $errorDb       = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_id'])) {
            csrf_verify();
            $citaId    = (int)$_POST['cancelar_id'];
            $resultado = Cita::cancelar(
                $citaId,
                $ciudadanoId,
                $_POST['motivo_cancelacion'] ?? ''
            );
            if (isset($resultado['ok'])) {
                // ── Auditoría ──────────────────────────────────────
                Auditoria::registrar(
                    'CANCELAR_CITA',
                    "Ciudadano {$ciudadanoNombre} canceló la cita ID {$citaId}. " .
                    "Motivo: " . ($_POST['motivo_cancelacion'] ?? 'Sin motivo'),
                    'citas',
                    $citaId
                );
                redirect('/dashboard?cancelada=1');
            }
            $mensajeCancel = $resultado['error'];
        }

        $perPage      = 25;
        $page         = max(1, (int)($_GET['pagina'] ?? 1));
        $totalCitas   = Cita::countMisCitas($ciudadanoId, $filtroEstado);
        $totalPaginas = (int)ceil($totalCitas / $perPage) ?: 1;
        $page         = min($page, $totalPaginas);

        $stats = Cita::getStats($ciudadanoId);
        $citas = Cita::getMisCitas($ciudadanoId, $filtroEstado, $page, $perPage);

        // Config + festivos para validación client-side en el dashboard
        try {
            $pdo    = Database::getConnection();
            $cfgRow = $pdo->query("SELECT lunes,martes,miercoles,jueves,viernes,sabado,domingo FROM configuracion_sistema LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $boolTrue = ['t', true, 1, '1'];
            $mapDias  = ['domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6];
            $diasHabilesDash = [];
            if ($cfgRow) {
                foreach ($mapDias as $col => $jsDay) {
                    if (in_array($cfgRow[$col], $boolTrue, true)) $diasHabilesDash[] = $jsDay;
                }
            }
            $festivosDash = $pdo->query("
                SELECT descripcion,
                       CASE WHEN recurrente THEN TO_CHAR(fecha,'MM-DD') ELSE TO_CHAR(fecha,'YYYY-MM-DD') END AS clave,
                       recurrente
                FROM dias_festivos WHERE activo = true
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('CitaController::dashboard config - ' . $e->getMessage());
            $diasHabilesDash = [];
            $festivosDash    = [];
        }

        require_once BASE_PATH . '/views/ciudadano/dashboard.php';
    }

    public function agendar(): void {
        // AJAX: estos lookups son usados también por Recepción (no solo Ciudadano),
        // por lo que se atienden antes del guard de rol y solo exigen sesión iniciada.
        if (isset($_GET['action'])) {
            if (!isset($_SESSION['usuario_id'])) {
                redirect('/login');
            }

            // AJAX: funcionarios por dependencia
            if ($_GET['action'] === 'funcionarios') {
                header('Content-Type: application/json');
                $depId = (int)($_GET['dependencia_id'] ?? 0);
                echo json_encode($depId ? Funcionario::getPorDependencia($depId) : []);
                exit;
            }

            // AJAX: horarios disponibles
            if ($_GET['action'] === 'horarios') {
                header('Content-Type: application/json');
                $funcId = (int)($_GET['funcionario_id'] ?? 0);
                $fecha  = $_GET['fecha'] ?? '';
                if (!$funcId || !$fecha) {
                    echo json_encode(['error' => 'Parámetros inválidos.']);
                    exit;
                }
                echo json_encode(Cita::getHorariosDisponibles($funcId, $fecha));
                exit;
            }

            // AJAX: configuración del sistema
            if ($_GET['action'] === 'config') {
                header('Content-Type: application/json');
                $pdo    = Database::getConnection();
                $config = $pdo->query("SELECT * FROM configuracion_sistema LIMIT 1")
                              ->fetch(PDO::FETCH_ASSOC);

                // Incluir festivos activos para validación cliente-side
                $hoy = date('Y');
                $festivos = $pdo->query("
                    SELECT descripcion,
                           CASE WHEN recurrente THEN TO_CHAR(fecha, 'MM-DD') ELSE TO_CHAR(fecha, 'YYYY-MM-DD') END AS clave,
                           recurrente
                    FROM dias_festivos
                    WHERE activo = true
                ")->fetchAll(PDO::FETCH_ASSOC);
                $config['_festivos'] = $festivos;

                echo json_encode($config);
                exit;
            }
        }

        $this->requireCiudadano();

        if (!isset($_SESSION['usuario_email'])) {
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->getById($_SESSION['usuario_id']);
            $_SESSION['usuario_email'] = $usuario['email'] ?? '';
        }

        $ciudadanoId        = (int)($_SESSION['ciudadano_id'] ?? 0);
        $ciudadanoNombre    = $_SESSION['usuario_nombre'] ?? '';
        $partes             = explode(' ', trim($ciudadanoNombre));
        $ciudadanoIniciales = strtoupper(substr($partes[0] ?? '', 0, 1) . substr($partes[1] ?? '', 0, 1));

        $dependencias = Dependencia::getActivas();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $dependenciaId = (int)($_POST['dependencia_id'] ?? 0);
            $funcionarioId = (int)($_POST['funcionario_id'] ?? 0);
            $fecha         = trim($_POST['fecha']       ?? '');
            $horaInicio    = trim($_POST['hora_inicio'] ?? '');
            $motivo        = trim($_POST['motivo'] ?? '');

            if (!$dependenciaId || !$funcionarioId || !$fecha || !$horaInicio || !$motivo) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ||
                      !checkdate((int)substr($fecha,5,2), (int)substr($fecha,8,2), (int)substr($fecha,0,4))) {
                $error = 'La fecha seleccionada no es válida.';
            } elseif (!preg_match('/^\d{2}:\d{2}$/', $horaInicio)) {
                $error = 'La hora seleccionada no es válida.';
            } elseif (strlen($motivo) < 10) {
                $error = 'El motivo debe tener al menos 10 caracteres.';
            } elseif (strlen($motivo) > 1000) {
                $error = 'El motivo no puede superar los 1000 caracteres.';
            } else {
                $resultado = Cita::crear($ciudadanoId, $funcionarioId, $dependenciaId, $fecha, $horaInicio, $motivo);

                if (isset($resultado['ok'])) {
                    $idCita       = $resultado['id_cita'] ?? 0;
                    $citaCompleta = Cita::getById($idCita);

                    // Enviar correo
                    require_once BASE_PATH . '/config/mail.php';
                    $mailer = new Mailer();
                    if ($citaCompleta) {
                        $datosCita = [
                            'fecha'       => $fecha,
                            'hora'        => $horaInicio,
                            'dependencia' => $citaCompleta['dependencia'] ?? 'No especificada',
                            'funcionario' => ($citaCompleta['func_nombres'] ?? '') . ' ' .
                                            ($citaCompleta['func_apellidos'] ?? ''),
                            'motivo'      => $motivo,
                        ];

                        // Correo al ciudadano — confirmación de solicitud
                        $emailCiudadano = $_SESSION['usuario_email'] ?? ($citaCompleta['ciudadano_email'] ?? '');
                        if ($emailCiudadano)
                            $mailer->enviarConfirmacionSolicitud($ciudadanoNombre, $emailCiudadano, $datosCita);

                        // Correo al funcionario — notificación de nueva solicitud
                        $emailFuncionario = $citaCompleta['func_email'] ?? '';
                        if ($emailFuncionario) {
                            $nombreFuncionario = trim(($citaCompleta['func_nombres'] ?? '') . ' ' . ($citaCompleta['func_apellidos'] ?? ''));
                            $mailer->enviarNuevaSolicitudFuncionario($nombreFuncionario, $emailFuncionario, [
                                'ciudadano'   => $ciudadanoNombre,
                                'dependencia' => $citaCompleta['dependencia'] ?? 'No especificada',
                                'fecha'       => $fecha,
                                'hora'        => $horaInicio,
                                'motivo'      => $motivo,
                            ]);
                        }
                    }

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'AGENDAR_CITA',
                        "Ciudadano {$ciudadanoNombre} agendó una cita para el {$fecha} " .
                        "a las {$horaInicio} con el funcionario ID {$funcionarioId} " .
                        "en la dependencia ID {$dependenciaId}. Motivo: {$motivo}",
                        'citas',
                        $idCita
                    );

                    $_SESSION['mensaje_exito'] = 'Solicitud enviada, pendiente de aprobación';
                    redirect('/dashboard');
                }

                $error = $resultado['error'];
            }
        }

        require_once BASE_PATH . '/views/ciudadano/agendar.php';
    }

    public function responderReprogramacion(): void {
        $token  = $_GET['token']  ?? '';
        $accion = $_GET['accion'] ?? '';

        if (!$token) die('Enlace inválido.');

        try {
            $pdo = Database::getConnection();

            $stmt = $pdo->prepare("
                SELECT c.*,
                    u.email AS ciudadano_email,
                    ci.nombres AS ciudadano_nombres,
                    ci.apellidos AS ciudadano_apellidos,
                    f.nombres AS func_nombres, f.apellidos AS func_apellidos,
                    f.email AS func_email,
                    d.nombre AS dependencia_nombre
                FROM citas c
                JOIN ciudadanos_cache ci ON ci.id_ciudadano = c.ciudadano_id
                JOIN usuarios u ON u.id_usuario = ci.usuario_id
                JOIN funcionarios_cache f ON f.id_funcionario = c.funcionario_id
                LEFT JOIN dependencias_cache d ON d.id_dependencia = c.dependencia_id
                WHERE c.token_respuesta = :token
                AND c.estado = 'propuesta_reprogramacion'
                AND c.token_expira > NOW()
            ");
            $stmt->execute([':token' => $token]);
            $cita = $stmt->fetch();

            if (!$cita) {
                $resultado = 'expirado';
                require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                return;
            }

            // Config + festivos for client-side date validation in the view
            $cfgRow = $pdo->query("SELECT lunes,martes,miercoles,jueves,viernes,sabado,domingo FROM configuracion_sistema LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $boolTrue = ['t', true, 1, '1'];
            $mapDias  = ['domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6];
            $diasHabiles = [];
            if ($cfgRow) {
                foreach ($mapDias as $col => $jsDay) {
                    if (in_array($cfgRow[$col], $boolTrue, true)) {
                        $diasHabiles[] = $jsDay;
                    }
                }
            }
            $festivosReprog = $pdo->query("
                SELECT descripcion,
                       CASE WHEN recurrente THEN TO_CHAR(fecha,'MM-DD') ELSE TO_CHAR(fecha,'YYYY-MM-DD') END AS clave,
                       recurrente
                FROM dias_festivos WHERE activo = true
            ")->fetchAll(PDO::FETCH_ASSOC);

            if (!in_array($accion, ['aceptar', 'rechazar']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                return;
            }

            // Contrapropuesta del ciudadano
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contrapropuesta_hora'], $_POST['contrapropuesta_fecha'])) {
                csrf_verify();
                $nuevaFecha = trim($_POST['contrapropuesta_fecha']);
                $nuevaHora  = trim($_POST['contrapropuesta_hora']);

                // ── Validación de fecha y hora ──────────────────────
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $nuevaFecha)) {
                    $resultado = 'error_fecha';
                    require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                    return;
                }
                if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $nuevaHora)) {
                    $resultado = 'error_fecha';
                    require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                    return;
                }
                $ts = strtotime($nuevaFecha);
                if (!$ts || $ts < strtotime('today')) {
                    $resultado = 'error_fecha_pasado';
                    require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                    return;
                }
                // Normalizar hora a HH:MM
                $nuevaHora = substr($nuevaHora, 0, 5);

                $pdo->prepare("
                    UPDATE citas SET
                        estado          = 'contrapropuesta_ciudadano',
                        fecha_propuesta = :fecha,
                        hora_propuesta  = :hora,
                        token_respuesta = NULL,
                        updated_at      = NOW()
                    WHERE token_respuesta = :token
                ")->execute([':fecha' => $nuevaFecha, ':hora' => $nuevaHora, ':token' => $token]);

                // ── Auditoría ──────────────────────────────────────
                Auditoria::registrar(
                    'CONTRAPROPUESTA_CIUDADANO',
                    "Ciudadano {$cita['ciudadano_nombres']} {$cita['ciudadano_apellidos']} " .
                    "envió contrapropuesta para la cita ID {$cita['id_cita']}: " .
                    "{$nuevaFecha} a las {$nuevaHora}",
                    'citas',
                    (int)$cita['id_cita']
                );

                require_once BASE_PATH . '/config/mail.php';
                $mailer = new Mailer();
                $mailer->enviarContrapropuestaCiudadano(
                    $cita['func_nombres'] . ' ' . $cita['func_apellidos'],
                    $cita['func_email'],
                    [
                        'ciudadano'      => $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
                        // "Anterior" = propuesta del funcionario (lo que el ciudadano está contraproponiendo)
                        'fecha_original' => date('d/m/Y', strtotime($cita['fecha_propuesta'])),
                        'hora_original'  => date('h:i A', strtotime($cita['hora_propuesta'])),
                        'nueva_fecha'    => date('d/m/Y', strtotime($nuevaFecha)),
                        'nueva_hora'     => date('h:i A', strtotime($nuevaHora)),
                        'dependencia'    => $cita['dependencia_nombre'],
                        'url_dashboard'  => app_base_url('funcionario/dashboard'),
                    ]
                );

                if (!empty($_POST['from_dashboard']) || !empty($_GET['from_dashboard'])) {
                    $_SESSION['flash_mensaje'] = 'Tu propuesta fue enviada. El funcionario la revisará y recibirás una respuesta.';
                    redirect('/dashboard');
                    return;
                }
                $resultado = 'contrapropuesta_enviada';
                require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';
                return;
            }

            // Aceptar reprogramación
            if ($accion === 'aceptar') {
                $configStmt = $pdo->query("SELECT intervalo_min FROM configuracion_sistema LIMIT 1");
                $intervalo  = (int)($configStmt->fetchColumn() ?: 15);

                $pdo->prepare("
                    UPDATE citas SET
                        fecha           = fecha_propuesta,
                        hora_inicio     = hora_propuesta,
                        hora_fin        = hora_propuesta::time + (? || ' minutes')::interval,
                        estado          = 'confirmada',
                        fecha_propuesta = NULL,
                        hora_propuesta  = NULL,
                        token_respuesta = NULL,
                        updated_at      = NOW()
                    WHERE token_respuesta = ?
                ")->execute([$intervalo, $token]);

                // ── Auditoría ──────────────────────────────────────
                Auditoria::registrar(
                    'ACEPTAR_REPROGRAMACION',
                    "Ciudadano {$cita['ciudadano_nombres']} {$cita['ciudadano_apellidos']} " .
                    "aceptó la reprogramación de la cita ID {$cita['id_cita']}",
                    'citas',
                    (int)$cita['id_cita']
                );

                // Correo de confirmación al ciudadano con la nueva fecha/hora confirmada
                require_once BASE_PATH . '/config/mail.php';
                $mailer = new Mailer();
                $mailer->enviarConfirmacionAprobacion(
                    $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
                    $cita['ciudadano_email'],
                    [
                        'id_cita'     => $cita['id_cita'],
                        'fecha'       => $cita['fecha_propuesta'],
                        'hora'        => $cita['hora_propuesta'],
                        'dependencia' => $cita['dependencia_nombre'],
                        'funcionario' => $cita['func_nombres'] . ' ' . $cita['func_apellidos'],
                        'motivo'      => $cita['motivo'],
                    ]
                );

                if (!empty($_GET['from_dashboard'])) {
                    $_SESSION['flash_mensaje'] = '¡Cita confirmada! La nueva fecha ha sido aceptada.';
                    redirect('/dashboard');
                    return;
                }
                $resultado = 'aceptada';
            }

            // Rechazar reprogramación
            if ($accion === 'rechazar') {
                $pdo->prepare("
                    UPDATE citas SET
                        estado          = 'pendiente',
                        fecha_propuesta = NULL,
                        hora_propuesta  = NULL,
                        token_respuesta = NULL,
                        updated_at      = NOW()
                    WHERE token_respuesta = :token
                ")->execute([':token' => $token]);

                // ── Auditoría ──────────────────────────────────────
                Auditoria::registrar(
                    'RECHAZAR_REPROGRAMACION',
                    "Ciudadano {$cita['ciudadano_nombres']} {$cita['ciudadano_apellidos']} " .
                    "rechazó la reprogramación de la cita ID {$cita['id_cita']}",
                    'citas',
                    (int)$cita['id_cita']
                );

                require_once BASE_PATH . '/config/mail.php';
                $mailer = new Mailer();
                $mailer->enviarRechazoReprogramacion(
                    $cita['func_nombres'] . ' ' . $cita['func_apellidos'],
                    $cita['func_email'],
                    [
                        'ciudadano'     => $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
                        'dependencia'   => $cita['dependencia_nombre'],
                        'url_dashboard' => app_base_url('funcionario/dashboard'),
                    ]
                );

                if (!empty($_GET['from_dashboard'])) {
                    $_SESSION['flash_mensaje'] = 'Propuesta rechazada. El funcionario buscará una nueva alternativa.';
                    redirect('/dashboard');
                    return;
                }
                $resultado = 'rechazada';
            }

            require_once BASE_PATH . '/views/ciudadano/respuesta_reprogramacion.php';

        } catch (Exception $e) {
            error_log('responderReprogramacion: ' . $e->getMessage());
            die('Error procesando la respuesta.');
        }
    }
}