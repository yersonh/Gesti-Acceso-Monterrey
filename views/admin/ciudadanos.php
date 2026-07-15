<?php
/** @var array $ciudadanos */
/** @var array $kpis */
$ciudadanos ??= [];
$kpis       ??= [];
?>

<div class="page-header">
    <div>
        <h2>Ciudadanos</h2>
        <p>Ciudadanos registrados en el sistema</p>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card verde">
        <div class="kpi-label"><i class="fas fa-users"></i> Total registrados</div>
        <div class="kpi-value"><?= (int)($kpis['total'] ?? 0) ?></div>
    </div>
    <div class="kpi-card azul">
        <div class="kpi-label"><i class="fas fa-user-check"></i> Activos</div>
        <div class="kpi-value"><?= (int)($kpis['activos'] ?? 0) ?></div>
    </div>
    <div class="kpi-card dorado">
        <div class="kpi-label"><i class="fas fa-id-card"></i> Con cuenta</div>
        <div class="kpi-value"><?= (int)($kpis['con_cuenta'] ?? 0) ?></div>
    </div>
</div>

<!-- Tabla -->
<div class="table-card">
    <div class="table-toolbar" style="flex-wrap:wrap;gap:10px;">
        <span id="ciu-contador" style="font-size:0.82rem;color:var(--texto-sub);">
            <?= count($ciudadanos) ?> registro(s)
        </span>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;">
            <input type="text" id="ciu-buscar" class="filter-input" placeholder="Buscar nombre, ID, email…" style="width:210px;"
                   oninput="filtrarTabla({tbodyId:'ciu-tbody',buscarId:'ciu-buscar',filtros:[{selectId:'ciu-estado',attr:'activo'},{selectId:'ciu-cuenta',attr:'cuenta'}],contadorId:'ciu-contador'})">
            <select id="ciu-estado" class="filter-input" style="width:120px;"
                    onchange="filtrarTabla({tbodyId:'ciu-tbody',buscarId:'ciu-buscar',filtros:[{selectId:'ciu-estado',attr:'activo'},{selectId:'ciu-cuenta',attr:'cuenta'}],contadorId:'ciu-contador'})">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            <select id="ciu-cuenta" class="filter-input" style="width:130px;"
                    onchange="filtrarTabla({tbodyId:'ciu-tbody',buscarId:'ciu-buscar',filtros:[{selectId:'ciu-estado',attr:'activo'},{selectId:'ciu-cuenta',attr:'cuenta'}],contadorId:'ciu-contador'})">
                <option value="">Con/sin cuenta</option>
                <option value="1">Con cuenta</option>
                <option value="0">Sin cuenta</option>
            </select>
        </div>
    </div>

    <?php if (empty($ciudadanos)): ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h4>Sin ciudadanos registrados</h4>
        <p>Aún no hay ciudadanos en el sistema.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Identificación</th>
                <th>Contacto</th>
                <th>Proveniencia</th>
                <th>Citas</th>
                <th>Cuenta</th>
                <th>Estado</th>
                <th>Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="ciu-tbody">
        <?php foreach ($ciudadanos as $c):
            $cActivo = ($c['activo'] === 't' || $c['activo'] === true || $c['activo'] === 1 || $c['activo'] === '1');
            $cCuenta = !empty($c['cuenta_email']) ? '1' : '0';
        ?>
        <tr data-activo="<?= $cActivo ? '1' : '0' ?>"
            data-cuenta="<?= $cCuenta ?>">
            <td style="color:var(--texto-sub);font-size:0.82rem;"><?= (int)$c['id_ciudadano'] ?></td>
            <td>
                <strong><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></strong>
            </td>
            <td style="font-size:0.82rem;">
                <span style="color:var(--texto-sub);"><?= htmlspecialchars($c['tipo_identificacion']) ?></span>
                <?= htmlspecialchars($c['numero_identificacion']) ?>
            </td>
            <td style="font-size:0.82rem;">
                <?php if ($c['email']): ?>
                    <div><?= htmlspecialchars($c['email']) ?></div>
                <?php endif; ?>
                <?php if ($c['telefono']): ?>
                    <div style="color:var(--texto-sub);"><?= htmlspecialchars($c['telefono']) ?></div>
                <?php endif; ?>
            </td>
            <td style="font-size:0.82rem;color:var(--texto-sub);">
                <?= htmlspecialchars($c['proveniencia'] ?? '—') ?>
            </td>
            <td style="text-align:center;">
                <span class="badge badge-rol"><?= (int)$c['total_citas'] ?></span>
            </td>
            <td style="font-size:0.82rem;">
                <?php if ($c['cuenta_email']): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Sí</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> No</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($c['activo']): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactivo</span>
                <?php endif; ?>
            </td>
            <td style="color:var(--texto-sub);font-size:0.82rem;white-space:nowrap;">
                <?= htmlspecialchars($c['created_at'] ? date('d/m/Y', strtotime($c['created_at'])) : '—') ?>
            </td>
            <td>
                <form method="POST" action="/admin/ciudadanos" style="display:inline;">
                    <?= csrf_field() ?>
                    <?= tab_id_field() ?>
                    <input type="hidden" name="accion"       value="toggle_activo">
                    <input type="hidden" name="id_ciudadano" value="<?= (int)$c['id_ciudadano'] ?>">
                    <input type="hidden" name="activo"       value="<?= $c['activo'] ? '0' : '1' ?>">
                    <button type="submit"
                            class="action-btn <?= $c['activo'] ? 'danger' : '' ?>"
                            title="<?= $c['activo'] ? 'Desactivar' : 'Activar' ?>"
                            onclick="return confirm('¿Confirmar cambio de estado para <?= htmlspecialchars(addslashes($c['nombres'])) ?>?')">
                        <i class="fas <?= $c['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
