<?php

declare(strict_types=1);

class AdminController
{
    // ─── Guard ────────────────────────────────────────────────────────────────

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
            redirect('/login');
        }
    }

    // ─── Usuarios ─────────────────────────────────────────────────────────────

    public function usuarios(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['usuario_id'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);

                    if (!$id) throw new InvalidArgumentException('ID inválido.');
                    if ($id === (int)$_SESSION['usuario_id']) {
                        throw new InvalidArgumentException('No puedes desactivar tu propia cuenta.');
                    }

                    $stmt = $pdo->prepare("UPDATE usuarios SET activo = :a WHERE id_usuario = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_USUARIO' : 'DESACTIVAR_USUARIO',
                        ($activo ? 'Activación' : 'Desactivación') . " de usuario id=$id",
                        'usuarios',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Usuario activado.' : 'Usuario desactivado.';

                } elseif ($accion === 'cambiar_rol') {
                    $id  = (int)($_POST['usuario_id'] ?? 0);
                    $rol = $_POST['rol'] ?? '';

                    $rolesPermitidos = ['Administrador', 'Recepcionista', 'Funcionario', 'Ciudadano'];
                    if (!$id || !in_array($rol, $rolesPermitidos, true)) {
                        throw new InvalidArgumentException('Datos inválidos.');
                    }
                    if ($id === (int)$_SESSION['usuario_id']) {
                        throw new InvalidArgumentException('No puedes cambiar tu propio rol.');
                    }

                    $stmtNombreRol = $pdo->prepare("
                        SELECT COALESCE(p.nombres||' '||p.apellidos, f.nombres||' '||f.apellidos, u.email) AS nombre
                        FROM usuarios u
                        LEFT JOIN personal p     ON p.usuario_id = u.id_usuario
                        LEFT JOIN funcionarios f ON f.usuario_id = u.id_usuario
                        WHERE u.id_usuario = :id
                    ");
                    $stmtNombreRol->execute([':id' => $id]);
                    $nombreRol = $stmtNombreRol->fetchColumn() ?: "id=$id";

                    $stmt = $pdo->prepare("UPDATE usuarios SET rol = :r WHERE id_usuario = :id");
                    $stmt->execute([':r' => $rol, ':id' => $id]);

                    Auditoria::registrar('CAMBIAR_ROL', "Rol cambiado a '$rol' para: $nombreRol", 'usuarios', $id);
                    $_SESSION['flash_mensaje'] = 'Rol actualizado correctamente.';

                } elseif ($accion === 'editar_usuario') {
                    $id    = (int)($_POST['usuario_id'] ?? 0);
                    $email = strtolower(trim($_POST['email'] ?? ''));
                    $rol   = $_POST['rol'] ?? '';

                    $rolesPermitidos = ['Administrador', 'Recepcionista', 'Funcionario', 'Ciudadano'];
                    if (!$id) throw new InvalidArgumentException('ID inválido.');
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Email inválido.');
                    if (!in_array($rol, $rolesPermitidos, true)) throw new InvalidArgumentException('Rol inválido.');
                    if ($id === (int)$_SESSION['usuario_id'] && $rol !== $_SESSION['usuario_rol']) {
                        throw new InvalidArgumentException('No puedes cambiar tu propio rol.');
                    }

                    $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :em AND id_usuario <> :id");
                    $chk->execute([':em' => $email, ':id' => $id]);
                    if ($chk->fetchColumn() > 0) throw new InvalidArgumentException('Ese email ya está en uso por otra cuenta.');

                    $pdo->prepare("UPDATE usuarios SET email = :em, rol = :r WHERE id_usuario = :id")
                        ->execute([':em' => $email, ':r' => $rol, ':id' => $id]);

                    Auditoria::registrar('EDITAR_USUARIO', "Email/rol actualizado: $email · rol $rol", 'usuarios', $id);
                    $_SESSION['flash_mensaje'] = 'Usuario actualizado correctamente.';

                } elseif ($accion === 'crear_usuario') {
                    $nombres   = trim($_POST['nombres']   ?? '');
                    $apellidos = trim($_POST['apellidos'] ?? '');
                    $tipoId    = $_POST['tipo_identificacion'] ?? '';
                    $numeroId  = trim($_POST['numero_identificacion'] ?? '');
                    $email     = strtolower(trim($_POST['email'] ?? ''));
                    $telefono  = trim($_POST['telefono'] ?? '');
                    $cargo     = trim($_POST['cargo'] ?? '');
                    $rol       = $_POST['rol'] ?? '';

                    if (!$nombres || !$apellidos) throw new InvalidArgumentException('Nombres y apellidos son obligatorios.');
                    $tiposPermitidos = ['CC','TI','CE','PA','NIT','RC'];
                    if (!in_array($tipoId, $tiposPermitidos, true)) throw new InvalidArgumentException('Tipo de identificación inválido.');
                    if (!$numeroId) throw new InvalidArgumentException('El número de identificación es obligatorio.');
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('El correo electrónico no es válido.');
                    if (!$cargo) throw new InvalidArgumentException('El cargo es obligatorio.');
                    $rolesPermitidos = ['Administrador', 'Recepcionista'];
                    if (!in_array($rol, $rolesPermitidos, true)) throw new InvalidArgumentException('Rol inválido.');

                    $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :em");
                    $chk->execute([':em' => $email]);
                    if ($chk->fetchColumn() > 0) {
                        throw new InvalidArgumentException('Este correo ya tiene una cuenta registrada en el sistema.');
                    }

                    $pdo->beginTransaction();

                    $tempPass = $this->generarPasswordTemporal();
                    $stmtU = $pdo->prepare("
                        INSERT INTO usuarios (email, password_hash, rol, activo, password_temporal, password_temporal_expires_at)
                        VALUES (:em, :ph, :rol, true, true, NOW() + INTERVAL '24 hours')
                        RETURNING id_usuario
                    ");
                    $stmtU->execute([
                        ':em'  => $email,
                        ':ph'  => password_hash($tempPass, PASSWORD_DEFAULT),
                        ':rol' => $rol,
                    ]);
                    $nuevoId = (int)$stmtU->fetchColumn();

                    $pdo->prepare("
                        INSERT INTO personal
                            (nombres, apellidos, tipo_identificacion, numero_identificacion,
                             telefono, email, cargo, activo, usuario_id)
                        VALUES (:n, :ap, :ti, :ni, :tel, :em, :cargo, true, :uid)
                    ")->execute([
                        ':n'     => $nombres,
                        ':ap'    => $apellidos,
                        ':ti'    => $tipoId,
                        ':ni'    => $numeroId,
                        ':tel'   => $telefono,
                        ':em'    => $email,
                        ':cargo' => $cargo,
                        ':uid'   => $nuevoId,
                    ]);

                    $pdo->commit();

                    require_once BASE_PATH . '/config/mail.php';
                    (new Mailer())->enviarCredencialesTemporales("$nombres $apellidos", $email, $tempPass, $rol);

                    Auditoria::registrar('CREAR_USUARIO', "Creación de usuario $nombres $apellidos ($email) con rol $rol", 'usuarios', $nuevoId);
                    $_SESSION['flash_mensaje'] = 'Usuario creado. Se enviaron las credenciales por correo.';

                } elseif ($accion === 'resetear_password') {
                    $id = (int)($_POST['usuario_id'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmt = $pdo->prepare("
                        SELECT u.id_usuario, u.email,
                               COALESCE(p.nombres || ' ' || p.apellidos,
                                        f.nombres || ' ' || f.apellidos,
                                        ci.nombres || ' ' || ci.apellidos,
                                        u.email) AS nombre_perfil
                        FROM usuarios u
                        LEFT JOIN personal p     ON p.usuario_id  = u.id_usuario
                        LEFT JOIN funcionarios f ON f.usuario_id  = u.id_usuario
                        LEFT JOIN ciudadanos ci  ON ci.usuario_id = u.id_usuario
                        WHERE u.id_usuario = :id AND u.activo = true
                    ");
                    $stmt->execute([':id' => $id]);
                    $usuarioReset = $stmt->fetch();
                    if (!$usuarioReset) throw new InvalidArgumentException('Usuario no encontrado o inactivo.');

                    require_once BASE_PATH . '/models/PasswordResetModel.php';
                    require_once BASE_PATH . '/config/mail.php';

                    $resetModel = new PasswordResetModel();
                    $token      = $resetModel->crearToken($id);
                    (new Mailer())->enviarRecuperacion($usuarioReset['nombre_perfil'], $usuarioReset['email'], $token);

                    Auditoria::registrar('RESETEAR_PASSWORD', "Contraseña reseteada por admin para: {$usuarioReset['nombre_perfil']} ({$usuarioReset['email']})", 'usuarios', $id);
                    $_SESSION['flash_mensaje'] = 'Se envió el enlace de restablecimiento al correo del usuario.';
                }

            } catch (InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                error_log('AdminController::usuarios - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/usuarios');
        }

        // GET
        try {
            $pdo = Database::getConnection();

            $usuarios = $pdo->query("
                SELECT
                    u.id_usuario,
                    u.email,
                    u.rol,
                    u.activo,
                    TO_CHAR(u.fecha_registro, 'DD/MM/YYYY')          AS fecha_registro_fmt,
                    TO_CHAR(u.last_login_at,  'DD/MM/YYYY HH24:MI')  AS last_login_fmt,
                    COALESCE(
                        (SELECT nombres || ' ' || apellidos FROM personal     WHERE usuario_id = u.id_usuario LIMIT 1),
                        (SELECT nombres || ' ' || apellidos FROM funcionarios WHERE usuario_id = u.id_usuario LIMIT 1),
                        (SELECT nombres || ' ' || apellidos FROM ciudadanos   WHERE usuario_id = u.id_usuario LIMIT 1),
                        u.email
                    ) AS nombre_perfil
                FROM usuarios u
                ORDER BY u.fecha_registro DESC NULLS LAST, u.id_usuario DESC
            ")->fetchAll();

            $kpis = $pdo->query("
                SELECT
                    COUNT(*)                                               AS total,
                    COUNT(*) FILTER (WHERE activo = true)                  AS activos,
                    COUNT(*) FILTER (WHERE activo = false)                 AS inactivos,
                    COUNT(*) FILTER (WHERE rol = 'Funcionario')            AS funcionarios,
                    COUNT(*) FILTER (WHERE rol = 'Administrador')          AS administradores,
                    COUNT(*) FILTER (WHERE rol = 'Recepcionista')          AS recepcionistas
                FROM usuarios
            ")->fetch();

        } catch (Exception $e) {
            error_log('AdminController::usuarios GET - ' . $e->getMessage());
            $usuarios = [];
            $kpis = ['total'=>0,'activos'=>0,'inactivos'=>0,'funcionarios'=>0,'administradores'=>0,'recepcionistas'=>0];
        }

        $tituloSeccion = 'Usuarios';
        $seccionActiva = 'usuarios';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/usuarios.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Personal ─────────────────────────────────────────────────────────────

    public function personal(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'crear') {
                    $campos = ['nombres','apellidos','tipo_identificacion','numero_identificacion','email','cargo'];
                    foreach ($campos as $c) {
                        if (empty(trim($_POST[$c] ?? ''))) {
                            throw new InvalidArgumentException("El campo '$c' es obligatorio.");
                        }
                    }

                    $tiposPermitidos = ['CC','TI','CE','PA','NIT','RC'];
                    if (!in_array($_POST['tipo_identificacion'], $tiposPermitidos, true)) {
                        throw new InvalidArgumentException('Tipo de identificación inválido.');
                    }

                    $emailPersonal = strtolower(trim($_POST['email']));
                    if (!filter_var($emailPersonal, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidArgumentException('El correo electrónico no es válido.');
                    }

                    $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :em");
                    $chk->execute([':em' => $emailPersonal]);
                    if ($chk->fetchColumn() > 0) {
                        throw new InvalidArgumentException('Este correo ya tiene una cuenta registrada en el sistema.');
                    }

                    $pdo->beginTransaction();

                    $tempPass = $this->generarPasswordTemporal();
                    $stmtU = $pdo->prepare("
                        INSERT INTO usuarios (email, password_hash, rol, activo, password_temporal, password_temporal_expires_at)
                        VALUES (:em, :ph, 'Recepcionista', true, true, NOW() + INTERVAL '24 hours')
                        RETURNING id_usuario
                    ");
                    $stmtU->execute([
                        ':em' => $emailPersonal,
                        ':ph' => password_hash($tempPass, PASSWORD_DEFAULT),
                    ]);
                    $usuarioId = (int)$stmtU->fetchColumn();

                    $stmt = $pdo->prepare("
                        INSERT INTO personal
                            (nombres, apellidos, tipo_identificacion, numero_identificacion,
                             telefono, email, cargo, activo, usuario_id)
                        VALUES (:n, :ap, :ti, :ni, :tel, :em, :cargo, true, :uid)
                        RETURNING id_personal
                    ");
                    $stmt->execute([
                        ':n'    => trim($_POST['nombres']),
                        ':ap'   => trim($_POST['apellidos']),
                        ':ti'   => $_POST['tipo_identificacion'],
                        ':ni'   => trim($_POST['numero_identificacion']),
                        ':tel'  => trim($_POST['telefono'] ?? ''),
                        ':em'   => $emailPersonal,
                        ':cargo'=> trim($_POST['cargo']),
                        ':uid'  => $usuarioId,
                    ]);
                    $id = (int)$stmt->fetchColumn();
                    $pdo->commit();

                    require_once BASE_PATH . '/config/mail.php';
                    (new Mailer())->enviarCredencialesTemporales(
                        trim($_POST['nombres']) . ' ' . trim($_POST['apellidos']),
                        $emailPersonal,
                        $tempPass,
                        'Recepcionista'
                    );

                    Auditoria::registrar('CREAR_PERSONAL', "Creación de personal: {$_POST['nombres']} {$_POST['apellidos']}", 'personal', $id);
                    $_SESSION['flash_mensaje'] = 'Personal registrado. Se enviaron las credenciales por correo.';

                } elseif ($accion === 'editar') {
                    $id = (int)($_POST['id_personal'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $tiposPermitidos = ['CC','TI','CE','PA','NIT','RC'];
                    if (!in_array($_POST['tipo_identificacion'] ?? '', $tiposPermitidos, true)) {
                        throw new InvalidArgumentException('Tipo de identificación inválido.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE personal SET
                            nombres               = :n,
                            apellidos             = :ap,
                            tipo_identificacion   = :ti,
                            numero_identificacion = :ni,
                            telefono              = :tel,
                            email                 = :em,
                            cargo                 = :cargo
                        WHERE id_personal = :id
                    ");
                    $stmt->execute([
                        ':n'     => trim($_POST['nombres']),
                        ':ap'    => trim($_POST['apellidos']),
                        ':ti'    => $_POST['tipo_identificacion'],
                        ':ni'    => trim($_POST['numero_identificacion']),
                        ':tel'   => trim($_POST['telefono'] ?? ''),
                        ':em'    => trim($_POST['email']),
                        ':cargo' => trim($_POST['cargo']),
                        ':id'    => $id,
                    ]);

                    Auditoria::registrar('EDITAR_PERSONAL', "Personal editado: {$_POST['nombres']} {$_POST['apellidos']}", 'personal', $id);
                    $_SESSION['flash_mensaje'] = 'Personal actualizado correctamente.';

                } elseif ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['id_personal'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmtNomP = $pdo->prepare("SELECT nombres||' '||apellidos AS nombre FROM personal WHERE id_personal = :id");
                    $stmtNomP->execute([':id' => $id]);
                    $nombreP = $stmtNomP->fetchColumn() ?: "id=$id";

                    $stmt = $pdo->prepare("UPDATE personal SET activo = :a WHERE id_personal = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_PERSONAL' : 'DESACTIVAR_PERSONAL',
                        ($activo ? 'Personal activado' : 'Personal desactivado') . ": $nombreP",
                        'personal',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Personal activado.' : 'Personal desactivado.';
                }

            } catch (InvalidArgumentException $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                error_log('AdminController::personal - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/personal');
        }

        // GET
        try {
            $pdo = Database::getConnection();
            $personal = $pdo->query("
                SELECT id_personal, nombres, apellidos, tipo_identificacion,
                       numero_identificacion, telefono, email, cargo, activo, created_at
                FROM personal
                ORDER BY apellidos, nombres
            ")->fetchAll();

            $kpis = $pdo->query("
                SELECT
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE activo = true) AS activos
                FROM personal
            ")->fetch();

        } catch (Exception $e) {
            error_log('AdminController::personal GET - ' . $e->getMessage());
            $personal = [];
            $kpis = ['total'=>0,'activos'=>0];
        }

        $tituloSeccion = 'Personal';
        $seccionActiva = 'personal';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/personal.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Funcionarios ─────────────────────────────────────────────────────────

    public function funcionarios(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'crear') {
                    $campos = ['nombres','apellidos','tipo_identificacion','numero_identificacion','email','cargo'];
                    foreach ($campos as $c) {
                        if (empty(trim($_POST[$c] ?? ''))) {
                            throw new InvalidArgumentException("El campo '$c' es obligatorio.");
                        }
                    }

                    $tiposPermitidos = ['CC','TI','CE','PA','NIT','RC'];
                    if (!in_array($_POST['tipo_identificacion'], $tiposPermitidos, true)) {
                        throw new InvalidArgumentException('Tipo de identificación inválido.');
                    }

                    $emailFunc = strtolower(trim($_POST['email']));
                    if (!filter_var($emailFunc, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidArgumentException('El correo electrónico no es válido.');
                    }

                    $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :em");
                    $chk->execute([':em' => $emailFunc]);
                    if ($chk->fetchColumn() > 0) {
                        throw new InvalidArgumentException('Este correo ya tiene una cuenta registrada en el sistema.');
                    }

                    $pdo->beginTransaction();

                    $tempPass = $this->generarPasswordTemporal();
                    $stmtU = $pdo->prepare("
                        INSERT INTO usuarios (email, password_hash, rol, activo, password_temporal, password_temporal_expires_at)
                        VALUES (:em, :ph, 'Funcionario', true, true, NOW() + INTERVAL '24 hours')
                        RETURNING id_usuario
                    ");
                    $stmtU->execute([
                        ':em' => $emailFunc,
                        ':ph' => password_hash($tempPass, PASSWORD_DEFAULT),
                    ]);
                    $usuarioId = (int)$stmtU->fetchColumn();

                    $stmt = $pdo->prepare("
                        INSERT INTO funcionarios
                            (nombres, apellidos, tipo_identificacion, numero_identificacion,
                             telefono, email, cargo, activo, usuario_id)
                        VALUES (:n, :ap, :ti, :ni, :tel, :em, :cargo, true, :uid)
                        RETURNING id_funcionario
                    ");
                    $stmt->execute([
                        ':n'    => trim($_POST['nombres']),
                        ':ap'   => trim($_POST['apellidos']),
                        ':ti'   => $_POST['tipo_identificacion'],
                        ':ni'   => trim($_POST['numero_identificacion']),
                        ':tel'  => trim($_POST['telefono'] ?? ''),
                        ':em'   => $emailFunc,
                        ':cargo'=> trim($_POST['cargo']),
                        ':uid'  => $usuarioId,
                    ]);
                    $id = (int)$stmt->fetchColumn();
                    // Insertar dependencias seleccionadas
                    $depIds = array_filter(
                        array_map('intval', (array)($_POST['dependencias'] ?? [])),
                        fn($v) => $v > 0
                    );
                    if ($depIds) {
                        $stmtDep = $pdo->prepare("
                            INSERT INTO funcionario_dependencia (funcionario_id, dependencia_id)
                            VALUES (:fid, :did)
                            ON CONFLICT DO NOTHING
                        ");
                        foreach ($depIds as $depId) {
                            $stmtDep->execute([':fid' => $id, ':did' => $depId]);
                        }
                    }

                    $pdo->commit();

                    require_once BASE_PATH . '/config/mail.php';
                    (new Mailer())->enviarCredencialesTemporales(
                        trim($_POST['nombres']) . ' ' . trim($_POST['apellidos']),
                        $emailFunc,
                        $tempPass,
                        'Funcionario'
                    );

                    Auditoria::registrar('CREAR_FUNCIONARIO', "Creación de funcionario: {$_POST['nombres']} {$_POST['apellidos']}", 'funcionarios', $id);
                    $_SESSION['flash_mensaje'] = 'Funcionario registrado. Se enviaron las credenciales por correo.';

                } elseif ($accion === 'editar') {
                    $id = (int)($_POST['id_funcionario'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $tiposPermitidos = ['CC','TI','CE','PA','NIT','RC'];
                    if (!in_array($_POST['tipo_identificacion'] ?? '', $tiposPermitidos, true)) {
                        throw new InvalidArgumentException('Tipo de identificación inválido.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE funcionarios SET
                            nombres               = :n,
                            apellidos             = :ap,
                            tipo_identificacion   = :ti,
                            numero_identificacion = :ni,
                            telefono              = :tel,
                            email                 = :em,
                            cargo                 = :cargo
                        WHERE id_funcionario = :id
                    ");
                    $stmt->execute([
                        ':n'    => trim($_POST['nombres']),
                        ':ap'   => trim($_POST['apellidos']),
                        ':ti'   => $_POST['tipo_identificacion'],
                        ':ni'   => trim($_POST['numero_identificacion']),
                        ':tel'  => trim($_POST['telefono'] ?? ''),
                        ':em'   => trim($_POST['email']),
                        ':cargo'=> trim($_POST['cargo']),
                        ':id'   => $id,
                    ]);

                    Auditoria::registrar('EDITAR_FUNCIONARIO', "Funcionario editado: {$_POST['nombres']} {$_POST['apellidos']}", 'funcionarios', $id);
                    $_SESSION['flash_mensaje'] = 'Funcionario actualizado correctamente.';

                } elseif ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['id_funcionario'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmtNomF = $pdo->prepare("SELECT nombres||' '||apellidos AS nombre FROM funcionarios WHERE id_funcionario = :id");
                    $stmtNomF->execute([':id' => $id]);
                    $nombreF = $stmtNomF->fetchColumn() ?: "id=$id";

                    $stmt = $pdo->prepare("UPDATE funcionarios SET activo = :a WHERE id_funcionario = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_FUNCIONARIO' : 'DESACTIVAR_FUNCIONARIO',
                        ($activo ? 'Funcionario activado' : 'Funcionario desactivado') . ": $nombreF",
                        'funcionarios',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Funcionario activado.' : 'Funcionario desactivado.';
                }

            } catch (InvalidArgumentException $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                error_log('AdminController::funcionarios - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/funcionarios');
        }

        // GET
        try {
            $pdo = Database::getConnection();

            $funcionarios = $pdo->query("
                SELECT
                    f.id_funcionario,
                    f.nombres,
                    f.apellidos,
                    f.tipo_identificacion,
                    f.numero_identificacion,
                    f.telefono,
                    f.email,
                    f.cargo,
                    f.activo,
                    f.created_at,
                    STRING_AGG(d.nombre, ', ' ORDER BY d.nombre) AS dependencias,
                    COALESCE(
                        JSON_AGG(d.id_dependencia ORDER BY d.nombre) FILTER (WHERE d.id_dependencia IS NOT NULL),
                        '[]'
                    )::text AS dependencia_ids
                FROM funcionarios f
                LEFT JOIN funcionario_dependencia fd ON fd.funcionario_id = f.id_funcionario
                LEFT JOIN dependencias d ON d.id_dependencia = fd.dependencia_id
                GROUP BY f.id_funcionario, f.nombres, f.apellidos, f.tipo_identificacion,
                         f.numero_identificacion, f.telefono, f.email, f.cargo, f.activo, f.created_at
                ORDER BY f.apellidos, f.nombres
            ")->fetchAll();

            $kpis = $pdo->query("
                SELECT
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE f.activo = true) AS activos,
                    COUNT(DISTINCT fd.funcionario_id) AS con_dependencias
                FROM funcionarios f
                LEFT JOIN funcionario_dependencia fd ON fd.funcionario_id = f.id_funcionario
            ")->fetch();

            $dependenciasDisponibles = $pdo->query("
                SELECT id_dependencia, nombre FROM dependencias WHERE activo = true ORDER BY nombre
            ")->fetchAll();

        } catch (Exception $e) {
            error_log('AdminController::funcionarios GET - ' . $e->getMessage());
            $funcionarios = [];
            $kpis = ['total'=>0,'activos'=>0,'con_dependencias'=>0];
            $dependenciasDisponibles = [];
        }

        $tituloSeccion = 'Funcionarios';
        $seccionActiva = 'funcionarios';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/funcionarios.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Dependencias ─────────────────────────────────────────────────────────

    public function dependencias(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'crear') {
                    $nombre = trim($_POST['nombre'] ?? '');
                    if ($nombre === '') throw new InvalidArgumentException('El nombre es obligatorio.');

                    $stmt = $pdo->prepare("
                        INSERT INTO dependencias (nombre, descripcion, activo)
                        VALUES (:n, :d, :a)
                        RETURNING id_dependencia
                    ");
                    $stmt->execute([
                        ':n' => $nombre,
                        ':d' => trim($_POST['descripcion'] ?? ''),
                        ':a' => isset($_POST['activo']) ? 'true' : 'true',
                    ]);
                    $id = (int)$stmt->fetchColumn();

                    Auditoria::registrar('CREAR_DEPENDENCIA', "Creación de dependencia: $nombre", 'dependencias', $id);
                    $_SESSION['flash_mensaje'] = 'Dependencia creada correctamente.';

                } elseif ($accion === 'editar') {
                    $id     = (int)($_POST['id_dependencia'] ?? 0);
                    $nombre = trim($_POST['nombre'] ?? '');
                    if (!$id || $nombre === '') throw new InvalidArgumentException('Datos inválidos.');

                    $stmt = $pdo->prepare("
                        UPDATE dependencias
                        SET nombre = :n, descripcion = :d
                        WHERE id_dependencia = :id
                    ");
                    $stmt->execute([
                        ':n'  => $nombre,
                        ':d'  => trim($_POST['descripcion'] ?? ''),
                        ':id' => $id,
                    ]);

                    Auditoria::registrar('EDITAR_DEPENDENCIA', "Dependencia editada: $nombre", 'dependencias', $id);
                    $_SESSION['flash_mensaje'] = 'Dependencia actualizada correctamente.';

                } elseif ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['id_dependencia'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmtNomD = $pdo->prepare("SELECT nombre FROM dependencias WHERE id_dependencia = :id");
                    $stmtNomD->execute([':id' => $id]);
                    $nombreD = $stmtNomD->fetchColumn() ?: "id=$id";

                    $stmt = $pdo->prepare("UPDATE dependencias SET activo = :a WHERE id_dependencia = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_DEPENDENCIA' : 'DESACTIVAR_DEPENDENCIA',
                        ($activo ? 'Dependencia activada' : 'Dependencia desactivada') . ": $nombreD",
                        'dependencias',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Dependencia activada.' : 'Dependencia desactivada.';
                }

            } catch (InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                error_log('AdminController::dependencias - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/dependencias');
        }

        // GET
        try {
            $pdo = Database::getConnection();
            $dependencias = $pdo->query("
                SELECT
                    d.id_dependencia,
                    d.nombre,
                    d.descripcion,
                    d.activo,
                    d.created_at,
                    COUNT(fd.funcionario_id) AS num_funcionarios
                FROM dependencias d
                LEFT JOIN funcionario_dependencia fd ON fd.dependencia_id = d.id_dependencia
                GROUP BY d.id_dependencia, d.nombre, d.descripcion, d.activo, d.created_at
                ORDER BY d.nombre
            ")->fetchAll();

        } catch (Exception $e) {
            error_log('AdminController::dependencias GET - ' . $e->getMessage());
            $dependencias = [];
        }

        $tituloSeccion = 'Dependencias';
        $seccionActiva = 'dependencias';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/dependencias.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Funcionario-Dependencia ──────────────────────────────────────────────

    public function funcDependencia(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'asignar') {
                    $funcId = (int)($_POST['funcionario_id'] ?? 0);
                    $depId  = (int)($_POST['dependencia_id'] ?? 0);
                    if (!$funcId || !$depId) throw new InvalidArgumentException('Debe seleccionar funcionario y dependencia.');

                    // Verificar duplicado
                    $check = $pdo->prepare("
                        SELECT COUNT(*) FROM funcionario_dependencia
                        WHERE funcionario_id = :f AND dependencia_id = :d
                    ");
                    $check->execute([':f' => $funcId, ':d' => $depId]);
                    if ($check->fetchColumn() > 0) {
                        throw new InvalidArgumentException('Esta asignación ya existe.');
                    }

                    $stmtNomAs = $pdo->prepare("
                        SELECT f.nombres||' '||f.apellidos AS func_nom, d.nombre AS dep_nom
                        FROM funcionarios f, dependencias d
                        WHERE f.id_funcionario = :f AND d.id_dependencia = :d
                    ");
                    $stmtNomAs->execute([':f' => $funcId, ':d' => $depId]);
                    $nomAs = $stmtNomAs->fetch();

                    $stmt = $pdo->prepare("
                        INSERT INTO funcionario_dependencia (funcionario_id, dependencia_id)
                        VALUES (:f, :d)
                        RETURNING id
                    ");
                    $stmt->execute([':f' => $funcId, ':d' => $depId]);
                    $id = (int)$stmt->fetchColumn();

                    $descAs = $nomAs
                        ? "{$nomAs['func_nom']} asignado a {$nomAs['dep_nom']}"
                        : "Funcionario=$funcId asignado a Dependencia=$depId";
                    Auditoria::registrar('ASIGNAR_DEPENDENCIA', $descAs, 'funcionario_dependencia', $id);
                    $_SESSION['flash_mensaje'] = 'Asignación realizada correctamente.';

                } elseif ($accion === 'desasignar') {
                    $id = (int)($_POST['asignacion_id'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmtNomDes = $pdo->prepare("
                        SELECT f.nombres||' '||f.apellidos AS func_nom, d.nombre AS dep_nom
                        FROM funcionario_dependencia fd
                        JOIN funcionarios f   ON f.id_funcionario = fd.funcionario_id
                        JOIN dependencias d   ON d.id_dependencia = fd.dependencia_id
                        WHERE fd.id = :id
                    ");
                    $stmtNomDes->execute([':id' => $id]);
                    $nomDes = $stmtNomDes->fetch();

                    $stmt = $pdo->prepare("DELETE FROM funcionario_dependencia WHERE id = :id");
                    $stmt->execute([':id' => $id]);

                    $descDes = $nomDes
                        ? "{$nomDes['func_nom']} retirado de {$nomDes['dep_nom']}"
                        : "Asignación id=$id eliminada";
                    Auditoria::registrar('DESASIGNAR_DEPENDENCIA', $descDes, 'funcionario_dependencia', $id);
                    $_SESSION['flash_mensaje'] = 'Asignación eliminada correctamente.';
                }

            } catch (InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                error_log('AdminController::funcDependencia - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/func-dependencia');
        }

        // GET
        try {
            $pdo = Database::getConnection();

            $asignaciones = $pdo->query("
                SELECT
                    fd.id,
                    fd.created_at,
                    f.id_funcionario,
                    f.nombres    AS func_nombres,
                    f.apellidos  AS func_apellidos,
                    f.cargo      AS func_cargo,
                    d.id_dependencia,
                    d.nombre     AS dep_nombre
                FROM funcionario_dependencia fd
                JOIN funcionarios  f ON f.id_funcionario   = fd.funcionario_id
                JOIN dependencias  d ON d.id_dependencia   = fd.dependencia_id
                ORDER BY f.apellidos, f.nombres, d.nombre
            ")->fetchAll();

            $funcionarios = $pdo->query("
                SELECT id_funcionario, nombres, apellidos, cargo
                FROM funcionarios
                WHERE activo = true
                ORDER BY apellidos, nombres
            ")->fetchAll();

            $dependencias = $pdo->query("
                SELECT id_dependencia, nombre
                FROM dependencias
                WHERE activo = true
                ORDER BY nombre
            ")->fetchAll();

        } catch (Exception $e) {
            error_log('AdminController::funcDependencia GET - ' . $e->getMessage());
            $asignaciones = [];
            $funcionarios = [];
            $dependencias = [];
        }

        $tituloSeccion = 'Funcionarios por Dependencia';
        $seccionActiva = 'func-dependencia';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/func_dependencia.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Horarios ─────────────────────────────────────────────────────────────

    public function horarios(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'guardar') {
                try {
                    $pdo = Database::getConnection();

                    $intervalo = (int)($_POST['intervalo_min'] ?? 30);
                    if ($intervalo < 5 || $intervalo > 120) {
                        throw new InvalidArgumentException('El intervalo debe estar entre 5 y 120 minutos.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE configuracion_sistema SET
                            manana_inicio = :mi,
                            manana_fin    = :mf,
                            tarde_inicio  = :ti,
                            tarde_fin     = :tf,
                            intervalo_min = :iv,
                            lunes         = :lu,
                            martes        = :ma,
                            miercoles     = :mi2,
                            jueves        = :ju,
                            viernes       = :vi,
                            sabado        = :sa,
                            domingo       = :do,
                            updated_at    = NOW()
                        WHERE id = 1
                    ");
                    $stmt->execute([
                        ':mi'  => $_POST['manana_inicio'],
                        ':mf'  => $_POST['manana_fin'],
                        ':ti'  => $_POST['tarde_inicio'],
                        ':tf'  => $_POST['tarde_fin'],
                        ':iv'  => $intervalo,
                        ':lu'  => isset($_POST['lunes'])     ? 'true' : 'false',
                        ':ma'  => isset($_POST['martes'])    ? 'true' : 'false',
                        ':mi2' => isset($_POST['miercoles']) ? 'true' : 'false',
                        ':ju'  => isset($_POST['jueves'])    ? 'true' : 'false',
                        ':vi'  => isset($_POST['viernes'])   ? 'true' : 'false',
                        ':sa'  => isset($_POST['sabado'])    ? 'true' : 'false',
                        ':do'  => isset($_POST['domingo'])   ? 'true' : 'false',
                    ]);

                    Auditoria::registrar('EDITAR_HORARIOS', 'Actualización de configuración de horarios', 'configuracion_sistema', 1);
                    $_SESSION['flash_mensaje'] = 'Horarios actualizados correctamente.';

                } catch (InvalidArgumentException $e) {
                    $_SESSION['flash_error'] = $e->getMessage();
                } catch (Exception $e) {
                    error_log('AdminController::horarios POST - ' . $e->getMessage());
                    $_SESSION['flash_error'] = 'Error interno del servidor.';
                }
            }

            redirect('/admin/horarios');
        }

        // GET
        try {
            $pdo = Database::getConnection();
            $config = $pdo->query("SELECT * FROM configuracion_sistema LIMIT 1")->fetch();
        } catch (Exception $e) {
            error_log('AdminController::horarios GET - ' . $e->getMessage());
            $config = null;
        }

        $tituloSeccion = 'Horarios de Atención';
        $seccionActiva = 'horarios';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/horarios.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Festivos ─────────────────────────────────────────────────────────────

    public function festivos(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'crear') {
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $fecha       = trim($_POST['fecha'] ?? '');
                    if ($descripcion === '' || $fecha === '') {
                        throw new InvalidArgumentException('Descripción y fecha son obligatorias.');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO dias_festivos (fecha, descripcion, recurrente, activo)
                        VALUES (:f, :d, :r, true)
                        RETURNING id
                    ");
                    $stmt->execute([
                        ':f' => $fecha,
                        ':d' => $descripcion,
                        ':r' => isset($_POST['recurrente']) ? 'true' : 'false',
                    ]);
                    $id = (int)$stmt->fetchColumn();

                    Auditoria::registrar('CREAR_FESTIVO', "Creación de día festivo: $descripcion ($fecha)", 'dias_festivos', $id);
                    $_SESSION['flash_mensaje'] = 'Día festivo agregado correctamente.';

                } elseif ($accion === 'editar') {
                    $id          = (int)($_POST['id_festivo'] ?? 0);
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $fecha       = trim($_POST['fecha'] ?? '');
                    if (!$id) throw new InvalidArgumentException('ID inválido.');
                    if ($descripcion === '' || $fecha === '') {
                        throw new InvalidArgumentException('Descripción y fecha son obligatorias.');
                    }

                    $pdo->prepare("
                        UPDATE dias_festivos
                        SET descripcion = :d, fecha = :f, recurrente = :r
                        WHERE id = :id
                    ")->execute([
                        ':d'  => $descripcion,
                        ':f'  => $fecha,
                        ':r'  => isset($_POST['recurrente']) ? 'true' : 'false',
                        ':id' => $id,
                    ]);

                    Auditoria::registrar('EDITAR_FESTIVO', "Edición de festivo id=$id: $descripcion ($fecha)", 'dias_festivos', $id);
                    $_SESSION['flash_mensaje'] = 'Día festivo actualizado correctamente.';

                } elseif ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['id_festivo'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmt = $pdo->prepare("UPDATE dias_festivos SET activo = :a WHERE id = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_FESTIVO' : 'DESACTIVAR_FESTIVO',
                        ($activo ? 'Activación' : 'Desactivación') . " de festivo id=$id",
                        'dias_festivos',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Festivo activado.' : 'Festivo desactivado.';
                }

            } catch (InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                error_log('AdminController::festivos - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/festivos');
        }

        // GET
        try {
            $pdo = Database::getConnection();
            $festivos = $pdo->query("
                SELECT id, fecha, descripcion, recurrente, activo, created_at
                FROM dias_festivos
                ORDER BY fecha ASC
            ")->fetchAll();

        } catch (Exception $e) {
            error_log('AdminController::festivos GET - ' . $e->getMessage());
            $festivos = [];
        }

        $tituloSeccion = 'Días Festivos';
        $seccionActiva = 'festivos';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/festivos.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Ciudadanos ───────────────────────────────────────────────────────────

    public function ciudadanos(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                $pdo = Database::getConnection();

                if ($accion === 'toggle_activo') {
                    $id     = (int)($_POST['id_ciudadano'] ?? 0);
                    $activo = (bool)($_POST['activo'] ?? 0);
                    if (!$id) throw new InvalidArgumentException('ID inválido.');

                    $stmt = $pdo->prepare("UPDATE ciudadanos SET activo = :a WHERE id_ciudadano = :id");
                    $stmt->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

                    Auditoria::registrar(
                        $activo ? 'ACTIVAR_CIUDADANO' : 'DESACTIVAR_CIUDADANO',
                        ($activo ? 'Activación' : 'Desactivación') . " de ciudadano id=$id",
                        'ciudadanos',
                        $id
                    );
                    $_SESSION['flash_mensaje'] = $activo ? 'Ciudadano activado.' : 'Ciudadano desactivado.';
                }

            } catch (InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            } catch (Exception $e) {
                error_log('AdminController::ciudadanos - ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error interno del servidor.';
            }

            redirect('/admin/ciudadanos');
        }

        // GET
        try {
            $pdo = Database::getConnection();

            $ciudadanos = $pdo->query("
                SELECT
                    ci.id_ciudadano,
                    ci.nombres,
                    ci.apellidos,
                    ci.tipo_identificacion,
                    ci.numero_identificacion,
                    ci.telefono,
                    ci.email,
                    ci.proveniencia,
                    ci.activo,
                    ci.created_at,
                    u.email       AS cuenta_email,
                    COUNT(c.id_cita) AS total_citas
                FROM ciudadanos ci
                LEFT JOIN usuarios u ON u.id_usuario = ci.usuario_id
                LEFT JOIN citas c    ON c.ciudadano_id = ci.id_ciudadano
                GROUP BY ci.id_ciudadano, ci.nombres, ci.apellidos, ci.tipo_identificacion,
                         ci.numero_identificacion, ci.telefono, ci.email, ci.proveniencia,
                         ci.activo, ci.created_at, u.email
                ORDER BY ci.apellidos, ci.nombres
            ")->fetchAll();

            $kpis = $pdo->query("
                SELECT
                    COUNT(*)                                            AS total,
                    COUNT(*) FILTER (WHERE activo = true)               AS activos,
                    COUNT(*) FILTER (WHERE usuario_id IS NOT NULL)      AS con_cuenta
                FROM ciudadanos
            ")->fetch();

        } catch (Exception $e) {
            error_log('AdminController::ciudadanos GET - ' . $e->getMessage());
            $ciudadanos = [];
            $kpis = ['total' => 0, 'activos' => 0, 'con_cuenta' => 0];
        }

        $tituloSeccion = 'Ciudadanos';
        $seccionActiva = 'ciudadanos';
        require_once BASE_PATH . '/views/admin/_header.php';
        require_once BASE_PATH . '/views/admin/ciudadanos.php';
        require_once BASE_PATH . '/views/admin/_footer.php';
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function generarPasswordTemporal(): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $digits  = '23456789';
        $special = '!@#$%&*';

        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $all = $upper . $lower . $digits . $special;
        for ($i = 0; $i < 6; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }
}
