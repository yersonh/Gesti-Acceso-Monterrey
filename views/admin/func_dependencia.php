<?php
// $asignaciones, $funcionarios, $dependencias are set by AdminController::funcDependencia()
?>

<div class="page-header">
    <div>
        <h2>Funcionarios por Dependencia</h2>
        <p>Asignación de funcionarios a dependencias organizativas</p>
    </div>
    <button class="btn-primary" onclick="abrirModal('modal-asignar')">
        <i class="fas fa-link"></i> Asignar funcionario
    </button>
</div>

<!-- Tabla asignaciones -->
<div class="table-card">
    <?php if (empty($asignaciones)): ?>
    <div class="empty-state">
        <i class="fas fa-sitemap"></i>
        <h4>Sin asignaciones</h4>
        <p>Use el botón "Asignar funcionario" para crear nuevas asignaciones.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Funcionario</th>
                <th>Cargo</th>
                <th>Dependencia</th>
                <th>Fecha asignación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($asignaciones as $a): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($a['func_nombres'] . ' ' . $a['func_apellidos']) ?></strong>
            </td>
            <td style="font-size:0.82rem;color:var(--texto-sub);"><?= htmlspecialchars($a['func_cargo']) ?></td>
            <td>
                <span class="badge badge-rol"><?= htmlspecialchars($a['dep_nombre']) ?></span>
            </td>
            <td style="font-size:0.82rem;color:var(--texto-sub);">
                <?= htmlspecialchars(date('d/m/Y', strtotime($a['created_at']))) ?>
            </td>
            <td>
                <form method="POST" action="/admin/func-dependencia" style="display:inline;">
                    <?= csrf_field() ?>
                    <?= tab_id_field() ?>
                    <input type="hidden" name="accion"       value="desasignar">
                    <input type="hidden" name="asignacion_id" value="<?= (int)$a['id'] ?>">
                    <button type="submit" class="action-btn danger" title="Desasignar"
                            onclick="return confirm('¿Eliminar esta asignación?')">
                        <i class="fas fa-unlink"></i>
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

<!-- ══ Modal Asignar ══ -->
<div class="modal-overlay" id="modal-asignar">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-sitemap" style="color:var(--verde-medio);margin-right:6px;"></i> Asignar Funcionario a Dependencia</h3>
            <button class="modal-close" onclick="cerrarModal('modal-asignar')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/func-dependencia">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="asignar">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Funcionario *</label>
                    <select name="funcionario_id" class="form-control" required>
                        <option value="">-- Seleccionar funcionario --</option>
                        <?php foreach ($funcionarios as $f): ?>
                        <option value="<?= (int)$f['id_funcionario'] ?>">
                            <?= htmlspecialchars($f['nombres'] . ' ' . $f['apellidos']) ?>
                            (<?= htmlspecialchars($f['cargo']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Dependencia *</label>
                    <select name="dependencia_id" class="form-control" required>
                        <option value="">-- Seleccionar dependencia --</option>
                        <?php foreach ($dependencias as $d): ?>
                        <option value="<?= (int)$d['id_dependencia'] ?>">
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-asignar')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-link"></i> Asignar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(id) {
    document.getElementById(id).classList.add('open');
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}
</script>
