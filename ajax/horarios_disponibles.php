<?php
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Funcionario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Funcionario') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$funcionarioId = (int)($_GET['funcionario_id'] ?? 0);
$fecha         = trim($_GET['fecha'] ?? '');
$excluirCita   = (int)($_GET['excluir_cita'] ?? 0);

if (!$funcionarioId || !$fecha) {
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

// Validar formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !strtotime($fecha)) {
    http_response_code(400);
    echo json_encode(['error' => 'Fecha inválida']);
    exit;
}

// IDOR: verificar que el funcionario autenticado puede consultar el horario solicitado
try {
    $pdo = Database::getConnection();

    // Obtener el id_funcionario del usuario logueado
    $stmtMio = $pdo->prepare("SELECT id_funcionario FROM funcionarios WHERE usuario_id = ?");
    $stmtMio->execute([$_SESSION['usuario_id']]);
    $miFuncionario = (int)$stmtMio->fetchColumn();

    if (!$miFuncionario) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    // Si consulta su propio horario (reprogramación propia), siempre permitido
    if ($miFuncionario !== $funcionarioId) {
        // Para otro funcionario: verificar misma dependencia via tabla junction
        $stmtIdr = $pdo->prepare("
            SELECT 1
            FROM funcionario_dependencia fd1
            JOIN funcionario_dependencia fd2 ON fd2.dependencia_id = fd1.dependencia_id
            WHERE fd1.funcionario_id = ? AND fd2.funcionario_id = ?
            LIMIT 1
        ");
        $stmtIdr->execute([$miFuncionario, $funcionarioId]);
        if (!$stmtIdr->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }
} catch (Exception $e) {
    error_log('horarios_disponibles - IDOR check: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Leer configuración desde BD
    $config = $pdo->query("SELECT * FROM configuracion_sistema LIMIT 1")->fetch();
    if (!$config) {
        // Fallback si no hay config
        $config = [
            'manana_inicio' => '07:00',
            'manana_fin'    => '12:00',
            'tarde_inicio'  => '14:00',
            'tarde_fin'     => '17:00',
            'intervalo_min' => 15,
            'lunes'         => true,
            'martes'        => true,
            'miercoles'     => true,
            'jueves'        => true,
            'viernes'       => true,
            'sabado'        => false,
            'domingo'       => false,
        ];
    }

    // Verificar si es día laborable según configuración
    $mapaDias = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ];
    $numeroDia = (int)date('N', strtotime($fecha));
    $campoDia  = $mapaDias[$numeroDia];

    if (!$config[$campoDia]) {
        echo json_encode(['error' => 'No se atiende este día.']);
        exit;
    }
    $hoy = date('Y-m-d', strtotime('now', strtotime('America/Bogota')));
    if ($fecha <= $hoy) {
        echo json_encode(['error' => 'No es posible agendar citas para el día de hoy. Por favor selecciona una fecha a partir de mañana.']);
        exit;
    }

    // Generar slots dinámicamente desde configuración
    $horas     = [];
    $intervalo = (int)$config['intervalo_min'];

    $h = strtotime($config['manana_inicio']);
    while ($h < strtotime($config['manana_fin'])) {
        $horas[] = date('H:i', $h);
        $h = strtotime("+{$intervalo} minutes", $h);
    }
    $h = strtotime($config['tarde_inicio']);
    while ($h < strtotime($config['tarde_fin'])) {
        $horas[] = date('H:i', $h);
        $h = strtotime("+{$intervalo} minutes", $h);
    }

    // Citas ocupadas (excluyendo la cita actual si se está reprogramando)
    $stmtCitas = $pdo->prepare("
        SELECT hora_inicio::text AS hora_inicio
        FROM citas
        WHERE funcionario_id = :func
          AND fecha = :fecha
          AND id_cita != :excluir
          AND estado IN ('confirmada', 'pendiente', 'propuesta_reprogramacion')
    ");
    $stmtCitas->execute([
        ':func'    => $funcionarioId,
        ':fecha'   => $fecha,
        ':excluir' => $excluirCita
    ]);
    $citasOcupadas = array_map(
        fn($r) => substr($r['hora_inicio'], 0, 5),
        $stmtCitas->fetchAll()
    );

    // Bloqueos
    $stmtBloqueos = $pdo->prepare("
        SELECT hora_inicio, hora_fin
        FROM horarios_bloqueados
        WHERE funcionario_id = :func AND fecha = :fecha
    ");
    $stmtBloqueos->execute([':func' => $funcionarioId, ':fecha' => $fecha]);
    $bloqueos = $stmtBloqueos->fetchAll();

    $horaToMinutos = fn($h) => (int)explode(':', $h)[0] * 60 + (int)explode(':', $h)[1];

    $slotsManana = [];
    $slotsTarde  = [];

    foreach ($horas as $hora) {
        $disponible = !in_array($hora, $citasOcupadas);

        if ($disponible) {
            $horaActualMin = $horaToMinutos($hora);
            foreach ($bloqueos as $bloqueo) {
                $ini = $horaToMinutos(substr($bloqueo['hora_inicio'], 0, 5));
                $fin = $horaToMinutos(substr($bloqueo['hora_fin'],    0, 5));
                if ($horaActualMin >= $ini && $horaActualMin <= $fin) {
                    $disponible = false;
                    break;
                }
            }
        }

        $slot = ['hora' => $hora, 'disponible' => $disponible];

        if (strtotime($hora) < strtotime($config['manana_fin']))
            $slotsManana[] = $slot;
        else
            $slotsTarde[] = $slot;
    }

    echo json_encode([
        'manana' => $slotsManana,
        'tarde'  => $slotsTarde
    ]);

} catch (Exception $e) {
    error_log('Error en horarios_disponibles.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Error al consultar horarios']);
}