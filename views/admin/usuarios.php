<?php
/** @var array $usuarios */
/** @var array $kpis */
$usuarios        ??= [];
$kpis            ??= [];
$rolesPermitidos   = ['Administrador', 'Recepcionista', 'Funcionario', 'Ciudadano'];
$miId              = (int)($_SESSION['usuario_id'] ?? 0);
?>

<style>
.main { padding-top: calc(var(--header-h) + 12px) !important; }
.page-header { padding-bottom: 8px; margin-bottom: 12px; }
.kpi-grid { gap: 10px; margin-bottom: 12px; }
.kpi-card { padding: 10px 14px; }
.kpi-label { font-size: 0.75rem; }
.kpi-value { font-size: 1.3rem; }
.table-toolbar { padding: 8px 16px; }
thead th { padding: 7px 14px; }
tbody td { padding: 5px 12px; }
</style>

<div class="page-header">
    <div>
        <h2>Usuarios</h2>
        <p>Gestión de cuentas de acceso al sistema</p>
    </div>
    <button class="btn-primary" onclick="abrirModal('modal-nuevo-usuario')">
        <i class="fas fa-plus"></i> Nuevo administrador
    </button>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card verde">
        <div class="kpi-label"><i class="fas fa-users"></i> Total</div>
        <div class="kpi-value"><?= (int)($kpis['total'] ?? 0) ?></div>
    </div>
    <div class="kpi-card azul">
        <div class="kpi-label"><i class="fas fa-user-check"></i> Activos</div>
        <div class="kpi-value"><?= (int)($kpis['activos'] ?? 0) ?></div>
    </div>
    <div class="kpi-card gris">
        <div class="kpi-label"><i class="fas fa-user-slash"></i> Inactivos</div>
        <div class="kpi-value"><?= (int)($kpis['inactivos'] ?? 0) ?></div>
    </div>
    <div class="kpi-card dorado">
        <div class="kpi-label"><i class="fas fa-user-tie"></i> Funcionarios</div>
        <div class="kpi-value"><?= (int)($kpis['funcionarios'] ?? 0) ?></div>
    </div>
</div>

<!-- Tabla -->
<div class="table-card">
    <div class="table-toolbar" style="flex-wrap:wrap;gap:10px;">
        <span id="usu-contador" style="font-size:0.82rem;color:var(--texto-sub);">
            <?= count($usuarios) ?> registro(s)
        </span>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;">
            <input type="text" id="usu-buscar" class="filter-input" placeholder="Buscar nombre o email…" style="width:200px;"
                   oninput="filtrarTabla({tbodyId:'usu-tbody',buscarId:'usu-buscar',filtros:[{selectId:'usu-rol',attr:'rol'},{selectId:'usu-estado',attr:'activo'}],contadorId:'usu-contador'})">
            <select id="usu-rol" class="filter-input" style="width:140px;"
                    onchange="filtrarTabla({tbodyId:'usu-tbody',buscarId:'usu-buscar',filtros:[{selectId:'usu-rol',attr:'rol'},{selectId:'usu-estado',attr:'activo'}],contadorId:'usu-contador'})">
                <option value="">Todos los roles</option>
                <option value="Superadmin">Superadmin</option>
                <option value="Administrador">Administrador</option>
                <option value="Recepcionista">Recepcionista</option>
                <option value="Funcionario">Funcionario</option>
                <option value="Ciudadano">Ciudadano</option>
            </select>
            <select id="usu-estado" class="filter-input" style="width:120px;"
                    onchange="filtrarTabla({tbodyId:'usu-tbody',buscarId:'usu-buscar',filtros:[{selectId:'usu-rol',attr:'rol'},{selectId:'usu-estado',attr:'activo'}],contadorId:'usu-contador'})">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>

    <?php if (empty($usuarios)): ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h4>Sin usuarios</h4>
        <p>No hay usuarios registrados en el sistema.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Último acceso</th>
                <th>Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="usu-tbody">
        <?php foreach ($usuarios as $u):
            $activo = ($u['activo'] === 't' || $u['activo'] === true || $u['activo'] === 1 || $u['activo'] === '1');
            try { ?>
        <tr data-uid="<?= (int)$u['id_usuario'] ?>"
            data-activo="<?= $activo ? '1' : '0' ?>"
            data-nombre="<?= htmlspecialchars($u['nombre_perfil'] ?? '', ENT_QUOTES) ?>"
            data-email="<?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?>"
            data-rol="<?= htmlspecialchars($u['rol'] ?? '', ENT_QUOTES) ?>">
            <td style="color:var(--texto-sub);font-size:0.82rem;"><?= (int)$u['id_usuario'] ?></td>
            <td><strong><?= htmlspecialchars($u['nombre_perfil'] ?? '') ?></strong></td>
            <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email'] ?? '') ?></td>
            <td>
                <span class="badge badge-rol"><?= htmlspecialchars($u['rol'] ?? '') ?></span>
            </td>
            <td>
                <?php if ($activo): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activo</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactivo</span>
                <?php endif; ?>
            </td>
            <td style="font-size:0.82rem;color:var(--texto-sub);white-space:nowrap;">
                <?php if ($u['last_login_fmt']): ?>
                    <i class="fas fa-clock" style="font-size:0.7rem;margin-right:3px;"></i>
                    <?= htmlspecialchars($u['last_login_fmt']) ?>
                <?php else: ?>
                    <span style="color:#cbd5e1;">Nunca</span>
                <?php endif; ?>
            </td>
            <td style="color:var(--texto-sub);font-size:0.82rem;white-space:nowrap;">
                <?= htmlspecialchars($u['fecha_registro_fmt'] ?? '') ?>
            </td>
            <td>
                <?php if (($u['rol'] ?? '') === 'Superadmin'): ?>
                <span style="font-size:0.75rem;color:var(--texto-sub);display:flex;align-items:center;gap:5px;">
                    <i class="fas fa-lock" style="font-size:0.7rem;"></i> Solo lectura
                </span>
                <?php else: ?>
                <div style="display:flex;align-items:center;gap:6px;">

                    <!-- Editar -->
                    <button type="button" class="action-btn" title="Editar email y rol"
                            onclick="abrirEditarUsuario(this)">
                        <i class="fas fa-pen"></i>
                    </button>

                    <!-- Resetear contraseña -->
                    <button type="button"
                            class="action-btn reset-btn <?= !$activo ? 'warn' : '' ?>"
                            title="<?= $activo ? 'Restablecer contraseña' : 'Usuario inactivo — no se puede restablecer' ?>"
                            onclick="abrirModalReset(this)">
                        <i class="fas fa-key"></i>
                    </button>

                    <!-- Toggle activo -->
                    <?php if ((int)$u['id_usuario'] !== $miId): ?>
                    <button type="button"
                            class="action-btn toggle-btn <?= $activo ? 'danger-soft' : '' ?>"
                            title="<?= $activo ? 'Desactivar usuario' : 'Activar usuario' ?>"
                            onclick="abrirModalToggle(this)">
                        <i class="fas <?= $activo ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                    </button>
                    <?php else: ?>
                    <button type="button" class="action-btn toggle-btn" disabled
                            title="No puedes desactivar tu propia cuenta"
                            style="opacity:0.35;cursor:not-allowed;">
                        <i class="fas fa-user-slash"></i>
                    </button>
                    <?php endif; ?>

                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php } catch (\Throwable $e) {
            error_log('AdminUsuarios fila uid=' . ($u['id_usuario'] ?? '?') . ': ' . $e->getMessage());
        } endforeach; ?>
        <?php
        // Si no se renderizaron todas las filas, loguear el conteo real
        $filas_esperadas = count($usuarios);
        ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<script>
// Auto-retry si el DOM tiene menos filas de las esperadas (catch silenciosos o truncado HTTP)
(function() {
    var esperadas = <?= $filas_esperadas ?? 0 ?>;
    var renderizadas = document.querySelectorAll('tbody tr[data-uid]').length;
    if (esperadas > 0 && renderizadas < esperadas) {
        if (!sessionStorage.getItem('_usr_retry')) {
            sessionStorage.setItem('_usr_retry', '1');
            location.reload();
        } else {
            sessionStorage.removeItem('_usr_retry');
            console.error('AdminUsuarios: filas esperadas=' + esperadas + ', renderizadas=' + renderizadas);
        }
    } else {
        sessionStorage.removeItem('_usr_retry');
    }
})();
</script>

<!-- ══ Modal Toggle Activo ══ -->
<div class="modal-overlay" id="modal-toggle-usuario">
    <div class="modal-card" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="toggle-modal-title"><i class="fas fa-user-slash" style="color:var(--verde-medio);margin-right:6px;"></i> Cambiar estado</h3>
            <button class="modal-close" onclick="cerrarModal('modal-toggle-usuario')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="toggle-modal-body"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="cerrarModal('modal-toggle-usuario')">Cancelar</button>
            <button type="button" id="btn-confirmar-toggle" class="btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<!-- ══ Modal Resetear Contraseña ══ -->
<div class="modal-overlay" id="modal-reset-password">
    <div class="modal-card" style="max-width:420px;">
        <div class="modal-header">
            <h3><i class="fas fa-key" style="color:var(--verde-medio);margin-right:6px;"></i> Restablecer Contraseña</h3>
            <button class="modal-close" onclick="cerrarModal('modal-reset-password')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="reset-modal-body"></div>
        <div class="modal-footer" id="reset-modal-footer">
            <button type="button" class="btn-secondary" onclick="cerrarModal('modal-reset-password')">Cancelar</button>
            <button type="submit" form="form-reset-password" class="btn-primary" id="btn-confirmar-reset">
                <i class="fas fa-paper-plane"></i> Enviar enlace
            </button>
        </div>
    </div>
</div>

<!-- Formulario oculto para el submit -->
<form id="form-reset-password" method="POST" action="/admin/usuarios" style="display:none;">
    <?= csrf_field() ?>
    <?= tab_id_field() ?>
    <input type="hidden" name="accion"     value="resetear_password">
    <input type="hidden" name="usuario_id" id="reset-usuario-id">
</form>

<!-- ══ Modal Editar Usuario ══ -->
<div class="modal-overlay" id="modal-editar-usuario">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-user-pen" style="color:var(--verde-medio);margin-right:6px;"></i> Editar Usuario</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar-usuario')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/usuarios">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion"     value="editar_usuario">
            <input type="hidden" name="usuario_id" id="edit-u-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="edit-u-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="rol" id="edit-u-rol" class="form-control" required>
                        <?php foreach ($rolesPermitidos as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-editar-usuario')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal Nuevo Usuario ══ -->
<div class="modal-overlay" id="modal-nuevo-usuario">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="color:var(--verde-medio);margin-right:6px;"></i> Nuevo Administrador</h3>
            <button class="modal-close" onclick="cerrarModal('modal-nuevo-usuario')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/usuarios" onsubmit="return validarFormulario(this)">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="accion" value="crear_usuario">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" required placeholder="Ej: María"
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" required placeholder="Ej: López"
                               data-vrequired="1" data-vpattern="nombres" oninput="validarInput(this)">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo ID *</label>
                        <select name="tipo_identificacion" class="form-control" required>
                            <?php foreach (['CC','TI','CE','PA','NIT','RC'] as $t): ?>
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
                        <input type="email" name="email" id="nuevo-usu-email" class="form-control" required
                               placeholder="correo@ejemplo.com"
                               data-vrequired="1" oninput="validarInput(this)"
                               onblur="verificarEmailUsuario(this,'nuevo-usu-email-error','btn-guardar-nuevo-usu')">
                        <span id="nuevo-usu-email-error" class="field-error"></span>
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
                <input type="hidden" name="rol" value="Administrador">
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:12px 14px;display:flex;gap:10px;align-items:flex-start;margin-top:4px;">
                    <i class="fas fa-envelope" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                    <p style="font-size:0.83rem;color:#166534;margin:0;line-height:1.5;">
                        Se creará un registro en Personal y se enviará al correo una contraseña temporal válida por <strong>24 horas</strong>.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modal-nuevo-usuario')">Cancelar</button>
                <button type="submit" id="btn-guardar-nuevo-usu" class="btn-primary">
                    <i class="fas fa-save"></i> Crear usuario
                </button>
            </div>
        </form>
    </div>
</div>

