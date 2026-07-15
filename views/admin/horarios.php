<?php
// $config is set by AdminController::horarios()
$mi  = $config['manana_inicio'] ?? '08:00';
$mf  = $config['manana_fin']    ?? '12:00';
$ti  = $config['tarde_inicio']  ?? '14:00';
$tf  = $config['tarde_fin']     ?? '18:00';
$inv = $config['intervalo_min'] ?? 30;

$diasConfig = [
    'lunes'     => 'Lunes',
    'martes'    => 'Martes',
    'miercoles' => 'Miércoles',
    'jueves'    => 'Jueves',
    'viernes'   => 'Viernes',
    'sabado'    => 'Sábado',
    'domingo'   => 'Domingo',
];
?>

<div class="page-header">
    <div>
        <h2>Horarios de Atención</h2>
        <p>Configuración de jornadas y franjas horarias del sistema</p>
    </div>
</div>

<form method="POST" action="/admin/horarios">
    <?= csrf_field() ?>
    <?= tab_id_field() ?>
    <input type="hidden" name="accion" value="guardar">

    <div class="toggle-grid" style="margin-top:4px;">

        <!-- Jornada mañana -->
        <div class="config-card">
            <h4><i class="fas fa-sun"></i> Jornada Mañana</h4>
            <div class="config-row">
                <div>
                    <div class="config-row-label">Hora de inicio</div>
                    <div class="config-row-sub">Inicio de atención en la mañana</div>
                </div>
                <input type="time" name="manana_inicio" value="<?= htmlspecialchars(substr($mi, 0, 5)) ?>"
                       class="form-control" style="width:120px;">
            </div>
            <div class="config-row">
                <div>
                    <div class="config-row-label">Hora de fin</div>
                    <div class="config-row-sub">Fin de atención en la mañana</div>
                </div>
                <input type="time" name="manana_fin" value="<?= htmlspecialchars(substr($mf, 0, 5)) ?>"
                       class="form-control" style="width:120px;">
            </div>
        </div>

        <!-- Jornada tarde -->
        <div class="config-card">
            <h4><i class="fas fa-moon"></i> Jornada Tarde</h4>
            <div class="config-row">
                <div>
                    <div class="config-row-label">Hora de inicio</div>
                    <div class="config-row-sub">Inicio de atención en la tarde</div>
                </div>
                <input type="time" name="tarde_inicio" value="<?= htmlspecialchars(substr($ti, 0, 5)) ?>"
                       class="form-control" style="width:120px;">
            </div>
            <div class="config-row">
                <div>
                    <div class="config-row-label">Hora de fin</div>
                    <div class="config-row-sub">Fin de atención en la tarde</div>
                </div>
                <input type="time" name="tarde_fin" value="<?= htmlspecialchars(substr($tf, 0, 5)) ?>"
                       class="form-control" style="width:120px;">
            </div>
        </div>

        <!-- Días hábiles -->
        <div class="config-card">
            <h4><i class="fas fa-calendar-week"></i> Días Hábiles</h4>
            <?php foreach ($diasConfig as $key => $label): ?>
            <div class="config-row">
                <div class="config-row-label"><?= $label ?></div>
                <label class="form-check">
                    <input type="checkbox" name="<?= $key ?>"
                           <?= !empty($config[$key]) ? 'checked' : '' ?>>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Intervalo -->
        <div class="config-card">
            <h4><i class="fas fa-stopwatch"></i> Intervalo de Citas</h4>
            <div class="config-row">
                <div>
                    <div class="config-row-label">Duración por cita</div>
                    <div class="config-row-sub">En minutos (5 a 120)</div>
                </div>
                <input type="number" name="intervalo_min" value="<?= (int)$inv ?>"
                       min="5" max="120" step="5"
                       class="form-control" style="width:80px;text-align:center;">
            </div>
        </div>

    </div>

    <div style="margin-top:24px;">
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i> Guardar cambios
        </button>
    </div>
</form>
