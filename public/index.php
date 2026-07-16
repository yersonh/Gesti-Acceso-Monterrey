<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // php -S sirve el archivo directamente
}

// ── Cabeceras de seguridad HTTP ───────────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

// ── Rutas base ────────────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  '/');

// ── Base de datos primero: el session handler la necesita ─────────────────────
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/session_handler.php'; // registra sesiones en PostgreSQL
session_start();                                         // ahora las sesiones sobreviven reinicios

// Elimina la cookie tab_id legada para que no interfiera con el sistema
// de identificación por URL/POST que usamos ahora.
if (isset($_COOKIE['tab_id'])) {
    setcookie('tab_id', '', 1, '/', '', !empty($_SERVER['HTTPS']), true);
    unset($_COOKIE['tab_id']);
}

// ── Helpers de seguridad ──────────────────────────────────────────────────────
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/config/auth.php';
require_once BASE_PATH . '/config/rate_limiter.php';
auth_load();                             // carga datos del tab actual en $_SESSION
register_shutdown_function('auth_save'); // persiste al final del request
require_once BASE_PATH . '/models/UsuarioModel.php';
require_once BASE_PATH . '/models/Cita.php';
require_once BASE_PATH . '/models/Dependencia.php';
require_once BASE_PATH . '/models/Funcionario.php';
require_once BASE_PATH . '/models/Auditoria.php';
require_once BASE_PATH . '/models/ReporteModel.php';
require_once BASE_PATH . '/models/PasswordResetModel.php';
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/CitaController.php';
require_once BASE_PATH . '/controllers/FuncionarioController.php';
require_once BASE_PATH . '/models/ValoracionModel.php';
require_once BASE_PATH . '/controllers/ValoracionController.php';
require_once BASE_PATH . '/controllers/ReportesController.php';
require_once BASE_PATH . '/models/SuperAdminModel.php';
require_once BASE_PATH . '/controllers/SuperAdminController.php';
require_once BASE_PATH . '/controllers/AdminController.php';
require_once BASE_PATH . '/controllers/PerfilController.php';
// ── Obtener la ruta solicitada ────────────────────────────────────────────────
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// ── Enrutador ─────────────────────────────────────────────────────────────────
switch ($uri) {

    // Auth
    case '':
    case 'login':
        (new AuthController())->login();
        break;

    case 'registro':
        (new AuthController())->registro();
        break;

    case 'recuperar':
        (new AuthController())->recuperar();
        break;

    case 'restablecer':
        (new AuthController())->restablecer();
        break;

    case 'restablecerEscritorio':
        (new AuthController())->restablecerEscritorio();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    // Ciudadano
    case 'dashboard':
        (new CitaController())->dashboard();
        break;

    case 'agendar':
        (new CitaController())->agendar();
        break;

    // Funcionario
    case 'funcionario/dashboard':
        (new FuncionarioController())->dashboard();
        break;

    // Recepción
    case 'recepcion':
        require_once BASE_PATH . '/controllers/RecepcionController.php';
        (new RecepcionController())->dashboard();
        break;

    case 'recepcion/verificar':
        require_once BASE_PATH . '/controllers/RecepcionController.php';
        (new RecepcionController())->verificarCiudadano();
        break;

    // Ajax
    case 'ajax/get_citas':
        require_once BASE_PATH . '/ajax/get_citas.php';
        break;

    case 'ajax/horarios_disponibles':
        require_once BASE_PATH . '/ajax/horarios_disponibles.php';
        break;

    case 'ajax/verificar_identificacion':
        require_once BASE_PATH . '/ajax/verificar_identificacion.php';
        break;

    case 'ajax/verificar_email_usuario':
        require_once BASE_PATH . '/ajax/verificar_email_usuario.php';
        break;
        
    case 'valorar':
        (new ValoracionController())->formulario();
        break;
    case 'cita/responder':
        (new CitaController())->responderReprogramacion();
        break;
    case 'admin/dashboard':
    case 'admin/usuarios':
        (new AdminController())->usuarios(); break;

    case 'admin/personal':
        (new AdminController())->personal(); break;

    case 'admin/funcionarios':
        (new AdminController())->funcionarios(); break;

    case 'admin/ciudadanos':
        (new AdminController())->ciudadanos(); break;

    case 'admin/dependencias':
        (new AdminController())->dependencias(); break;

    case 'admin/func-dependencia':
        (new AdminController())->funcDependencia(); break;

    case 'admin/horarios':
        (new AdminController())->horarios(); break;

    case 'admin/festivos':
        (new AdminController())->festivos(); break;

    case 'cambiar-contrasena':
        (new PerfilController())->cambiarContrasena(); break;

    case 'superadmin/reportes':
        (new ReportesController())->dashboard();
        break;

    case 'ajax/reportes':
        (new ReportesController())->api();
        break;
    case 'superadmin/usuarios':
        (new SuperAdminController())->usuarios();
        break;

    case 'ajax/admin_usuarios':
        require_once BASE_PATH . '/ajax/admin_usuarios.php';
        break;
    case 'ajax/superadmin_usuarios':
        require_once BASE_PATH . '/ajax/superadmin_usuarios.php';
        break;
    case 'ajax/check_nuevas_citas':
        require_once BASE_PATH . '/ajax/check_nuevas_citas.php';
        break;

    case 'ajax/recepcion_poll':
        require_once BASE_PATH . '/ajax/recepcion_poll.php';
        break;
        // Donde tengas las rutas del superadmin
    case 'reportes/excel':
        (new ReportesController())->exportarExcel();
        break;
    // 404
    default:
        http_response_code(404); 
        echo '<h1>404 — Página no encontrada</h1>';
        break;
}