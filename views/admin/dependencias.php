<?php
// $dependencias is set by AdminController::dependencias()
?>

<div class="page-header">
    <div>
        <h2>Dependencias</h2>
        <p>Unidades organizativas de la alcaldía</p>
    </div>
    <button class="btn-primary" onclick="abrirModal('modal-nueva-dep')">
        <i class="fas fa-plus"></i> Nueva dependencia
    </button>
</div>

<!-- Tabla -->
<div class="table-card">
    <?php if (empty($dependencias)): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h4>Sin dependencias registradas</h4>
        <p>Use el botón "Nueva dependencia" para agregar.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>N° Funcionarios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($dependencias as $d):
            $dActivo = ($d['activo'] === 't' || $d['activo'] === true || $d['activo'] === 1 || $d['activo'] === '1');
        ?>
        <tr data-id="<?= (int)$d['id_dependencia'] ?>"
            data-nombre="<?= htmlspecialchars($d['nombre'] ?? '', ENT_QUOTES) ?>"
            data-descripcion="<?= htmlspecialchars($d['descripcion'] ?? '', ENT_QUOTES) ?>"
            data-activo="<?= $dActivo ? '1' : '0' ?>">
            <td style="color:var(--texto-sub);font-size:0.82rem;"><?= (int)$d['id_dependencia'] ?></td>
            <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
            <td style="max-width:200px;font-size:0.82rem;color:var(--texto-sub);">
                <?= htmlspecialchars($d['descripcion'] ?? '—') ?>
            </td>
            <td style="text-align:center;">
                <span class="badge badge-rol"><?= (int)$d['num_funcionarios'] ?></span>
            </td>
            <td>
                <?php if ($dActivo): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activa</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactiva</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="table-actions">
                    <!-- Editar -->
                    <button type="button" class="action-btn" title="Editar"
                            onclick="abrirEditar(this)">
                        <i class="fas fa-pen"></i>
                    </button>

                    <!-- Toggle activo -->
                    <button type="button" class="action-btn <?= $dActivo ? 'danger' : '' ?>"
                            title="<?= $dActivo ? 'Desactivar' : 'Activar' ?>"
                            onclick="abrirToggleDep(this)">
                        <i class="fas <?= $dActivo ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
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

<!-- ══ Modal Nueva Dependencia ══ -->
<div class="modal-overlay" id="modal-nueva-dep">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-building" style="color:var(--verde-medio);margin-right:6px;"></i> Nueva Dependencia</h3>
            <button class="modal-close" onclick="cerrarModal('modal-nueva-dep')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/dependencias">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="crear">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Secretaría de Planeación">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-nueva-dep')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Editar Dependencia ══ -->
<div class="modal-overlay" id="modal-editar-dep">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-pen" style="color:var(--verde-medio);margin-right:6px;"></i> Editar Dependencia</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar-dep')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/dependencias">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion"         value="editar">
            <input type="hidden" name="id_dependencia" id="edit-dep-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="edit-dep-nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="edit-dep-descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-editar-dep')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Toggle Dependencia ══ -->
<div class="modal-overlay" id="modal-toggle-dep">
    <div class="modal-card" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="dep-toggle-title"><i class="fas fa-building" style="margin-right:6px;"></i> Cambiar estado</h3>
            <button class="modal-close" onclick="cerrarModal('modal-toggle-dep')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="dep-toggle-body"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="cerrarModal('modal-toggle-dep')">Cancelar</button>
            <button type="button" id="btn-confirmar-dep-toggle" class="btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<!-- Formulario oculto para el submit -->
<form id="form-toggle-dep" method="POST" action="/admin/dependencias" style="display:none;">
    <?= csrf_field() ?>
    <?= tab_id_field() ?>
    <input type="hidden" name="accion"         value="toggle_activo">
    <input type="hidden" name="id_dependencia" id="dep-toggle-id">
    <input type="hidden" name="activo"         id="dep-toggle-activo">
</form>

<script>
function abrirModal(id) {
    document.getElementById(id).classList.add('open');
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}
function abrirEditar(btn) {
    var r = btn.closest('tr');
    document.getElementById('edit-dep-id').value          = r.dataset.id;
    document.getElementById('edit-dep-nombre').value      = r.dataset.nombre;
    document.getElementById('edit-dep-descripcion').value = r.dataset.descripcion || '';
    abrirModal('modal-editar-dep');
}
function abrirToggleDep(btn) {
    var r      = btn.closest('tr');
    var id     = r.dataset.id;
    var nombre = r.dataset.nombre;
    var activo = r.dataset.activo === '1';

    document.getElementById('dep-toggle-id').value     = id;
    document.getElementById('dep-toggle-activo').value = activo ? '0' : '1';

    var title = document.getElementById('dep-toggle-title');
    var body  = document.getElementById('dep-toggle-body');
    var ok    = document.getElementById('btn-confirmar-dep-toggle');

    if (activo) {
        title.innerHTML = '<i class="fas fa-toggle-off" style="color:#ef4444;margin-right:6px;"></i> Desactivar dependencia';
        body.innerHTML  =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">La dependencia <strong>' + nombre + '</strong> quedará inactiva y no estará disponible para asignar a funcionarios.</p>' +
            '<div style="background:#fff1f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#991b1b;">' +
                '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>¿Confirmar desactivación?' +
            '</div>';
        ok.className = 'btn-danger';
        ok.textContent = 'Desactivar';
    } else {
        title.innerHTML = '<i class="fas fa-toggle-on" style="color:var(--verde-medio);margin-right:6px;"></i> Activar dependencia';
        body.innerHTML  =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">La dependencia <strong>' + nombre + '</strong> volverá a estar disponible para asignar a funcionarios.</p>' +
            '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#166534;">' +
                '<i class="fas fa-check-circle" style="margin-right:6px;"></i>¿Confirmar activación?' +
            '</div>';
        ok.className = 'btn-primary';
        ok.textContent = 'Activar';
    }

    ok.onclick = function() { document.getElementById('form-toggle-dep').submit(); };
    abrirModal('modal-toggle-dep');
}
</script>
