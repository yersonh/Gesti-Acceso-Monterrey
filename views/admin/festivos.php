<?php
// $festivos is set by AdminController::festivos()
?>

<div class="page-header">
    <div>
        <h2>Días Festivos</h2>
        <p>Calendario de días no hábiles que bloquean el agendamiento de citas</p>
    </div>
    <button class="btn-primary" onclick="abrirModal('modal-nuevo-festivo')">
        <i class="fas fa-plus"></i> Agregar festivo
    </button>
</div>

<!-- Tabla -->
<div class="table-card">
    <?php if (empty($festivos)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h4>Sin días festivos registrados</h4>
        <p>Use el botón "Agregar festivo" para crear registros.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Recurrente</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($festivos as $f):
            $fActivo = ($f['activo'] === 't' || $f['activo'] === true || $f['activo'] === 1 || $f['activo'] === '1');
        ?>
        <tr data-id="<?= (int)$f['id'] ?>"
            data-descripcion="<?= htmlspecialchars($f['descripcion'] ?? '', ENT_QUOTES) ?>"
            data-fecha="<?= htmlspecialchars($f['fecha'] ?? '', ENT_QUOTES) ?>"
            data-recurrente="<?= ($f['recurrente'] === 't' || $f['recurrente'] === true || $f['recurrente'] === 1 || $f['recurrente'] === '1') ? '1' : '0' ?>"
            data-activo="<?= $fActivo ? '1' : '0' ?>">
            <td><strong><?= htmlspecialchars($f['descripcion']) ?></strong></td>
            <td style="font-size:0.85rem;">
                <?= htmlspecialchars(date('d/m/Y', strtotime($f['fecha']))) ?>
            </td>
            <td>
                <?php if ($f['recurrente']): ?>
                    <span class="badge badge-recurrente"><i class="fas fa-redo" style="font-size:0.65rem;"></i> Sí</span>
                <?php else: ?>
                    <span class="badge badge-no-recurrente">No</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($fActivo): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactivo</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="table-actions">
                    <button type="button" class="action-btn" title="Editar"
                            onclick="abrirEditarFestivo(this)">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="action-btn <?= $fActivo ? 'danger' : '' ?>"
                            title="<?= $fActivo ? 'Desactivar' : 'Activar' ?>"
                            onclick="abrirToggleFestivo(this)">
                        <i class="fas <?= $fActivo ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══ Modal Nuevo Festivo ══ -->
<div class="modal-overlay" id="modal-nuevo-festivo">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-calendar-plus" style="color:var(--verde-medio);margin-right:6px;"></i> Agregar Día Festivo</h3>
            <button class="modal-close" onclick="cerrarModal('modal-nuevo-festivo')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/festivos">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="crear">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Descripción *</label>
                    <input type="text" name="descripcion" class="form-control" required
                           placeholder="Ej: Día de la Independencia">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="recurrente" value="1">
                        Recurrente anualmente
                        <span style="font-size:0.78rem;color:var(--texto-sub);margin-left:4px;">
                            (se repite cada año en el mismo día/mes)
                        </span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-nuevo-festivo')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Toggle Festivo ══ -->
<div class="modal-overlay" id="modal-toggle-festivo">
    <div class="modal-card" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="fest-toggle-title"><i class="fas fa-calendar" style="margin-right:6px;"></i> Cambiar estado</h3>
            <button class="modal-close" onclick="cerrarModal('modal-toggle-festivo')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="fest-toggle-body"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="cerrarModal('modal-toggle-festivo')">Cancelar</button>
            <button type="button" id="btn-confirmar-fest-toggle" class="btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<form id="form-toggle-festivo" method="POST" action="/admin/festivos" style="display:none;">
    <?= csrf_field() ?>
    <?= tab_id_field() ?>
    <input type="hidden" name="accion"     value="toggle_activo">
    <input type="hidden" name="id_festivo" id="fest-toggle-id">
    <input type="hidden" name="activo"     id="fest-toggle-activo">
</form>

<script>
function abrirModal(id) {
    document.getElementById(id).classList.add('open');
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}
function abrirToggleFestivo(btn) {
    var r      = btn.closest('tr');
    var id     = r.dataset.id;
    var nombre = r.dataset.descripcion;
    var activo = r.dataset.activo === '1';

    document.getElementById('fest-toggle-id').value     = id;
    document.getElementById('fest-toggle-activo').value = activo ? '0' : '1';

    var title = document.getElementById('fest-toggle-title');
    var body  = document.getElementById('fest-toggle-body');
    var ok    = document.getElementById('btn-confirmar-fest-toggle');

    if (activo) {
        title.innerHTML = '<i class="fas fa-toggle-off" style="color:#ef4444;margin-right:6px;"></i> Desactivar día festivo';
        body.innerHTML  =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">El festivo <strong>' + nombre + '</strong> quedará inactivo y no bloqueará el agendamiento de citas.</p>' +
            '<div style="background:#fff1f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#991b1b;">' +
                '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>¿Confirmar desactivación?' +
            '</div>';
        ok.className   = 'btn-danger';
        ok.textContent = 'Desactivar';
    } else {
        title.innerHTML = '<i class="fas fa-toggle-on" style="color:var(--verde-medio);margin-right:6px;"></i> Activar día festivo';
        body.innerHTML  =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">El festivo <strong>' + nombre + '</strong> volverá a bloquear el agendamiento de citas en esa fecha.</p>' +
            '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#166534;">' +
                '<i class="fas fa-check-circle" style="margin-right:6px;"></i>¿Confirmar activación?' +
            '</div>';
        ok.className   = 'btn-primary';
        ok.textContent = 'Activar';
    }

    ok.onclick = function() { document.getElementById('form-toggle-festivo').submit(); };
    abrirModal('modal-toggle-festivo');
}
function abrirEditarFestivo(btn) {
    var r = btn.closest('tr');
    document.getElementById('edit-fest-id').value          = r.dataset.id;
    document.getElementById('edit-fest-descripcion').value = r.dataset.descripcion;
    document.getElementById('edit-fest-fecha').value       = r.dataset.fecha;
    document.getElementById('edit-fest-recurrente').checked = r.dataset.recurrente === '1';
    abrirModal('modal-editar-festivo');
}
</script>

<!-- ══ Modal Editar Festivo ══ -->
<div class="modal-overlay" id="modal-editar-festivo">
    <div class="modal-card" style="max-width:460px;">
        <div class="modal-header">
            <h3><i class="fas fa-pen" style="color:var(--verde-medio);margin-right:6px;"></i> Editar Día Festivo</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar-festivo')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/festivos">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion"     value="editar">
            <input type="hidden" name="id_festivo" id="edit-fest-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Descripción *</label>
                    <input type="text" name="descripcion" id="edit-fest-descripcion"
                           class="form-control" required placeholder="Ej: Día de la Independencia">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" id="edit-fest-fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="recurrente" id="edit-fest-recurrente" value="1">
                        Recurrente anualmente
                        <span style="font-size:0.78rem;color:var(--texto-sub);margin-left:4px;">
                            (se repite cada año en el mismo día/mes)
                        </span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-editar-festivo')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>
