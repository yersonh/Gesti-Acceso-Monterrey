    </main>
</div><!-- /.main-wrap -->

<script>
// Inject tab_id into all sidebar nav links so auth_load() identifies the tab correctly
(function() {
    var tid = window.TAB_ID;
    if (!tid) return;
    document.querySelectorAll('.nav-item[href]').forEach(function(a) {
        var href = a.getAttribute('href');
        if (href && href.indexOf('tab_id') === -1) {
            a.setAttribute('href', href + (href.indexOf('?') !== -1 ? '&' : '?') + 'tab_id=' + tid);
        }
    });
})();

// Verificación de email único para formularios de creación
function verificarEmailUsuario(input, errorId, btnId) {
    var email = input.value.trim();
    var errorEl = document.getElementById(errorId);
    var btnEl   = document.getElementById(btnId);
    if (!errorEl) return;

    errorEl.style.display = 'none';
    errorEl.textContent   = '';
    input.style.borderColor = '';
    if (btnEl) btnEl.disabled = false;

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;

    fetch('/ajax/verificar_email_usuario?email=' + encodeURIComponent(email) + '&tab_id=' + (window.TAB_ID || ''))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.existe) {
                errorEl.textContent   = 'Este correo ya tiene una cuenta registrada en el sistema.';
                errorEl.style.display = 'block';
                input.style.borderColor = '#dc2626';
                if (btnEl) btnEl.disabled = true;
            } else {
                input.style.borderColor = '#16a34a';
            }
        })
        .catch(function() {});
}

// ── Validación en tiempo real de formularios ──
var _vPatterns = {
    nombres:   { re: /^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s'\-]+$/, msg: 'Solo letras y espacios.' },
    numero_id: { re: /^\d{4,20}$/, msg: 'Solo dígitos (4-20 caracteres).' },
    telefono:  { re: /^[\d\s\-\+\(\)]{7,15}$/, msg: 'Teléfono inválido (7-15 dígitos).' },
    cargo:     { re: /^.{2,120}$/, msg: 'Mínimo 2 caracteres.' }
};

function validarInput(input) {
    var val  = input.value.trim();
    var req  = input.dataset.vrequired === '1';
    var pat  = input.dataset.vpattern;
    var opt  = input.dataset.voptional === '1';
    var err  = input.parentElement.querySelector('.field-error');
    var msg  = '';

    if (req && !val) {
        msg = 'Campo requerido.';
    } else if (val && pat && _vPatterns[pat] && !_vPatterns[pat].re.test(val)) {
        msg = _vPatterns[pat].msg;
    }

    if (err) { err.textContent = msg; err.style.display = msg ? '' : 'none'; }
    if (msg) {
        input.style.borderColor = '#dc2626';
    } else if (val || (!req && !opt)) {
        input.style.borderColor = val ? '#16a34a' : '';
    } else {
        input.style.borderColor = '';
    }
    return !msg;
}

function validarFormulario(form) {
    var ok = true;
    form.querySelectorAll('[data-vrequired],[data-vpattern]').forEach(function(inp) {
        if (!validarInput(inp)) ok = false;
    });
    return ok;
}

function limpiarValidacion(form) {
    form.querySelectorAll('[data-vrequired],[data-vpattern]').forEach(function(inp) {
        inp.style.borderColor = '';
        var err = inp.parentElement.querySelector('.field-error');
        if (err) { err.textContent = ''; err.style.display = 'none'; }
    });
}

// ── Filtro genérico de tabla (cliente-side) ──
function filtrarTabla(cfg) {
    // cfg: { tbodyId, buscarId, filtros: [{selectId, attr}], contadorId }
    var tbody    = document.getElementById(cfg.tbodyId);
    var buscarEl = cfg.buscarId ? document.getElementById(cfg.buscarId) : null;
    var buscar   = buscarEl ? buscarEl.value.toLowerCase().trim() : '';
    var filtros  = (cfg.filtros || []).map(function(f) {
        var el = document.getElementById(f.selectId);
        return { val: el ? el.value : '', attr: f.attr };
    });

    if (!tbody) return;
    var visible = 0;
    tbody.querySelectorAll('tr').forEach(function(fila) {
        var texto    = fila.textContent.toLowerCase();
        var coincide = !buscar || texto.includes(buscar);
        filtros.forEach(function(f) {
            if (f.val !== '') {
                coincide = coincide && (fila.dataset[f.attr] === f.val);
            }
        });
        fila.style.display = coincide ? '' : 'none';
        if (coincide) visible++;
    });

    if (cfg.contadorId) {
        var total = tbody.querySelectorAll('tr').length;
        var el = document.getElementById(cfg.contadorId);
        if (el) el.textContent = visible + (visible !== total ? ' de ' + total : '') + ' registro(s)';
    }
}

// Funciones globales de modal
function abrirModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('open');
}
function cerrarModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
}
// ── Toggle activo/inactivo via AJAX (sin reload de página) ──
var _toggleBtn = null; // referencia al botón que abrió el modal

function abrirModalToggle(btn) {
    var row    = btn.closest('tr');
    var id     = row.dataset.uid;
    var nombre = row.dataset.nombre;
    var activo = row.dataset.activo === '1';
    _toggleBtn = btn;

    var body  = document.getElementById('toggle-modal-body');
    var title = document.getElementById('toggle-modal-title');
    var ok    = document.getElementById('btn-confirmar-toggle');
    if (!body) return;

    if (activo) {
        title.innerHTML = '<i class="fas fa-user-slash" style="color:#ef4444;margin-right:6px;"></i> Desactivar usuario';
        body.innerHTML =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">El usuario <strong>' + nombre + '</strong> no podrá iniciar sesión hasta ser reactivado.</p>' +
            '<div style="background:#fff1f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#991b1b;">' +
                '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>¿Confirmar desactivación?' +
            '</div>';
        ok.className = 'btn-danger';
        ok.innerHTML = '<i class="fas fa-user-slash"></i> Desactivar';
    } else {
        title.innerHTML = '<i class="fas fa-user-check" style="color:var(--verde-medio);margin-right:6px;"></i> Activar usuario';
        body.innerHTML =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">El usuario <strong>' + nombre + '</strong> podrá iniciar sesión nuevamente.</p>' +
            '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:0.85rem;color:#166534;">' +
                '<i class="fas fa-check-circle" style="margin-right:6px;"></i>¿Confirmar activación?' +
            '</div>';
        ok.className = 'btn-primary';
        ok.innerHTML = '<i class="fas fa-user-check"></i> Activar';
    }

    ok.onclick = function() { ejecutarToggle(id, !activo); };
    abrirModal('modal-toggle-usuario');
}

function ejecutarToggle(id, nuevoActivo) {
    var ok = document.getElementById('btn-confirmar-toggle');
    ok.disabled = true;

    var body = new FormData();
    body.append('accion',     'toggle_activo');
    body.append('usuario_id', id);
    body.append('activo',     nuevoActivo ? '1' : '0');
    body.append('csrf_token', window.CSRF_TOKEN || '');
    body.append('tab_id',     window.TAB_ID   || '');

    fetch('/ajax/admin_usuarios', { method: 'POST', body: body })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                actualizarFilaToggle(id, nuevoActivo);
                cerrarModal('modal-toggle-usuario');
                mostrarToast(data.mensaje, nuevoActivo ? 'ok' : 'warn');
            } else {
                alert(data.error);
            }
            ok.disabled = false;
        })
        .catch(function() {
            alert('Error de conexión. Intenta nuevamente.');
            ok.disabled = false;
        });
}

function actualizarFilaToggle(id, activo) {
    var row = document.querySelector('tr[data-uid="' + id + '"]');
    if (!row) return;

    row.dataset.activo = activo ? '1' : '0';

    // Badge estado
    var badge = row.querySelector('.badge-activo, .badge-inactivo');
    if (badge) {
        badge.className = 'badge ' + (activo ? 'badge-activo' : 'badge-inactivo');
        badge.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem"></i> ' + (activo ? 'Activo' : 'Inactivo');
    }

    // Botón toggle
    var toggleBtn = row.querySelector('.toggle-btn');
    if (toggleBtn) {
        toggleBtn.className = 'action-btn toggle-btn' + (activo ? ' danger-soft' : '');
        toggleBtn.title = activo ? 'Desactivar usuario' : 'Activar usuario';
        toggleBtn.querySelector('i').className = 'fas ' + (activo ? 'fa-user-slash' : 'fa-user-check');
    }

    // Botón reset (advertencia si inactivo)
    var resetBtn = row.querySelector('.reset-btn');
    if (resetBtn) {
        if (activo) {
            resetBtn.classList.remove('warn');
            resetBtn.title = 'Restablecer contraseña';
        } else {
            resetBtn.classList.add('warn');
            resetBtn.title = 'Usuario inactivo — no se puede restablecer';
        }
    }

    // KPIs
    var kpiActivos   = document.querySelector('.kpi-card.azul .kpi-value');
    var kpiInactivos = document.querySelector('.kpi-card.gris .kpi-value');
    if (kpiActivos && kpiInactivos) {
        var a = parseInt(kpiActivos.textContent) || 0;
        var i = parseInt(kpiInactivos.textContent) || 0;
        kpiActivos.textContent   = activo ? a + 1 : a - 1;
        kpiInactivos.textContent = activo ? i - 1 : i + 1;
    }
}

function mostrarToast(msg, tipo) {
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:28px;right:28px;z-index:9999;padding:12px 20px;border-radius:10px;font-size:0.87rem;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,0.15);transition:opacity 0.4s;display:flex;align-items:center;gap:8px;';
    if (tipo === 'ok') {
        t.style.background = '#d1fae5'; t.style.color = '#065f46'; t.style.border = '1px solid #6ee7b7';
        t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    } else {
        t.style.background = '#fffbeb'; t.style.color = '#92400e'; t.style.border = '1px solid #fcd34d';
        t.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
    }
    document.body.appendChild(t);
    setTimeout(function() { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 400); }, 2800);
}

function abrirModalReset(btn) {
    var row    = btn.closest('tr');
    var id     = row.dataset.uid;
    var email  = row.dataset.email;
    var nombre = row.dataset.nombre;
    var activo = row.dataset.activo === '1';

    document.getElementById('reset-usuario-id').value = id;
    var body  = document.getElementById('reset-modal-body');
    var btnOk = document.getElementById('btn-confirmar-reset');
    if (!body) return;

    if (activo) {
        body.innerHTML =
            '<p style="margin-bottom:14px;color:#475569;font-size:0.9rem;">Se enviará un enlace de restablecimiento de 24 horas al correo del usuario:</p>' +
            '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:4px;">' +
                '<p style="font-weight:600;color:#1e293b;margin-bottom:2px;">' + nombre + '</p>' +
                '<p style="font-size:0.85rem;color:#64748b;">' + email + '</p>' +
            '</div>';
        btnOk.style.display = '';
        btnOk.disabled = false;
    } else {
        body.innerHTML =
            '<div style="background:#fffbeb;border:1.5px solid #f59e0b;border-radius:10px;padding:14px 16px;display:flex;gap:12px;align-items:flex-start;">' +
                '<i class="fas fa-exclamation-triangle" style="color:#d97706;margin-top:2px;flex-shrink:0;"></i>' +
                '<div>' +
                    '<p style="font-weight:600;color:#92400e;margin-bottom:4px;">Usuario inactivo</p>' +
                    '<p style="font-size:0.87rem;color:#78350f;">No es posible enviar un enlace de restablecimiento a una cuenta inactiva. Activa el usuario primero y vuelve a intentarlo.</p>' +
                '</div>' +
            '</div>';
        btnOk.style.display = 'none';
    }
    abrirModal('modal-reset-password');
}

function abrirEditarUsuario(btn) {
    var row = btn.closest('tr');
    var elId    = document.getElementById('edit-u-id');
    var elEmail = document.getElementById('edit-u-email');
    var elRol   = document.getElementById('edit-u-rol');
    if (!elId) return;
    elId.value    = row.dataset.uid;
    elEmail.value = row.dataset.email;
    elRol.value   = row.dataset.rol;
    abrirModal('modal-editar-usuario');
}

// Close modals on Escape or outside click
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
            m.classList.remove('open');
        });
    }
});
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});
</script>
</body>
</html>
