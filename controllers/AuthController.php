<?php
require_once BASE_PATH . '/config/mail.php';

class AuthController {

    private UsuarioModel $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login(): void {
        $error     = '';
        $bloqueado = false;
        $minutos   = 0;

        // Verificar bloqueo tanto en GET como en POST para que el botón
        // aparezca deshabilitado desde que el usuario llega a la página.
        $rateLimit = RateLimiter::check();
        if ($rateLimit['blocked']) {
            $bloqueado = true;
            $minutos   = $rateLimit['minutos_restantes'];
            $error     = "Demasiados intentos fallidos. Espera {$minutos} minuto(s) antes de intentar de nuevo.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            if ($bloqueado) {
                require_once BASE_PATH . '/views/auth/login.php';
                return;
            }

            $email    = $_POST['email']    ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Por favor complete todos los campos';
            } else {
                $usuario = $this->usuarioModel->verificarCredenciales($email, $password);

                if ($usuario) {
                    if (!$usuario['activo']) {
                        $error = 'Su cuenta está desactivada. Contacte al administrador.';
                        RateLimiter::registrarFallo($email);

                        // ── Auditoría ──────────────────────────────
                        Auditoria::registrar(
                            'LOGIN_FALLIDO',
                            "Intento de login con cuenta desactivada: {$email}",
                            'usuarios',
                            (int)$usuario['id_usuario']
                        );
                    } else {
                        RateLimiter::limpiar();

                        // Si ya había otra sesión activa de un usuario diferente, registrar
                        // su logout forzado antes de destruir la sesión
                        if (!empty($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] !== (int)$usuario['id_usuario']) {
                            Auditoria::registrar(
                                'LOGOUT_FORZADO',
                                "La sesión de {$_SESSION['usuario_nombre']} ({$_SESSION['usuario_email']}) " .
                                "fue cerrada al iniciar sesión {$email} en el mismo navegador",
                                'usuarios',
                                (int)$_SESSION['usuario_id']
                            );
                        }

                        session_regenerate_id(true);

                        $_SESSION['usuario_id']     = $usuario['id_usuario'];
                        $_SESSION['usuario_nombre'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
                        $_SESSION['usuario_email']  = $usuario['email'];
                        $_SESSION['usuario_rol']    = $usuario['rol'];

                        try {
                            $pdo = Database::getConnection();
                            $pdo->prepare("UPDATE usuarios SET last_login_at = NOW() WHERE id_usuario = ?")
                                ->execute([$usuario['id_usuario']]);
                        } catch (Exception $e) {
                            error_log('AuthController: no se pudo actualizar last_login_at - ' . $e->getMessage());
                        }

                        // ── Auditoría ──────────────────────────────
                        Auditoria::registrar(
                            'LOGIN_EXITOSO',
                            "El usuario {$usuario['nombres']} {$usuario['apellidos']} " .
                            "({$email}) inició sesión con rol {$usuario['rol']}",
                            'usuarios',
                            (int)$usuario['id_usuario']
                        );

                        if ($usuario['rol'] === 'Ciudadano') {
                            $_SESSION['ciudadano_id'] = $usuario['id_ciudadano'] ?? null;
                            redirect('/dashboard');
                        } elseif ($usuario['rol'] === 'Funcionario') {
                            redirect('/funcionario/dashboard');
                        } elseif ($usuario['rol'] === 'Recepcionista') {
                            redirect('/recepcion');
                        } elseif ($usuario['rol'] === 'Administrador') {
                            redirect('/admin/dashboard');
                        } elseif ($usuario['rol'] === 'Superadmin') {
                            redirect('/superadmin/reportes');
                        }
                        exit;
                    }
                } else {
                    RateLimiter::registrarFallo($email);
                    $error = 'Correo o contraseña incorrectos';

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'LOGIN_FALLIDO',
                        "Intento de login fallido con email: {$email}"
                    );
                }
            }
        }

        require_once BASE_PATH . '/views/auth/login.php';
    }

    public function registro(): void {
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            try {
                if ($_POST['password'] !== $_POST['password_confirm'])
                    throw new Exception('Las contraseñas no coinciden');

                if (!isset($_POST['terminos']))
                    throw new Exception('Debe aceptar los términos y condiciones');

                $emailRegistro = trim(strtolower($_POST['email'] ?? ''));
                if (!filter_var($emailRegistro, FILTER_VALIDATE_EMAIL))
                    throw new Exception('El correo electrónico no es válido');

                $tiposPermitidos = ['CC', 'CE', 'TI', 'PA', 'RC', 'NIT', 'PEP'];
                if (!in_array($_POST['tipo_identificacion'] ?? '', $tiposPermitidos, true))
                    throw new Exception('Tipo de identificación no válido');

                if (strlen(trim($_POST['nombres'] ?? '')) > 100)
                    throw new Exception('El nombre no puede superar los 100 caracteres');
                if (strlen(trim($_POST['apellidos'] ?? '')) > 100)
                    throw new Exception('Los apellidos no pueden superar los 100 caracteres');
                if (strlen(trim($_POST['numero_identificacion'] ?? '')) > 20)
                    throw new Exception('El número de identificación no puede superar los 20 caracteres');
                if (strlen(trim($_POST['telefono'] ?? '')) > 20)
                    throw new Exception('El teléfono no puede superar los 20 caracteres');
                if (strlen(trim($_POST['whatsapp'] ?? '')) > 20)
                    throw new Exception('El WhatsApp no puede superar los 20 caracteres');
                if (strlen(trim($_POST['direccion'] ?? '')) > 255)
                    throw new Exception('La dirección no puede superar los 255 caracteres');

                if ($this->usuarioModel->emailExisteEnUsuarios($emailRegistro))
                    throw new Exception('El correo electrónico ya está registrado');

                $ciudadanoExistente = $this->usuarioModel->getByIdentificacion($_POST['numero_identificacion']);

                if ($ciudadanoExistente && !empty($ciudadanoExistente['usuario_id']))
                    throw new Exception('El número de identificación ya tiene una cuenta registrada');

                $data = [
                    'nombres'               => trim($_POST['nombres']),
                    'apellidos'             => trim($_POST['apellidos']),
                    'tipo_identificacion'   => $_POST['tipo_identificacion'],
                    'numero_identificacion' => trim($_POST['numero_identificacion']),
                    'telefono'              => trim($_POST['telefono'] ?? '')     ?: null,
                    'email'                 => $emailRegistro,
                    'password_hash'         => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'rol'                   => 'ciudadano',
                    'activo'                => true,
                    'whatsapp'              => trim($_POST['whatsapp'] ?? '')     ?: null,
                    'direccion'             => trim($_POST['direccion'] ?? '')    ?: null,
                    'proveniencia'          => $_POST['proveniencia'] ?: null,
                    'id_ciudadano_existente'=> $ciudadanoExistente['id_ciudadano'] ?? null,
                ];

                if ($this->usuarioModel->create($data)) {
                    $mailer = new Mailer();
                    $mailer->enviarConfirmacionRegistro(trim($_POST['nombres']), $emailRegistro);

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'REGISTRO_CIUDADANO',
                        "Nuevo ciudadano registrado: {$_POST['nombres']} {$_POST['apellidos']} " .
                        "({$emailRegistro}), identificación: {$_POST['numero_identificacion']}"
                    );

                    $success = '¡Registro exitoso! Ya puede iniciar sesión.';
                    header('refresh:3;url=/login');
                } else {
                    throw new Exception('Error al registrar el usuario. Intente nuevamente.');
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/views/auth/registro.php';
    }

    public function recuperar(): void {
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            try {
                $email = $_POST['email'] ?? '';
                if (empty($email))
                    throw new Exception('Por favor ingresa tu correo electrónico');

                $usuario = $this->usuarioModel->getByEmail($email);

                if ($usuario) {
                    $resetModel = new PasswordResetModel();
                    $token      = $resetModel->crearToken($usuario['id_usuario']);
                    $mailer     = new Mailer();
                    $mailer->enviarRecuperacion($usuario['nombres'], $email, $token);

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'RECUPERAR_CONTRASENA',
                        "Solicitud de recuperación de contraseña para: {$email}",
                        'usuarios',
                        (int)$usuario['id_usuario']
                    );
                }

                $success = 'Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña.';

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/views/auth/recuperar.php';
    }

    public function restablecer(): void {
        $error        = '';
        $success      = '';
        $token_valido = false;
        $token_data   = null;

        if (isset($_GET['token']) && !empty($_GET['token'])) {
            $resetModel   = new PasswordResetModel();
            $token_data   = $resetModel->verificarToken($_GET['token']);
            $token_valido = (bool)$token_data;
            if (!$token_valido) $error = 'El enlace no es válido o ha expirado.';
        } else {
            $error = 'No se proporcionó un token válido.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
            csrf_verify();
            try {
                $password = $_POST['password']         ?? '';
                $confirm  = $_POST['password_confirm'] ?? '';

                if (empty($password) || empty($confirm))        throw new Exception('Todos los campos son obligatorios');
                if ($password !== $confirm)                      throw new Exception('Las contraseñas no coinciden');
                if (strlen($password) < 8)                      throw new Exception('La contraseña debe tener al menos 8 caracteres');
                if (!preg_match('/[A-Z]/', $password))          throw new Exception('La contraseña debe contener al menos una mayúscula');
                if (!preg_match('/[0-9]/', $password))          throw new Exception('La contraseña debe contener al menos un número');
                if (!preg_match('/[^A-Za-z0-9]/', $password))  throw new Exception('La contraseña debe contener al menos un carácter especial');

                $hash = password_hash($password, PASSWORD_DEFAULT);

                if ($this->usuarioModel->updatePassword($token_data['usuario_id'], $hash)) {
                    $resetModel = new PasswordResetModel();
                    $resetModel->marcarComoUsado($_GET['token']);

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'RESTABLECER_CONTRASENA',
                        "Contraseña restablecida para usuario ID {$token_data['usuario_id']} vía web",
                        'usuarios',
                        (int)$token_data['usuario_id']
                    );

                    $success = '¡Contraseña actualizada exitosamente! Serás redirigido al inicio de sesión.';
                    header('refresh:3;url=/login');
                } else {
                    throw new Exception('Error al actualizar la contraseña. Intente nuevamente.');
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/views/auth/restablecer.php';
    }

    public function restablecerEscritorio(): void {
        $error        = '';
        $success      = '';
        $token_valido = false;
        $token_data   = null;

        if (isset($_GET['token']) && !empty($_GET['token'])) {
            $resetModel   = new PasswordResetModel();
            $token_data   = $resetModel->verificarToken($_GET['token']);
            $token_valido = (bool)$token_data;
            if (!$token_valido) $error = 'El enlace no es válido o ha expirado.';
        } else {
            $error = 'No se proporcionó un token válido.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
            csrf_verify();
            try {
                $password = $_POST['password']         ?? '';
                $confirm  = $_POST['password_confirm'] ?? '';

                if (empty($password) || empty($confirm))        throw new Exception('Todos los campos son obligatorios');
                if ($password !== $confirm)                      throw new Exception('Las contraseñas no coinciden');
                if (strlen($password) < 8)                      throw new Exception('La contraseña debe tener al menos 8 caracteres');
                if (!preg_match('/[A-Z]/', $password))          throw new Exception('La contraseña debe contener al menos una mayúscula');
                if (!preg_match('/[0-9]/', $password))          throw new Exception('La contraseña debe contener al menos un número');
                if (!preg_match('/[^A-Za-z0-9]/', $password))  throw new Exception('La contraseña debe contener al menos un carácter especial');

                $hash = password_hash($password, PASSWORD_DEFAULT);

                if ($this->usuarioModel->updatePassword($token_data['usuario_id'], $hash)) {
                    $resetModel = new PasswordResetModel();
                    $resetModel->marcarComoUsado($_GET['token']);

                    // ── Auditoría ──────────────────────────────────
                    Auditoria::registrar(
                        'RESTABLECER_CONTRASENA',
                        "Contraseña restablecida para usuario ID {$token_data['usuario_id']} vía escritorio",
                        'usuarios',
                        (int)$token_data['usuario_id']
                    );

                    $success = '¡Contraseña actualizada exitosamente!';
                    header('refresh:3;url=/login');
                } else {
                    throw new Exception('Error al actualizar la contraseña.');
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/views/auth/restablecerEcritorio.php';
    }

    public function logout(): void {
        // Si no hay sesión activa no hay nada que cerrar
        if (empty($_SESSION['usuario_id'])) {
            redirect('/login');
        }

        // ── Auditoría ──────────────────────────────────────────
        Auditoria::registrar(
            'LOGOUT',
            "El usuario {$_SESSION['usuario_nombre']} ({$_SESSION['usuario_email']}) " .
            "cerró sesión",
            'usuarios',
            (int)$_SESSION['usuario_id']
        );

        // Solo limpia el slot de ESTA pestaña; otras pestañas con otros
        // usuarios activos no se ven afectadas.
        auth_clear();
        session_regenerate_id(false);
        require_once BASE_PATH . '/views/auth/logout.php';
    }
}