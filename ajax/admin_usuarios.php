<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

csrf_verify();

$accion = $_POST['accion'] ?? '';

try {
    $pdo = Database::getConnection();

    if ($accion === 'toggle_activo') {
        $id     = (int)($_POST['usuario_id'] ?? 0);
        $activo = filter_var($_POST['activo'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$id) throw new InvalidArgumentException('ID inválido.');
        if ($id === (int)$_SESSION['usuario_id']) {
            throw new InvalidArgumentException('No puedes desactivar tu propia cuenta.');
        }

        $pdo->prepare("UPDATE usuarios SET activo = :a WHERE id_usuario = :id")
            ->execute([':a' => $activo ? 'true' : 'false', ':id' => $id]);

        Auditoria::registrar(
            $activo ? 'ACTIVAR_USUARIO' : 'DESACTIVAR_USUARIO',
            ($activo ? 'Activación' : 'Desactivación') . " de usuario id=$id",
            'usuarios',
            $id
        );

        echo json_encode([
            'ok'      => true,
            'activo'  => $activo,
            'mensaje' => $activo ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);

    } else {
        throw new InvalidArgumentException('Acción no reconocida.');
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('ajax/admin_usuarios - ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor.']);
}
