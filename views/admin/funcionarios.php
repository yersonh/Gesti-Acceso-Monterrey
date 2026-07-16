<?php
// $funcionarios, $kpis are set by AdminController::funcionarios()
$tiposId = ['CC','TI','CE','PA','NIT','RC'];
?>

<div class="page-header">
    <div>
        <h2>Funcionarios</h2>
        <p>Gestión de funcionarios habilitados para recibir citas</p>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card verde">
        <div class="kpi-label"><i class="fas fa-user-tie"></i> Total</div>
        <div class="kpi-value"><?= (int)($kpis['total'] ?? 0) ?></div>
    </div>
    <div class="kpi-card azul">
        <div class="kpi-label"><i class="fas fa-check-circle"></i> Activos</div>
        <div class="kpi-value"><?= (int)($kpis['activos'] ?? 0) ?></div>
    </div>
    <div class="kpi-card dorado">
        <div class="kpi-label"><i class="fas fa-building"></i> Con dependencias</div>
        <div class="kpi-value"><?= (int)($kpis['con_dependencias'] ?? 0) ?></div>
    </div>
</div>

<!-- Tabla -->
<div class="table-card">
    <div class="table-toolbar" style="flex-wrap:wrap;gap:10px;">
        <span id="func-contador" style="font-size:0.82rem;color:var(--texto-sub);">
            <?= count($funcionarios) ?> registro(s)
        </span>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;">
            <input type="text" id="func-buscar" class="filter-input" placeholder="Buscar nombre, cargo, email…" style="width:220px;"
                   oninput="filtrarTabla({tbodyId:'func-tbody',buscarId:'func-buscar',filtros:[{selectId:'func-estado',attr:'activo'}],contadorId:'func-contador'})">
            <select id="func-estado" class="filter-input" style="width:120px;"
                    onchange="filtrarTabla({tbodyId:'func-tbody',buscarId:'func-buscar',filtros:[{selectId:'func-estado',attr:'activo'}],contadorId:'func-contador'})">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>
    <?php if (empty($funcionarios)): ?>
    <div class="empty-state">
        <i class="fas fa-user-tie"></i>
        <h4>Sin funcionarios registrados</h4>
        <p>Aún no hay funcionarios sincronizados desde Core.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Identificación</th>
                <th>Cargo</th>
                <th>Email</th>
                <th>Dependencias</th>
                <th>Cuenta</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="func-tbody">
        <?php foreach ($funcionarios as $f):
            $fActivo = ($f['activo'] === 't' || $f['activo'] === true || $f['activo'] === 1 || $f['activo'] === '1');
        ?>
        <tr data-activo="<?= $fActivo ? '1' : '0' ?>"
            data-id="<?= (int)$f['id_funcionario'] ?>"
            data-nombres="<?= htmlspecialchars($f['nombres'] ?? '', ENT_QUOTES) ?>"
            data-apellidos="<?= htmlspecialchars($f['apellidos'] ?? '', ENT_QUOTES) ?>"
            data-tipo="<?= htmlspecialchars($f['tipo_identificacion'] ?? '', ENT_QUOTES) ?>"
            data-numero="<?= htmlspecialchars($f['numero_identificacion'] ?? '', ENT_QUOTES) ?>"
            data-email="<?= htmlspecialchars($f['email'] ?? '', ENT_QUOTES) ?>"
            data-telefono="<?= htmlspecialchars($f['telefono'] ?? '', ENT_QUOTES) ?>"
            data-cargo="<?= htmlspecialchars($f['cargo'] ?? '', ENT_QUOTES) ?>"
            data-tiene-cuenta="<?= $f['tiene_cuenta'] ? '1' : '0' ?>">
            <td>
                <strong><?= htmlspecialchars($f['nombres'] . ' ' . $f['apellidos']) ?></strong>
            </td>
            <td style="font-size:0.82rem;">
                <?= htmlspecialchars($f['tipo_identificacion']) ?>
                <?= htmlspecialchars($f['numero_identificacion']) ?>
            </td>
            <td><?= htmlspecialchars($f['cargo']) ?></td>
            <td style="font-size:0.82rem;"><?= htmlspecialchars($f['email']) ?></td>
            <td style="max-width:200px;">
                <?php if ($f['dependencias']): ?>
                <div class="deps-scroll">
                    <?php foreach (explode(', ', $f['dependencias']) as $dep): ?>
                    <span class="badge badge-rol" style="white-space:nowrap;flex-shrink:0;"><?= htmlspecialchars(trim($dep)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <span style="color:var(--texto-sub);">—</span>
                <?php endif; ?>
            </td>
            <td style="font-size:0.82rem;">
                <?php if ($f['tiene_cuenta']): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Sí</span>
                    <?php if ($f['cuenta_email']): ?>
                    <div style="color:var(--texto-sub);font-size:0.75rem;margin-top:2px;"><?= htmlspecialchars($f['cuenta_email']) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> No</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($f['activo']): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactivo</span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;flex-direction:row;align-items:center;gap:6px;">
                    <!-- Editar -->
                    <button type="button" class="action-btn" title="Editar"
                        onclick="abrirEditar(this)">
                        <i class="fas fa-pen"></i>
                    </button>

                    <!-- Toggle activo -->
                    <form method="POST" action="/admin/funcionarios" style="display:contents;">
                        <?= csrf_field() ?>
                        <?= tab_id_field() ?>
                        <input type="hidden" name="accion"          value="toggle_activo">
                        <input type="hidden" name="id_funcionario"  value="<?= (int)$f['id_funcionario'] ?>">
                        <input type="hidden" name="activo"          value="<?= $f['activo'] ? '0' : '1' ?>">
                        <button type="submit" class="action-btn <?= $f['activo'] ? 'danger' : '' ?>"
                                title="<?= $f['activo'] ? 'Desactivar' : 'Activar' ?>"
                                onclick="return confirm('¿Confirmar cambio de estado?')">
                            <i class="fas <?= $f['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                        </button>
                    </form>

                    <!-- Dar acceso -->
                    <?php if (!$f['tiene_cuenta']): ?>
                    <button type="button" class="action-btn" title="Dar acceso"
                        onclick="abrirDarAcceso(this)">
                        <i class="fas fa-key"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══ Modal Dar Acceso ══ -->
<div class="modal-overlay" id="modal-dar-acceso">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-key" style="color:var(--verde-medio);margin-right:6px;"></i> Dar acceso al sistema</h3>
            <button class="modal-close" onclick="cerrarModal('modal-dar-acceso')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/funcionarios" onsubmit="return validarFormulario(this)">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="dar_acceso">
            <input type="hidden" name="id_funcionario" id="acceso-id_funcionario">
            <div class="modal-body">
                <p style="font-size:0.85rem;color:var(--texto-sub);margin:0 0 12px;">
                    Funcionario: <strong id="acceso-nombre-preview"></strong>
                </p>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="acceso-email" class="form-control" required placeholder="correo@ejemplo.com"
                           data-vrequired="1" oninput="validarInput(this)"
                           onblur="verificarEmailUsuario(this,'acceso-email-error','btn-guardar-acceso')">
                    <span id="acceso-email-error" class="field-error"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <input type="text" class="form-control" value="Funcionario" disabled>
                    <input type="hidden" name="rol" value="Funcionario">
                </div>
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:12px 14px;display:flex;gap:10px;align-items:flex-start;margin-top:4px;">
                    <i class="fas fa-envelope" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                    <p style="font-size:0.83rem;color:#166534;margin:0;line-height:1.5;">
                        Se creará una cuenta con el rol seleccionado y se enviará al correo ingresado una contraseña temporal válida por <strong>24 horas</strong>.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-dar-acceso')">Cancelar</button>
                <button type="submit" id="btn-guardar-acceso" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Editar Funcionario ══ -->
<div class="modal-overlay" id="modal-editar-func">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-pen" style="color:var(--verde-medio);margin-right:6px;"></i> Editar Funcionario</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar-func')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/funcionarios" onsubmit="return validarFormulario(this)">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion"         value="editar">
            <input type="hidden" name="id_funcionario" id="edit-id_funcionario">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" id="edit-func-nombres" class="form-control" required
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" id="edit-func-apellidos" class="form-control" required
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo ID *</label>
                        <select name="tipo_identificacion" id="edit-func-tipo_identificacion" class="form-control" required>
                            <?php foreach ($tiposId as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número ID *</label>
                        <input type="text" name="numero_identificacion" id="edit-func-numero_identificacion" class="form-control" required
                               data-vrequired="1" data-vpattern="numero_id" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="edit-func-email" class="form-control" required
                               data-vrequired="1" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="edit-func-telefono" class="form-control"
                               data-voptional="1" data-vpattern="telefono" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Cargo *</label>
                    <input type="text" name="cargo" id="edit-func-cargo" class="form-control" required
                           data-vrequired="1" data-vpattern="cargo" oninput="validarInput(this)">
                    <span class="field-error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-editar-func')">Cancelar</button>
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
    document.getElementById('edit-id_funcionario').value              = r.dataset.id;
    document.getElementById('edit-func-nombres').value               = r.dataset.nombres;
    document.getElementById('edit-func-apellidos').value             = r.dataset.apellidos;
    document.getElementById('edit-func-tipo_identificacion').value   = r.dataset.tipo;
    document.getElementById('edit-func-numero_identificacion').value = r.dataset.numero;
    document.getElementById('edit-func-email').value                 = r.dataset.email;
    document.getElementById('edit-func-telefono').value              = r.dataset.telefono || '';
    document.getElementById('edit-func-cargo').value                 = r.dataset.cargo;
    limpiarValidacion(document.getElementById('modal-editar-func').querySelector('form'));
    abrirModal('modal-editar-func');
}
function abrirDarAcceso(btn) {
    var r = btn.closest('tr');
    document.getElementById('acceso-id_funcionario').value = r.dataset.id;
    document.getElementById('acceso-email').value           = r.dataset.email || '';
    document.getElementById('acceso-nombre-preview').textContent = r.dataset.nombres + ' ' + r.dataset.apellidos;
    limpiarValidacion(document.getElementById('modal-dar-acceso').querySelector('form'));
    abrirModal('modal-dar-acceso');
}
</script>
