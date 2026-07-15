<?php

declare(strict_types=1);

class SuperAdminController
{
    private SuperAdminModel $model;

    public function __construct()
    {
        $this->model = new SuperAdminModel();
    }

    // ─── Guard ────────────────────────────────────────────────────────────────

    private function requireSuperAdmin(): void
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Superadmin') {
            redirect('/login');
        }
    }

    private function requireSuperAdminAjax(): void
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Superadmin') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Acceso denegado.']);
            exit;
        }
    }

    // ─── Vista principal ──────────────────────────────────────────────────────

    public function usuarios(): void
    {
        $this->requireSuperAdmin();
        require_once __DIR__ . '/../views/superadmin/usuarios.php';
    }

    // ─── API Ajax ─────────────────────────────────────────────────────────────

    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->requireSuperAdminAjax();

        $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
        }

        try {
            $resultado = match($accion) {
                'listar'          => $this->listar(),
                'crear'           => $this->crear(),
                'toggle_activo'   => $this->toggleActivo(),
                'reset_password'  => $this->resetPassword(),
                default           => throw new InvalidArgumentException("Acción no reconocida."),
            };
            echo json_encode(['ok' => true, 'data' => $resultado], JSON_UNESCAPED_UNICODE);

        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            error_log('SuperAdminController::api — ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor.']);
        }
    }

    // ─── Acciones ─────────────────────────────────────────────────────────────

    private function listar(): array
    {
        return $this->model->listarUsuarios();
    }

    private function crear(): array
    {
        $campos = ['nombres','apellidos','tipo_identificacion',
                'numero_identificacion','email','password','rol'];

        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                throw new InvalidArgumentException("El campo '$campo' es obligatorio.");
            }
        }

        $rol   = $_POST['rol'];
        $email = trim(strtolower($_POST['email']));

        if (!in_array($rol, ['Superadmin', 'Administrador'])) {
            throw new InvalidArgumentException('Rol no válido.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El email no es válido.');
        }

        if (strlen($_POST['password']) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        $tiposPermitidos = ['CC', 'CE', 'TI', 'PA', 'RC', 'NIT'];
        if (!in_array($_POST['tipo_identificacion'] ?? '', $tiposPermitidos, true)) {
            throw new InvalidArgumentException('Tipo de identificación no válido.');
        }

        if (strlen(trim($_POST['nombres'])) > 100) {
            throw new InvalidArgumentException('El nombre no puede superar los 100 caracteres.');
        }
        if (strlen(trim($_POST['apellidos'])) > 100) {
            throw new InvalidArgumentException('Los apellidos no pueden superar los 100 caracteres.');
        }

        if ($this->model->emailExiste($email)) {
            throw new InvalidArgumentException('Ya existe un usuario con ese email.');
        }

        if ($this->model->identificacionExiste(trim($_POST['numero_identificacion']))) {
            throw new InvalidArgumentException('Ya existe un usuario con esa identificación.');
        }

        $usuarioId = $this->model->crearUsuario([
            'nombres'               => trim($_POST['nombres']),
            'apellidos'             => trim($_POST['apellidos']),
            'tipo_identificacion'   => $_POST['tipo_identificacion'],
            'numero_identificacion' => trim($_POST['numero_identificacion']),
            'telefono'              => trim($_POST['telefono'] ?? ''),
            'email'                 => $email,
            'cargo'                 => trim($_POST['cargo'] ?? ''),
            'password'              => $_POST['password'],
            'rol'                   => $rol,
        ]);

        return ['usuario_id' => $usuarioId, 'mensaje' => "$rol creado exitosamente."];
    }

    private function toggleActivo(): array
    {
        $id     = (int)($_POST['usuario_id'] ?? 0);
        $activo = filter_var($_POST['activo'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // No puede desactivarse a sí mismo
       if ($id === (int)$_SESSION['usuario_id']) {
            throw new InvalidArgumentException('No puedes desactivar tu propia cuenta.');
        }

        if (!$id) throw new InvalidArgumentException('ID de usuario inválido.');

        $this->model->toggleActivo($id, $activo);
        return ['mensaje' => $activo ? 'Usuario activado.' : 'Usuario desactivado.'];
    }

    private function resetPassword(): array
    {
        $id       = (int)($_POST['usuario_id'] ?? 0);
        $password = $_POST['nueva_password'] ?? '';

        if (!$id) throw new InvalidArgumentException('ID de usuario inválido.');
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        $this->model->resetPassword($id, $password);
        return ['mensaje' => 'Contraseña actualizada exitosamente.'];
    }
}