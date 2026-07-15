<?php
/** @var array $personal */
/** @var array $kpis */
$personal ??= [];
$kpis     ??= [];
$tiposId = ['CC','TI','CE','PA','NIT','RC'];
?>

<div class="page-header">
    <div>
        <h2>Personal</h2>
        <p>Registro de empleados de la alcaldía</p>
    </div>
    <button class="btn-primary" onclick="abrirModal('modal-nuevo-personal')">
        <i class="fas fa-plus"></i> Nuevo personal
    </button>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card verde">
        <div class="kpi-label"><i class="fas fa-user"></i> Total personal</div>
        <div class="kpi-value"><?= (int)($kpis['total'] ?? 0) ?></div>
    </div>
    <div class="kpi-card azul">
        <div class="kpi-label"><i class="fas fa-check-circle"></i> Activos</div>
        <div class="kpi-value"><?= (int)($kpis['activos'] ?? 0) ?></div>
    </div>
</div>

<!-- Tabla -->
<div class="table-card">
    <div class="table-toolbar" style="flex-wrap:wrap;gap:10px;">
        <span id="per-contador" style="font-size:0.82rem;color:var(--texto-sub);">
            <?= count($personal) ?> registro(s)
        </span>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;">
            <input type="text" id="per-buscar" class="filter-input" placeholder="Buscar nombre, cargo, email…" style="width:220px;"
                   oninput="filtrarTabla({tbodyId:'per-tbody',buscarId:'per-buscar',filtros:[{selectId:'per-estado',attr:'activo'}],contadorId:'per-contador'})">
            <select id="per-estado" class="filter-input" style="width:120px;"
                    onchange="filtrarTabla({tbodyId:'per-tbody',buscarId:'per-buscar',filtros:[{selectId:'per-estado',attr:'activo'}],contadorId:'per-contador'})">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>
    <?php if (empty($personal)): ?>
    <div class="empty-state">
        <i class="fas fa-user"></i>
        <h4>Sin personal registrado</h4>
        <p>Use el botón "Nuevo personal" para agregar registros.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Nombre completo</th>
                <th>Identificación</th>
                <th>Cargo</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="per-tbody">
        <?php foreach ($personal as $p):
            $pActivo = ($p['activo'] === 't' || $p['activo'] === true || $p['activo'] === 1 || $p['activo'] === '1');
        ?>
        <tr data-activo="<?= $pActivo ? '1' : '0' ?>"
            data-id="<?= (int)$p['id_personal'] ?>"
            data-nombres="<?= htmlspecialchars($p['nombres'] ?? '', ENT_QUOTES) ?>"
            data-apellidos="<?= htmlspecialchars($p['apellidos'] ?? '', ENT_QUOTES) ?>"
            data-tipo="<?= htmlspecialchars($p['tipo_identificacion'] ?? '', ENT_QUOTES) ?>"
            data-numero="<?= htmlspecialchars($p['numero_identificacion'] ?? '', ENT_QUOTES) ?>"
            data-email="<?= htmlspecialchars($p['email'] ?? '', ENT_QUOTES) ?>"
            data-telefono="<?= htmlspecialchars($p['telefono'] ?? '', ENT_QUOTES) ?>"
            data-cargo="<?= htmlspecialchars($p['cargo'] ?? '', ENT_QUOTES) ?>">
            <td>
                <strong><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></strong>
            </td>
            <td style="font-size:0.82rem;">
                <?= htmlspecialchars($p['tipo_identificacion']) ?>
                <?= htmlspecialchars($p['numero_identificacion']) ?>
            </td>
            <td><?= htmlspecialchars($p['cargo']) ?></td>
            <td style="font-size:0.82rem;"><?= htmlspecialchars($p['email']) ?></td>
            <td style="font-size:0.82rem;"><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
            <td>
                <?php if ($p['activo']): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactivo</span>
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
                    <form method="POST" action="/admin/personal" style="display:inline;">
                        <?= csrf_field() ?>
                        <?= tab_id_field() ?>
                        <input type="hidden" name="accion"      value="toggle_activo">
                        <input type="hidden" name="id_personal" value="<?= (int)$p['id_personal'] ?>">
                        <input type="hidden" name="activo"      value="<?= $p['activo'] ? '0' : '1' ?>">
                        <button type="submit" class="action-btn <?= $p['activo'] ? 'danger' : '' ?>"
                                title="<?= $p['activo'] ? 'Desactivar' : 'Activar' ?>"
                                onclick="return confirm('¿Confirmar cambio de estado?')">
                            <i class="fas <?= $p['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══ Modal Nuevo Personal ══ -->
<div class="modal-overlay" id="modal-nuevo-personal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="color:var(--verde-medio);margin-right:6px;"></i> Nuevo Personal</h3>
            <button class="modal-close" onclick="cerrarModal('modal-nuevo-personal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/personal" onsubmit="return validarFormulario(this)">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="crear">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" required placeholder="Ej: Juan Carlos"
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" required placeholder="Ej: Pérez Gómez"
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo ID *</label>
                        <select name="tipo_identificacion" class="form-control" required>
                            <?php foreach ($tiposId as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número ID *</label>
                        <input type="text" name="numero_identificacion" class="form-control" required placeholder="Ej: 1234567890"
                               data-vrequired="1" data-vpattern="numero_id" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="personal-email" class="form-control" required placeholder="correo@ejemplo.com"
                               data-vrequired="1" oninput="validarInput(this)"
                               onblur="verificarEmailUsuario(this,'personal-email-error','btn-guardar-personal')">
                        <span id="personal-email-error" class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Ej: 3001234567"
                               data-voptional="1" data-vpattern="telefono" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Cargo *</label>
                    <input type="text" name="cargo" class="form-control" required placeholder="Ej: Secretario General"
                           data-vrequired="1" data-vpattern="cargo" oninput="validarInput(this)">
                    <span class="field-error"></span>
                </div>
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:12px 14px;display:flex;gap:10px;align-items:flex-start;margin-top:4px;">
                    <i class="fas fa-envelope" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                    <p style="font-size:0.83rem;color:#166534;margin:0;line-height:1.5;">
                        Se creará automáticamente una cuenta con rol <strong>Recepcionista</strong> y se enviará al correo ingresado una contraseña temporal válida por <strong>24 horas</strong>.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-nuevo-personal')">Cancelar</button>
                <button type="submit" id="btn-guardar-personal" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Editar Personal ══ -->
<div class="modal-overlay" id="modal-editar-personal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-pen" style="color:var(--verde-medio);margin-right:6px;"></i> Editar Personal</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar-personal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/personal" onsubmit="return validarFormulario(this)">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion"      value="editar">
            <input type="hidden" name="id_personal" id="edit-id_personal">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" id="edit-nombres" class="form-control" required
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" id="edit-apellidos" class="form-control" required
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo ID *</label>
                        <select name="tipo_identificacion" id="edit-tipo_identificacion" class="form-control" required>
                            <?php foreach ($tiposId as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número ID *</label>
                        <input type="text" name="numero_identificacion" id="edit-numero_identificacion" class="form-control" required
                               data-vrequired="1" data-vpattern="numero_id" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="edit-email" class="form-control" required
                               data-vrequired="1" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="edit-telefono" class="form-control"
                               data-voptional="1" data-vpattern="telefono" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Cargo *</label>
                    <input type="text" name="cargo" id="edit-cargo" class="form-control" required
                           data-vrequired="1" data-vpattern="cargo" oninput="validarInput(this)">
                    <span class="field-error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-editar-personal')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Actualizar</button>
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
function abrirEditar(btn) {
    var r = btn.closest('tr');
    document.getElementById('edit-id_personal').value           = r.dataset.id;
    document.getElementById('edit-nombres').value               = r.dataset.nombres;
    document.getElementById('edit-apellidos').value             = r.dataset.apellidos;
    document.getElementById('edit-tipo_identificacion').value   = r.dataset.tipo;
    document.getElementById('edit-numero_identificacion').value = r.dataset.numero;
    document.getElementById('edit-email').value                 = r.dataset.email;
    document.getElementById('edit-telefono').value              = r.dataset.telefono || '';
    document.getElementById('edit-cargo').value                 = r.dataset.cargo;
    limpiarValidacion(document.getElementById('modal-editar-personal').querySelector('form'));
    abrirModal('modal-editar-personal');
}
</script>
