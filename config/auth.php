<?php

// Claves de sesión que pertenecen a cada usuario/tab individualmente.
// El token CSRF NO está aquí porque es de sesión compartida (correcto por diseño).
const AUTH_KEYS = [
    'usuario_id', 'usuario_nombre', 'usuario_email', 'usuario_rol', 'ciudadano_id',
    'flash_mensaje', 'flash_error', 'mensaje_exito',
];

// URL base del sistema — nunca hardcodear el dominio de Railway.
function app_base_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

// Lee el tab_id del request actual.
// POST y GET vienen explícitamente del tab correcto.
// Las cookies NO se usan — son dominio-wide y causan que todas las pestañas
// compartan el mismo tab_id.
function auth_tab_id(): string {
    $raw = $_POST['tab_id'] ?? $_GET['tab_id'] ?? '';
    return preg_match('/^[a-f0-9]{6,64}$/i', $raw) ? $raw : 'default';
}

// Helper para incluir el tab_id en formularios HTML.
function tab_id_field(): string {
    return '<input type="hidden" name="tab_id" value="">';
}

// Redirige preservando el tab_id en la URL para que auth_load()
// pueda identificar al usuario correcto en el siguiente request.
function redirect(string $url): never {
    $tabId = auth_tab_id();
    if ($tabId !== 'default') {
        $sep  = str_contains($url, '?') ? '&' : '?';
        $url .= $sep . 'tab_id=' . urlencode($tabId);
    }
    header('Location: ' . $url);
    exit;
}

// Al inicio de cada request: carga los datos del tab actual en $_SESSION raíz
// para que todo el código existente siga funcionando sin cambios.
function auth_load(): void {
    $slot = $_SESSION['_tabs'][auth_tab_id()] ?? [];
    foreach (AUTH_KEYS as $key) {
        if (array_key_exists($key, $slot)) {
            $_SESSION[$key] = $slot[$key];
        } else {
            unset($_SESSION[$key]);
        }
    }
}

// Al final de cada request (via register_shutdown_function): persiste los datos
// del request de vuelta al slot del tab, sin tocar los slots de otras pestañas.
function auth_save(): void {
    $tabId = auth_tab_id();
    foreach (AUTH_KEYS as $key) {
        if (isset($_SESSION[$key])) {
            $_SESSION['_tabs'][$tabId][$key] = $_SESSION[$key];
        } else {
            unset($_SESSION['_tabs'][$tabId][$key]);
        }
    }
}

// Logout del tab actual: solo limpia el slot de esta pestaña.
// Las otras pestañas (con otros usuarios) quedan intactas.
function auth_clear(): void {
    unset($_SESSION['_tabs'][auth_tab_id()]);
    foreach (AUTH_KEYS as $key) {
        unset($_SESSION[$key]);
    }
}
