<?php
// ajax/verificar_identificacion.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ClienteCore.php';

header('Content-Type: application/json');

// Rate limiting: máx 15 verificaciones por sesión (más que suficiente para registrarse)
$_SESSION['verificar_count'] = ($_SESSION['verificar_count'] ?? 0) + 1;
if ($_SESSION['verificar_count'] > 15) {
    http_response_code(429);
    echo json_encode(['existe' => false, 'error' => true, 'mensaje' => 'Demasiadas consultas. Recarga la página.']);
    exit;
}

$numero = trim($_GET['numero_identificacion'] ?? '');
$tipo   = trim($_GET['tipo_identificacion'] ?? '');

// Validar que el número de identificación solo contenga dígitos (y letras para pasaportes)
if (!preg_match('/^[A-Z0-9\-]{3,20}$/i', $numero)) {
    echo json_encode(['existe' => false, 'mensaje' => '']);
    exit;
}

$tiposPermitidos = ['CC', 'CE', 'TI', 'PA', 'RC', 'NIT', 'PEP'];
if (!in_array($tipo, $tiposPermitidos, true)) {
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
        FROM   ciudadanos_cache
        WHERE  numero_identificacion = ?
        LIMIT  1
    ");
    $stmt->execute([$numero]);
    $row = $stmt->fetch();

    // No está en la cache local — puede existir en Core sin sincronizar todavía.
    // Se consulta Core solo en este caso (no en cada tecleo) para no sobrecargarlo.
    if (!$row) {
        try {
            $core = new ClienteCore();
            $ciudadanoCore = $core->buscarCiudadanoPorIdentificacion($tipo, $numero);
        } catch (Exception $eCore) {
            // Core caído/timeout: no bloquear el registro por esto, se resolverá
            // en el momento del envío real (UsuarioModel::create ya maneja ese caso).
            error_log('verificar_identificacion.php - Core no disponible: ' . $eCore->getMessage());
            $ciudadanoCore = null;
        }

        if ($ciudadanoCore) {
            // Existe en Core aunque no esté sincronizado localmente todavía
            echo json_encode([
                'existe'  => true,
                'estado'  => 'sin_cuenta'
            ]);
            exit;
        }
    }

    if (!$row) {
        // No existe ni en cache ni en Core — registro normal
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