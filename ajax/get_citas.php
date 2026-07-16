<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Ciudadano') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cita.php';

$ciudadanoId  = (int)($_SESSION['ciudadano_id'] ?? 0);

$estadosPermitidos = ['todas', 'pendiente', 'confirmada', 'cancelada', 'finalizada',
                      'no_asistio', 'propuesta_reprogramacion', 'contrapropuesta_ciudadano'];
$filtroEstado = $_GET['estado'] ?? 'todas';
if (!in_array($filtroEstado, $estadosPermitidos, true)) {
    $filtroEstado = 'todas';
}

if (!$ciudadanoId) {
    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id_ciudadano FROM ciudadanos_cache WHERE usuario_id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $row = $stmt->fetch();
        if ($row) {
            $ciudadanoId = (int)$row['id_ciudadano'];
            $_SESSION['ciudadano_id'] = $ciudadanoId;
        }
    } catch (Exception $e) {
        error_log('get_citas.php - fallback ciudadano_id: ' . $e->getMessage());
    }
}

$perPage      = 25;
$page         = max(1, (int)($_GET['pagina'] ?? 1));
$totalCitas   = Cita::countMisCitas($ciudadanoId, $filtroEstado);
$totalPaginas = (int)ceil($totalCitas / $perPage) ?: 1;
$page         = min($page, $totalPaginas);

$citas = Cita::getMisCitas($ciudadanoId, $filtroEstado, $page, $perPage);
$stats = Cita::getStats($ciudadanoId);

function badgeEstado($estado) {
    $map = [
        'pendiente'  => ['label' => 'Pendiente',  'class' => 'badge-pendiente',  'icon' => 'fa-clock'],
        'confirmada' => ['label' => 'Confirmada', 'class' => 'badge-confirmada', 'icon' => 'fa-check-circle'],
        'cancelada'  => ['label' => 'Cancelada',  'class' => 'badge-cancelada',  'icon' => 'fa-times-circle'],
        'finalizada' => ['label' => 'Finalizada', 'class' => 'badge-completada', 'icon' => 'fa-flag-checkered'],
        'no_asistio' => ['label' => 'No asistió', 'class' => 'badge-noasistio',  'icon' => 'fa-user-times'],
        'propuesta_reprogramacion' => ['label' => 'Propuesta de reprogramación', 'class' => 'badge-propuesta-reprogramacion', 'icon' => 'fa-calendar-plus'],
        'contrapropuesta_ciudadano' => ['label' => 'Contrapropuesta del ciudadano', 'class' => 'badge-contrapropuesta-ciudadano', 'icon' => 'fa-calendar-plus']
    ];
    return $map[$estado] ?? ['label' => $estado, 'class' => '', 'icon' => 'fa-circle'];
}

function formatFecha($fecha) {
    $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    [$y,$m,$d] = explode('-', $fecha);
    return "$d {$meses[(int)$m]} $y";
}

function formatHora($hora) {
    [$h,$m] = explode(':', $hora);
    $h = (int)$h;
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $h12  = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
    return "$h12:$m $ampm";
}

ob_start();
if (empty($citas)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3><?= $filtroEstado === 'todas' ? 'Aún no tienes citas' : 'No tienes citas ' . ($filtroEstado === 'pendiente' ? 'pendientes' : $filtroEstado . 's') ?></h3>
        <p><?= $filtroEstado === 'todas' ? 'Agenda tu primera cita con la Alcaldía.' : 'Prueba cambiando el filtro.' ?></p>
        <a href="agendar.php"><i class="fas fa-plus"></i> Agendar cita</a>
    </div>
<?php else:
    foreach ($citas as $i => $cita):
        $badge = badgeEstado($cita['estado']); ?>
        <div class="cita-card estado-<?= $cita['estado'] ?>" style="animation-delay:<?= $i*0.06 ?>s">
            <div class="cita-info">
                <div class="cita-dependencia"><?= htmlspecialchars($cita['dependencia']) ?></div>
                <div class="cita-funcionario"><?= htmlspecialchars($cita['func_nombres'].' '.$cita['func_apellidos']) ?></div>
                <div class="cita-meta">
                    <span class="cita-meta-item <?= $cita['estado'] === 'propuesta_reprogramacion' ? 'cita-meta-tachado' : '' ?>"><i class="fas fa-calendar"></i><?= formatFecha($cita['fecha']) ?></span>
                    <span class="cita-meta-item <?= $cita['estado'] === 'propuesta_reprogramacion' ? 'cita-meta-tachado' : '' ?>"><i class="fas fa-clock"></i><?= formatHora(substr($cita['hora_inicio'],0,5)) ?> – <?= formatHora(substr($cita['hora_fin'],0,5)) ?></span>
                </div>
                <?php if ($cita['estado'] === 'propuesta_reprogramacion' && !empty($cita['fecha_propuesta'])): ?>
                <div class="cita-propuesta-nueva">
                    <i class="fas fa-calendar-alt"></i>
                    Nueva propuesta: <strong><?= formatFecha($cita['fecha_propuesta']) ?> · <?= formatHora(substr($cita['hora_propuesta'],0,5)) ?></strong>
                </div>
                <?php if (!empty($cita['token_respuesta'])): ?>
                <div class="cita-responder-acciones">
                    <a href="/cita/responder?token=<?= htmlspecialchars($cita['token_respuesta']) ?>&accion=aceptar&from_dashboard=1"
                       class="btn-resp btn-resp-verde">
                        <i class="fas fa-check"></i> Aceptar
                    </a>
                    <button type="button" class="btn-resp btn-resp-amber btn-abrir-responder"
                            data-token="<?= htmlspecialchars($cita['token_respuesta']) ?>"
                            data-fecha-propuesta="<?= htmlspecialchars($cita['fecha_propuesta']) ?>"
                            data-hora-propuesta="<?= htmlspecialchars(substr($cita['hora_propuesta'],0,5)) ?>">
                        <i class="fas fa-calendar-alt"></i> Proponer otra / Rechazar
                    </button>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <div class="cita-motivo"><strong>Motivo:</strong> <?= htmlspecialchars($cita['motivo']) ?></div>
                <?php if (!empty($cita['nota_gestion'])): ?>
                <div class="cita-nota"><i class="fas fa-comment-alt"></i> <strong>Nota:</strong> <?= htmlspecialchars($cita['nota_gestion']) ?></div>
                <?php endif; ?>
            </div>
            <div class="cita-acciones">
                <span class="badge <?= $badge['class'] ?>"><i class="fas <?= $badge['icon'] ?>"></i> <?= $badge['label'] ?></span>
                <?php if ($cita['estado'] === 'pendiente'): ?>
                <button class="btn-cancelar" data-cita-id="<?= $cita['id_cita'] ?>"><i class="fas fa-times"></i> Cancelar</button>
                <?php endif; ?>
            </div>
        </div>
<?php endforeach;
endif;
$citasHtml = ob_get_clean();

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode([
    'citasHtml'   => $citasHtml,
    'stats'       => $stats,
    'paginacion'  => [
        'paginaActual'  => $page,
        'totalPaginas'  => $totalPaginas,
        'total'         => $totalCitas,
        'perPage'       => $perPage,
    ],
]);
exit;