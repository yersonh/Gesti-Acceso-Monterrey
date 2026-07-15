<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['existe' => false]);
    exit;
}

$email = strtolower(trim($_GET['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['existe' => false, 'valido' => false]);
    exit;
}

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $existe = (int)$stmt->fetchColumn() > 0;

    echo json_encode(['existe' => $existe, 'valido' => true]);
} catch (Exception $e) {
    error_log('ajax/verificar_email_usuario - ' . $e->getMessage());
    echo json_encode(['existe' => false, 'valido' => true, 'error' => true]);
}
