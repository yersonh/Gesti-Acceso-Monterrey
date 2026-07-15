<?php

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): void {
    $tokenPost   = $_POST['csrf_token']                ?? '';
    $tokenHeader = $_SERVER['HTTP_X_CSRF_TOKEN']       ?? '';
    $token       = $tokenPost !== '' ? $tokenPost : $tokenHeader;

    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Solicitud no válida. Por favor recarga la página e intenta nuevamente.');
    }
}
