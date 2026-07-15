<?php
// ajax/verificar_identificacion.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Rate limiting: máx 15 verificaciones por sesión (más que suficiente para registrarse)
$_SESSION['verificar_count'] = ($_SESSION['verificar_count'] ?? 0) + 1;
if ($_SESSION['verificar_count'] > 15) {
    http_response_code(429);
    echo json_encode(['existe' => false, 'error' => true, 'mensaje' => 'Demasiadas consultas. Recarga la página.']);
    exit;
}

$numero = trim($_GET['numero_identificacion'] ?? '');

// Validar que el número de identificación solo contenga dígitos (y letras para pasaportes)
if (!preg_match('/^[A-Z0-9\-]{3,20}$/i', $numero)) {
    echo json_encode(['existe' => false, 'mensaje' => '']);
    exit;
}

if (empty($numero)) {
    echo json_encode(['existe' => false, 'mensaje' => '']);
    exit;
}

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT id_ciudadano, usuario_id
        FROM   ciudadanos
        WHERE  numero_identificacion = ?
        LIMIT  1
    ");
    $stmt->execute([$numero]);
    $row = $stmt->fetch();

    if (!$row) {
        // No existe en ciudadanos — registro normal
        echo json_encode([
            'existe'  => false,
            'estado'  => 'nuevo',
            'mensaje' => '✓ Identificación disponible'
        ]);
    } elseif (empty($row['usuario_id'])) {
        // Existe en ciudadanos pero sin cuenta — se puede registrar y vincular
        echo json_encode([
            'existe'  => true,
            'estado'  => 'sin_cuenta'
        ]);
    } else {
        // Existe y ya tiene cuenta
        echo json_encode([
            'existe'  => true,
            'estado'  => 'con_cuenta',
            'mensaje' => 'Esta identificación ya tiene una cuenta registrada'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'existe'  => false,
        'error'   => true,
        'mensaje' => 'Error al verificar'
    ]);
}