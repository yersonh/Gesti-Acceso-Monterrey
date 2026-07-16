<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RecepcionModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Recepcionista') {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $termino = trim($_GET['q'] ?? '');

    $registros = $termino !== ''
        ? RecepcionModel::buscarRegistros($termino)
        : RecepcionModel::obtenerRegistrosDia();

    $alertas = RecepcionModel::obtenerAlertasDia(15);

    $stats = [
        'total'        => count($registros),
        'en_curso'     => 0,
        'finalizadas'  => 0,
        'espontaneas'  => 0,
    ];
    foreach ($registros as $r) {
        if ($r['estado'] === 'en_curso')   $stats['en_curso']++;
        if ($r['estado'] === 'finalizada') $stats['finalizadas']++;
        if ($r['tipo_registro'] === 'espontanea') $stats['espontaneas']++;
    }

    echo json_encode([
        'ok'        => true,
        'registros' => $registros,
        'alertas'   => $alertas,
        'stats'     => $stats,
    ]);

} catch (Exception $e) {
    error_log('recepcion_poll.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al actualizar.']);
}
