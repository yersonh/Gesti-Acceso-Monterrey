<?php

class FuncionarioController {

    private array $funcionario;
    private int   $funcionarioId;

    private function requireFuncionario(): void {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Funcionario') {
            redirect('/login');
        }
    }

    public function dashboard(): void {
        $this->requireFuncionario();

        $usuarioModel = new UsuarioModel();
        $usuario      = $usuarioModel->getById($_SESSION['usuario_id']);

        if (!$usuario) die('Error: Usuario no encontrado');

        $funcionario = null;
        if ($_SESSION['usuario_rol'] === 'Recepcionista') {
            try {
                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare('SELECT *, id_personal AS id_funcionario FROM personal WHERE usuario_id = ?');
                $stmt->execute([$usuario['id_usuario']]);
                $funcionario = $stmt->fetch();
            } catch (Exception $e) {
                error_log('FuncionarioController::dashboard recepcionista - ' . $e->getMessage());
            }
        } else {
            $funcionario = Funcionario::getPorUsuarioId($usuario['id_usuario']);
            if (!$funcionario) {
                try {
                    $pdo  = Database::getConnection();
                    $stmt = $pdo->prepare('SELECT * FROM funcionarios_cache WHERE usuario_id = ?');
                    $stmt->execute([$usuario['id_usuario']]);
                    $funcionario = $stmt->fetch();
                } catch (Exception $e) {
                    error_log('FuncionarioController::dashboard - ' . $e->getMessage());
                }
            }
        }
        if (!$funcionario) die('Error: No se encontró el perfil del usuario.');

        $this->funcionario   = $funcionario;
        $this->funcionarioId = (int)$funcionario['id_funcionario'];

        $passwordTemporal = false;
        if (!empty($usuario['password_temporal'])) {
            $expires = $usuario['password_temporal_expires_at'] ?? null;
            if ($expires && strtotime($expires) > time()) {
                $passwordTemporal = true;
            }
        }

        $mensaje = $_SESSION['flash_mensaje'] ?? '';
        $error   = $_SESSION['flash_error']   ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $action = $_POST['action'] ?? '';

            if ($action === 'aprobar' && isset($_POST['cita_id'])) {
                $citaId = (int)$_POST['cita_id'];

                // Opción C: rechazar aprobación de citas con fecha pasada
                try {
                    $pdo  = Database::getConnection();
                    $stmt = $pdo->prepare("SELECT fecha FROM citas WHERE id_cita = ? AND funcionario_id = ?");
                    $stmt->execute([$citaId, $this->funcionarioId]);
                    $fechaCita = $stmt->fetchColumn();
                    if (!$fechaCita || $fechaCita < date('Y-m-d')) {
                        $_SESSION['flash_error'] = 'No se puede aprobar una cita cuya fecha ya pasó.';
                        redirect('/funcionario/dashboard');
                    }
                } catch (Exception $e) {
                    error_log('FuncionarioController aprobar - validar fecha: ' . $e->getMessage());
                }

                $resultado = Funcionario::aprobarCita($citaId, $this->funcionarioId);
                if ($resultado['success']) {
                    $this->enviarCorreoAprobacion($resultado['cita'], $funcionario);
                    $_SESSION['flash_mensaje'] = 'Cita aprobada exitosamente';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'APROBAR_CITA',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "aprobó la cita ID {$citaId}",
                        'citas',
                        $citaId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            if ($action === 'rechazar' && isset($_POST['cita_id'], $_POST['motivo_rechazo'])) {
                $citaId        = (int)$_POST['cita_id'];
                $motivoRechazo = trim($_POST['motivo_rechazo']);

                if (strlen($motivoRechazo) > 500) {
                    $_SESSION['flash_error'] = 'El motivo no puede superar los 500 caracteres.';
                    redirect('/funcionario/dashboard');
                }

                $resultado = Funcionario::rechazarCita($citaId, $this->funcionarioId, $motivoRechazo);
                if ($resultado['success']) {
                    $this->enviarCorreoRechazo($resultado['cita'], $motivoRechazo);
                    $_SESSION['flash_mensaje'] = 'Cita rechazada exitosamente';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'RECHAZAR_CITA',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "rechazó la cita ID {$citaId}. Motivo: {$motivoRechazo}",
                        'citas',
                        $citaId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            if ($action === 'reprogramar' && isset($_POST['cita_id'], $_POST['nueva_fecha'], $_POST['nueva_hora'], $_POST['motivo_reprogramacion'])) {
                $citaId       = (int)$_POST['cita_id'];
                $nuevaFecha   = trim($_POST['nueva_fecha']);
                $nuevaHora    = trim($_POST['nueva_hora']);
                $motivoReprog = trim($_POST['motivo_reprogramacion']);

                // Opción C: no permitir reprogramar citas con fecha pasada
                try {
                    $pdo  = Database::getConnection();
                    $stmt = $pdo->prepare("SELECT fecha FROM citas WHERE id_cita = ? AND funcionario_id = ?");
                    $stmt->execute([$citaId, $this->funcionarioId]);
                    $fechaCita = $stmt->fetchColumn();
                    if (!$fechaCita || $fechaCita < date('Y-m-d')) {
                        $_SESSION['flash_error'] = 'No se puede reprogramar una cita cuya fecha ya pasó.';
                        redirect('/funcionario/dashboard');
                    }
                } catch (Exception $e) {
                    error_log('FuncionarioController reprogramar - validar fecha: ' . $e->getMessage());
                }

                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $nuevaFecha) ||
                    !checkdate((int)substr($nuevaFecha,5,2), (int)substr($nuevaFecha,8,2), (int)substr($nuevaFecha,0,4))) {
                    $_SESSION['flash_error'] = 'Fecha de reprogramación inválida.';
                    redirect('/funcionario/dashboard');
                }
                if (!preg_match('/^\d{2}:\d{2}$/', $nuevaHora)) {
                    $_SESSION['flash_error'] = 'Hora de reprogramación inválida.';
                    redirect('/funcionario/dashboard');
                }
                if (strlen($motivoReprog) > 500) {
                    $_SESSION['flash_error'] = 'El motivo no puede superar los 500 caracteres.';
                    redirect('/funcionario/dashboard');
                }

                $resultado = Funcionario::proponerReprogramacion(
                    $citaId,
                    $this->funcionarioId,
                    $nuevaFecha,
                    $nuevaHora,
                    $motivoReprog
                );
                if ($resultado['success']) {
                    $this->enviarCorreoReprogramacion($resultado, $funcionario);
                    $_SESSION['flash_mensaje'] = 'Propuesta enviada al ciudadano. Esperando su confirmación.';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'PROPONER_REPROGRAMACION',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "propuso reprogramar la cita ID {$citaId} " .
                        "para {$nuevaFecha} a las {$nuevaHora}. " .
                        "Motivo: {$motivoReprog}",
                        'citas',
                        $citaId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            if ($action === 'bloquear' && isset($_POST['fecha'], $_POST['hora_inicio'], $_POST['hora_fin'], $_POST['motivo_bloqueo'])) {
                $fechaBloqueo  = trim($_POST['fecha']);
                $horaInicioBlq = trim($_POST['hora_inicio']);
                $horaFinBlq    = trim($_POST['hora_fin']);
                $motivoBloqueo = trim($_POST['motivo_bloqueo']);

                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBloqueo) ||
                    !checkdate((int)substr($fechaBloqueo,5,2), (int)substr($fechaBloqueo,8,2), (int)substr($fechaBloqueo,0,4))) {
                    $_SESSION['flash_error'] = 'Fecha de bloqueo inválida.';
                    redirect('/funcionario/dashboard');
                }
                if (!preg_match('/^\d{2}:\d{2}$/', $horaInicioBlq) || !preg_match('/^\d{2}:\d{2}$/', $horaFinBlq)) {
                    $_SESSION['flash_error'] = 'Horario de bloqueo inválido.';
                    redirect('/funcionario/dashboard');
                }
                if ($horaFinBlq <= $horaInicioBlq) {
                    $_SESSION['flash_error'] = 'La hora de fin debe ser posterior a la hora de inicio.';
                    redirect('/funcionario/dashboard');
                }
                if (strlen($motivoBloqueo) > 500) {
                    $_SESSION['flash_error'] = 'El motivo no puede superar los 500 caracteres.';
                    redirect('/funcionario/dashboard');
                }

                $resultado = Funcionario::bloquearHorario(
                    $this->funcionarioId,
                    $fechaBloqueo,
                    $horaInicioBlq,
                    $horaFinBlq,
                    $motivoBloqueo
                );
                if ($resultado['success']) {
                    $_SESSION['flash_mensaje'] = 'Horario bloqueado exitosamente';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'BLOQUEAR_HORARIO',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "bloqueó el horario del {$fechaBloqueo} " .
                        "de {$horaInicioBlq} a {$horaFinBlq}. " .
                        "Motivo: {$motivoBloqueo}",
                        'horarios_bloqueados',
                        $this->funcionarioId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            if ($action === 'registrar_salida' && isset($_POST['cita_id'])) {
                $visitaId   = (int)$_POST['cita_id'];
                $tipoVisita = in_array($_POST['tipo'] ?? '', ['cita', 'espontanea', 'visita_directa'], true)
                    ? $_POST['tipo']
                    : 'cita';
                $resultado  = Funcionario::registrarSalida($visitaId, $this->funcionarioId, $tipoVisita);
                if ($resultado['success']) {
                    $this->enviarLinkValoracion($visitaId, $tipoVisita);
                    $_SESSION['flash_mensaje'] = 'Salida registrada exitosamente.';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'REGISTRAR_SALIDA',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "registró la salida de la visita ID {$visitaId} (tipo: {$tipoVisita})",
                        'citas',
                        $visitaId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            if ($action === 'eliminar_bloqueo' && isset($_POST['bloqueo_id'])) {
                $bloqueoId = (int)$_POST['bloqueo_id'];
                $resultado = Funcionario::eliminarBloqueo($bloqueoId, $this->funcionarioId);
                if ($resultado['success']) {
                    $_SESSION['flash_mensaje'] = 'Bloqueo eliminado exitosamente';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'ELIMINAR_BLOQUEO',
                        "Funcionario {$funcionario['nombres']} {$funcionario['apellidos']} " .
                        "eliminó el bloqueo de horario ID {$bloqueoId}",
                        'horarios_bloqueados',
                        $bloqueoId
                    );
                } else {
                    $_SESSION['flash_error'] = $resultado['error'];
                }
                redirect('/funcionario/dashboard');
            }

            redirect('/funcionario/dashboard');
        }

        // Opción A: expirar citas pasadas antes de renderizar
        Funcionario::expirarCitasPasadas($this->funcionarioId);

        $resumen               = Funcionario::getResumenActividades($this->funcionarioId);
        $solicitudesPendientes = Funcionario::getSolicitudesPendientes($this->funcionarioId);
        $dependencias          = Funcionario::getDependencias($this->funcionarioId);
        $horariosBloqueados    = Funcionario::getHorariosBloqueados($this->funcionarioId);
        $funcionarioId         = $this->funcionarioId;
        $citasEnCurso          = Funcionario::getCitasEnCurso($this->funcionarioId);

        // Config + festivos para validación client-side de fecha en reprogramar
        $pdo    = Database::getConnection();
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
        $festivosConfig = $pdo->query("
            SELECT descripcion,
                   CASE WHEN recurrente THEN TO_CHAR(fecha,'MM-DD') ELSE TO_CHAR(fecha,'YYYY-MM-DD') END AS clave,
                   recurrente
            FROM dias_festivos WHERE activo = true
        ")->fetchAll(PDO::FETCH_ASSOC);

        require_once BASE_PATH . '/views/funcionario/dashboard.php';
    }

    private function enviarCorreoAprobacion(array $cita, array $funcionario): void {
        if (!file_exists(BASE_PATH . '/config/mail.php')) return;
        require_once BASE_PATH . '/config/mail.php';
        $mailer    = new Mailer();
        $datosCita = [
            'id_cita'     => $cita['id_cita'],
            'fecha'       => $cita['fecha'],
            'hora'        => $cita['hora_inicio'],
            'dependencia' => $cita['dependencia_nombre'] ?? 'No especificada',
            'funcionario' => $funcionario['nombres'] . ' ' . $funcionario['apellidos'],
            'motivo'      => $cita['motivo'],
        ];
        $mailer->enviarConfirmacionAprobacion(
            $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
            $cita['ciudadano_email'],
            $datosCita
        );
    }

    private function enviarCorreoRechazo(array $cita, string $motivoRechazo): void {
        if (!file_exists(BASE_PATH . '/config/mail.php')) return;
        require_once BASE_PATH . '/config/mail.php';
        $mailer = new Mailer();
        $mailer->enviarNotificacionRechazo(
            $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
            $cita['ciudadano_email'],
            $cita['motivo'],
            $motivoRechazo
        );
    }

    private function enviarCorreoReprogramacion(array $resultado, array $funcionario): void {
        if (!file_exists(BASE_PATH . '/config/mail.php')) return;
        require_once BASE_PATH . '/config/mail.php';
        $mailer  = new Mailer();
        $cita    = $resultado['cita'];
        $baseUrl = rtrim(app_base_url(), '/');
        $datosReprogramacion = [
            'fecha_original' => date('d/m/Y', strtotime($cita['fecha'])),
            'hora_original'  => date('h:i A', strtotime($cita['hora_inicio'])),
            'nueva_fecha'    => date('d/m/Y', strtotime($resultado['nueva_fecha'])),
            'nueva_hora'     => date('h:i A', strtotime($resultado['nueva_hora'])),
            'motivo'         => $resultado['motivo_reprogramacion'],
            'dependencia'    => $cita['dependencia_nombre'] ?? 'No especificada',
            'funcionario'    => $funcionario['nombres'] . ' ' . $funcionario['apellidos'],
            'url_aceptar'    => $baseUrl . '/cita/responder?token=' . $resultado['token'] . '&accion=aceptar',
            'url_rechazar'   => $baseUrl . '/cita/responder?token=' . $resultado['token'],
        ];
        $mailer->enviarNotificacionReprogramacion(
            $cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'],
            $cita['ciudadano_email'],
            $datosReprogramacion
        );
    }

    private function enviarLinkValoracion(int $visitaId, string $tipo): void {
        try {
            $valoracionModel = new ValoracionModel();
            $token           = $valoracionModel->crear($visitaId, $tipo);
            $datosVisita     = $valoracionModel->getDatosVisita($visitaId, $tipo);

            $baseUrl         = rtrim(app_base_url(), '/');
            $link            = $baseUrl . '/valorar?token=' . $token;
            $email           = $datosVisita['email'] ?? null;
            $nombreVisitante = trim(($datosVisita['nombres'] ?? '') . ' ' . ($datosVisita['apellidos'] ?? ''));
            $dependencia     = $datosVisita['dependencia'] ?? '';
            $nombreFuncionario = trim(($datosVisita['func_nombres'] ?? '') . ' ' . ($datosVisita['func_apellidos'] ?? ''));

            if (!$email) return;

            require_once BASE_PATH . '/config/mail.php';
            $mailer = new Mailer();
            $mailer->enviarLinkValoracion($nombreVisitante, $email, $link, $dependencia, $nombreFuncionario);

        } catch (Exception $e) {
            error_log('enviarLinkValoracion error: ' . $e->getMessage());
        }
    }
}