<?php
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Superadmin') {
    header('Location: /login');
    exit;
}

$nombreAdmin    = $_SESSION['usuario_nombre'] ?? 'SuperAdmin';
$partes         = explode(' ', trim($nombreAdmin));
$iniciales      = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primerNombre   = $partes[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Gestión de SuperAdmins</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde:        #1a5c38;
            --verde-medio:  #2d7a4f;
            --verde-claro:  #3d9e68;
            --dorado:       #c9a84c;
            --dorado-claro: #e8c97a;
            --blanco:       #ffffff;
            --texto:        #eef3ee;
            --texto-sub:    rgba(238, 243, 238, 0.6);
            --borde:        rgba(255, 255, 255, 0.12);
            --fondo:        rgba(0, 0, 0, 0.22);
            --vidrio:       rgba(20, 34, 26, 0.45);
            --danger:       #ef4444;
            --success:      #10b981;
            --radius:       14px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        html, body { height: 100%; }

        body {
            font-family:'DM Sans',sans-serif; color:var(--texto); min-height:100vh;

            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.6) 0%, rgba(6, 14, 10, 0.78) 55%, rgba(6, 14, 10, 0.9) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* ── Header ── */
        .header {
            background:rgba(15, 53, 34, 0.6);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            padding:0 32px; height:64px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:100;
            box-shadow:0 4px 20px rgba(0,0,0,0.25);
            border-bottom: 1px solid rgba(201,168,76,0.2);
        }
        .header::after {
            content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
            background:linear-gradient(to right,var(--verde),var(--dorado),var(--verde-claro));
        }
        .header-brand { display:flex; align-items:center; gap:14px; text-decoration:none; }
        .header-brand img { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--dorado); }
        .header-brand-texto { color:var(--blanco); font-size:0.8rem; font-weight:500; letter-spacing:0.08em; text-transform:uppercase; opacity:0.9; line-height:1.4; }
        .header-brand-texto strong { display:block; font-size:0.92rem; color:var(--dorado-claro); letter-spacing:0.04em; }
        .header-nav { display:flex; align-items:center; gap:8px; }
        .nav-link {
            color:rgba(255,255,255,0.75); text-decoration:none; padding:6px 14px;
            border-radius:8px; font-size:0.85rem; font-weight:500;
            transition:all 0.2s; display:flex; align-items:center; gap:6px;
        }
        .nav-link:hover { background:rgba(255,255,255,0.1); color:var(--blanco); }
        .nav-link.activo { background:rgba(255,255,255,0.15); color:var(--blanco); }
        .header-usuario { display:flex; align-items:center; gap:12px; }
        .avatar {
            width:38px; height:38px; border-radius:50%;
            background:rgba(201,168,76,0.25); border:2px solid var(--dorado);
            color:var(--dorado-claro); font-size:0.85rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .header-rol {
            background:rgba(201,168,76,0.2); border:1px solid rgba(201,168,76,0.4);
            color:var(--dorado-claro); padding:3px 10px; border-radius:20px;
            font-size:0.72rem; font-weight:600; letter-spacing:0.08em; text-transform:uppercase;
        }
        .btn-logout {
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
            color:var(--blanco); padding:7px 14px; border-radius:8px;
            font-family:'DM Sans',sans-serif; font-size:0.82rem; font-weight:500;
            cursor:pointer; display:flex; align-items:center; gap:6px;
            text-decoration:none; transition:all 0.2s;
        }
        .btn-logout:hover { background:rgba(255,255,255,0.2); }

        /* ── Main ── */
        .main { max-width:1100px; margin:0 auto; padding:36px 24px 60px; }

        .bienvenida { margin-bottom:28px; }
        .bienvenida h1 { font-family:'Playfair Display',serif; font-size:1.9rem; font-weight:700; margin-bottom:4px; color:var(--texto); }
        .bienvenida h1 span { color:var(--dorado-claro); }
        .bienvenida p { color:var(--texto-sub); font-size:0.9rem; font-weight:300; }

        /* ── Botón nuevo ── */
        .btn-nuevo {
            display:inline-flex; align-items:center; gap:8px;
            background:linear-gradient(135deg, var(--dorado-claro), var(--dorado)); color:#2c2107; border:none;
            padding:10px 20px; border-radius:10px; font-family:'DM Sans',sans-serif;
            font-size:0.9rem; font-weight:700; cursor:pointer;
            transition:all 0.2s; text-decoration:none;
            margin-bottom:24px; box-shadow: 0 4px 14px rgba(201,168,76,0.2);
        }
        .btn-nuevo:hover { transform:translateY(-1px); box-shadow: 0 8px 22px rgba(201,168,76,0.35); }
        .btn-nuevo:active { transform:scale(0.97); }

        /* ── Tabla de superadmins ── */
        .panel {
            background:var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border:1px solid var(--borde);
            border-radius:var(--radius); overflow:hidden;
            box-shadow:0 8px 24px rgba(0,0,0,0.25); margin-bottom:24px;
        }

        .panel-header {
            padding:1rem 1.5rem; border-bottom:1px solid var(--borde);
            display:flex; align-items:center; justify-content:space-between;
        }

        .panel-header h2 {
            font-size:0.95rem; font-weight:600; color:var(--texto);
            display:flex; align-items:center; gap:8px;
        }

        .panel-icon {
            width:30px; height:30px; border-radius:8px;
            background:rgba(201,168,76,0.18); color:var(--dorado-claro);
            display:flex; align-items:center; justify-content:center; font-size:0.8rem;
        }

        .tabla-wrapper { overflow-x:auto; }

        table { width:100%; border-collapse:collapse; font-size:0.85rem; }
        thead th {
            background:rgba(0,0,0,0.2); padding:0.65rem 1rem; text-align:left;
            font-size:0.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:0.08em; color:var(--texto-sub); border-bottom:1px solid var(--borde);
        }
        tbody tr { border-bottom:1px solid rgba(255,255,255,0.06); transition:background 0.15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:rgba(255,255,255,0.04); }
        tbody td { padding:0.75rem 1rem; color:var(--texto); vertical-align:middle; }

        .badge-activo   { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; background:rgba(52,211,153,0.16); color:#6ee7b7; border:1px solid rgba(110,231,183,0.3); }
        .badge-inactivo { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; background:rgba(248,113,113,0.16); color:#fca5a5; border:1px solid rgba(252,165,165,0.3); }

        .acciones { display:flex; gap:6px; }
        .btn-accion {
            padding:5px 10px; border-radius:6px; border:1.5px solid var(--borde);
            background:rgba(255,255,255,0.06); color:var(--texto); font-family:'DM Sans',sans-serif;
            font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s;
            display:flex; align-items:center; gap:4px;
        }
        .btn-accion.toggle:hover { border-color:var(--dorado); color:var(--dorado-claro); background:rgba(201,168,76,0.1); }
        .btn-accion.reset:hover  { border-color:#f59e0b; color:#fcd34d; background:rgba(251,191,36,0.1); }

        .empty-state { text-align:center; padding:3rem; color:var(--texto-sub); }
        .empty-state i { font-size:2rem; color:rgba(255,255,255,0.25); display:block; margin-bottom:10px; }

        .loading { text-align:center; padding:2rem; color:var(--texto-sub); }
        .spinner { display:inline-block; width:20px; height:20px; border:2px solid var(--borde); border-top-color:var(--dorado); border-radius:50%; animation:spin 0.7s linear infinite; margin-right:8px; vertical-align:middle; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ── Modal ── */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.6); z-index:200;
            align-items:center; justify-content:center; backdrop-filter:blur(4px);
        }
        .modal-overlay.activo { display:flex; }
        .modal {
            background:rgba(20, 34, 26, 0.75);
            backdrop-filter: blur(20px) saturate(140%);
            -webkit-backdrop-filter: blur(20px) saturate(140%);
            border:1px solid var(--borde);
            border-radius:16px; padding:32px;
            width:100%; max-width:520px; margin:20px;
            animation:subirEntrar 0.3s ease both; max-height:90vh; overflow-y:auto;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        }
        .modal h3 { font-family:'Playfair Display',serif; font-size:1.3rem; margin-bottom:6px; color:var(--texto); }
        .modal p.sub { color:var(--texto-sub); font-size:0.875rem; margin-bottom:24px; font-weight:300; }

        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px; }
        .form-group { display:flex; flex-direction:column; gap:5px; }
        .form-group.full { grid-column:1/-1; }
        .form-group label { font-size:0.78rem; font-weight:600; color:var(--texto-sub); }
        .form-group input,
        .form-group select {
            padding:0.55rem 0.75rem; border:1.5px solid var(--borde);
            border-radius:8px; font-family:'DM Sans',sans-serif; font-size:0.875rem;
            color:var(--texto); background:rgba(0,0,0,0.28); outline:none; transition:border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus { border-color:var(--dorado); box-shadow: 0 0 0 3px rgba(201,168,76,0.15); }
        .form-group input::placeholder { color:rgba(255,255,255,0.35); }
        .form-group select option { background:#14221a; color:var(--texto); }

        .modal-acciones { display:flex; gap:10px; justify-content:flex-end; }
        .btn-cancelar-modal {
            padding:10px 20px; border:1.5px solid var(--borde); background:rgba(255,255,255,0.06);
            color:var(--texto-sub); border-radius:8px; font-family:'DM Sans',sans-serif;
            font-size:0.88rem; font-weight:500; cursor:pointer; transition:all 0.2s;
        }
        .btn-cancelar-modal:hover { border-color:rgba(255,255,255,0.3); color:var(--texto); }
        .btn-confirmar-modal {
            padding:10px 24px; background:linear-gradient(135deg, var(--dorado-claro), var(--dorado)); border:none; color:#2c2107;
            border-radius:8px; font-family:'DM Sans',sans-serif; font-size:0.88rem;
            font-weight:700; cursor:pointer; transition:all 0.2s;
            display:flex; align-items:center; gap:6px;
            box-shadow: 0 4px 14px rgba(201,168,76,0.2);
        }
        .btn-confirmar-modal:hover { transform:translateY(-1px); box-shadow: 0 8px 22px rgba(201,168,76,0.35); }
        .btn-confirmar-modal:disabled { opacity:0.6; cursor:not-allowed; transform:none; box-shadow:none; }

        /* ── Modal reset password ── */
        .modal-reset input { width:100%; }

        /* ── Alerta toast ── */
        .toast {
            position:fixed; bottom:24px; right:24px; z-index:300;
            padding:14px 20px; border-radius:10px; font-size:0.875rem; font-weight:500;
            display:flex; align-items:center; gap:10px; box-shadow:0 4px 20px rgba(0,0,0,0.35);
            animation:subirEntrar 0.3s ease both; max-width:360px;
            background: rgba(20, 34, 26, 0.75);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border: 1px solid var(--borde);
        }
        .toast-ok    { color:#6ee7b7; border-left:4px solid #34d399; }
        .toast-error { color:#fca5a5; border-left:4px solid #ef4444; }

        @keyframes subirEntrar { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

        @media (max-width:768px) {
            .header { padding:0 16px; }
            .main   { padding:20px 14px 48px; }
            .form-grid { grid-template-columns:1fr; }
            .header-nav .nav-link span { display:none; }
            .bienvenida h1 { font-size: 1.5rem; }
            .header-rol { display:none; }
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
        }
        @media (max-width:480px) {
            .header-brand-texto { display:none; }
            .main { padding:16px 12px 48px; }
            .bienvenida h1 { font-size: 1.3rem; }
            .tabla-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 480px; }
            .modal-box { padding: 24px 16px; margin: 12px; }
            .btn-nuevo { width: 100%; justify-content: center; font-size: 0.88rem; }
        }
    </style>
</head>
<body>

<header class="header">
    <a href="/superadmin/reportes" class="header-brand">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="header-brand-texto">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </a>
    <nav class="header-nav">
        <a href="/superadmin/reportes" class="nav-link">
            <i class="fas fa-chart-bar"></i> <span>Reportes</span>
        </a>
        <a href="/superadmin/usuarios" class="nav-link activo">
            <i class="fas fa-users-cog"></i> <span>Usuarios</span>
        </a>
    </nav>
    <div class="header-usuario">
        <div class="avatar"><?= htmlspecialchars($iniciales) ?></div>
        <span class="header-rol">SuperAdmin</span>
        <a href="/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</header>

<main class="main">

    <div class="bienvenida">
        <h1>Gestión de <span>SuperAdmins</span></h1>
        <p>Crea y administra las cuentas con acceso total al sistema</p>
    </div>

    <button class="btn-nuevo" onclick="abrirModalCrear()">
        <i class="fas fa-plus"></i> Nuevo SuperAdmin
    </button>

    <div class="panel">
        <div class="panel-header">
            <h2>
                <div class="panel-icon"><i class="fas fa-users-cog"></i></div>
                SuperAdministradores registrados
            </h2>
            <span id="contador" style="font-size:0.8rem;color:var(--texto-sub)"></span>
        </div>
        <div class="tabla-wrapper">
            <div class="loading" id="loading-tabla">
                <span class="spinner"></span> Cargando...
            </div>
            <table id="tabla-admins" style="display:none">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Identificación</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-admins"></tbody>
            </table>
        </div>
    </div>

</main>

<!-- ── Modal Crear SuperAdmin ───────────────────────────────────────────────── -->
<div class="modal-overlay" id="modal-crear">
    <div class="modal">
        <h3>Nuevo SuperAdmin</h3>
        <p class="sub">Completa los datos para crear una cuenta con acceso total al sistema.</p>

        <div class="form-grid">
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" id="c-nombres" placeholder="Ej: Carlos Andrés">
            </div>
            <div class="form-group">
                <label>Apellidos *</label>
                <input type="text" id="c-apellidos" placeholder="Ej: Moreno Ríos">
            </div>
            <div class="form-group">
                <label>Tipo de identificación *</label>
                <select id="c-tipo-id">
                    <option value="">Seleccionar...</option>
                    <option value="CC">Cédula de ciudadanía</option>
                    <option value="CE">Cédula de extranjería</option>
                    <option value="PA">Pasaporte</option>
                    <option value="TI">Tarjeta de identidad</option>
                </select>
            </div>
            <div class="form-group">
                <label>Número de identificación *</label>
                <input type="text" id="c-numero-id" placeholder="Ej: 1234567890">
            </div>
            <div class="form-group full">
                <label>Email *</label>
                <input type="email" id="c-email" placeholder="correo@alcaldia.gov.co">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" id="c-telefono" placeholder="Ej: 3001234567">
            </div>
            <div class="form-group">
                <label>Rol *</label>
                <select id="c-rol">
                    <option value="">Seleccionar...</option>
                    <option value="Superadmin">SuperAdmin</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <input type="text" id="c-cargo" placeholder="Ej: Administrador del Sistema">
            </div>
            <div class="form-group full">
                <label>Contraseña * (mínimo 8 caracteres)</label>
                <input type="password" id="c-password" placeholder="••••••••">
            </div>
        </div>

        <div class="modal-acciones">
            <button class="btn-cancelar-modal" onclick="cerrarModales()">Cancelar</button>
            <button class="btn-confirmar-modal" id="btn-crear" onclick="crearSuperAdmin()">
                <i class="fas fa-plus"></i> Crear SuperAdmin
            </button>
        </div>
    </div>
</div>

<!-- ── Modal Reset Password ─────────────────────────────────────────────────── -->
<div class="modal-overlay modal-reset" id="modal-reset">
    <div class="modal">
        <h3>Restablecer contraseña</h3>
        <p class="sub" id="reset-nombre-label">Ingresa la nueva contraseña para este usuario.</p>
        <input type="hidden" id="reset-usuario-id">
        <div class="form-group" style="margin-bottom:20px">
            <label>Nueva contraseña * (mínimo 8 caracteres)</label>
            <input type="password" id="reset-password" placeholder="••••••••" style="width:100%">
        </div>
        <div class="modal-acciones">
            <button class="btn-cancelar-modal" onclick="cerrarModales()">Cancelar</button>
            <button class="btn-confirmar-modal" onclick="confirmarReset()">
                <i class="fas fa-key"></i> Restablecer
            </button>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
const TAB_ID = window.TAB_ID || '';

// ════════════════════════════════════════════════════════════════════
//  Init
// ════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => cargarAdmins());

// ════════════════════════════════════════════════════════════════════
//  Cargar lista
// ════════════════════════════════════════════════════════════════════
async function cargarAdmins() {
    try {
        const res  = await fetch('/ajax/superadmin_usuarios?accion=listar&tab_id=' + TAB_ID);
        const json = await res.json();

        document.getElementById('loading-tabla').style.display = 'none';
        document.getElementById('tabla-admins').style.display  = 'table';

        if (!json.ok) throw new Error(json.error);

        const data = json.data;
        document.getElementById('contador').textContent =
            `${data.length} usuario${data.length !== 1 ? 's' : ''}`;

        const tbody = document.getElementById('tbody-admins');

        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">
                <i class="fas fa-users-slash"></i>
                No hay SuperAdmins registrados aún.
            </td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(u => `
            <tr id="fila-${u.id_usuario}">
                <td>
                    <strong>${escHtml(u.nombres)} ${escHtml(u.apellidos)}</strong>
                </td>
                <td>
                    <span style="
                        display:inline-block; padding:3px 10px; border-radius:20px;
                        font-size:0.72rem; font-weight:600;
                        background:${u.rol === 'Superadmin' ? 'rgba(201,168,76,0.16)' : 'rgba(96,165,250,0.18)'};
                        color:${u.rol === 'Superadmin' ? '#e8c97a' : '#93c5fd'};
                        border:1px solid ${u.rol === 'Superadmin' ? 'rgba(201,168,76,0.4)' : 'rgba(147,197,253,0.35)'};
                    ">${u.rol}</span>
                </td>
                <td>${escHtml(u.tipo_identificacion)} ${escHtml(u.numero_identificacion)}</td>
                <td>${escHtml(u.email)}</td>
                <td>${escHtml(u.telefono ?? '—')}</td>
                <td>${escHtml(u.cargo ?? '—')}</td>
                <td>
                    <span class="badge-${u.activo ? 'activo' : 'inactivo'}">
                        ${u.activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td style="color:var(--texto-sub);font-size:0.8rem">
                    ${fmtFecha(u.fecha_registro)}
                </td>
                <td>
                    <div class="acciones">
                        <button class="btn-accion toggle"
                            onclick="toggleActivo(${u.id_usuario}, ${!u.activo})"
                            title="${u.activo ? 'Desactivar' : 'Activar'}">
                            <i class="fas fa-${u.activo ? 'ban' : 'check'}"></i>
                            ${u.activo ? 'Desactivar' : 'Activar'}
                        </button>
                        <button class="btn-accion reset"
                            onclick="abrirModalReset(${u.id_usuario}, '${escHtml(u.nombres)} ${escHtml(u.apellidos)}')"
                            title="Restablecer contraseña">
                            <i class="fas fa-key"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (e) {
        document.getElementById('loading-tabla').innerHTML =
            `<span style="color:var(--danger)"><i class="fas fa-exclamation-circle"></i> Error: ${e.message}</span>`;
    }
}

// ════════════════════════════════════════════════════════════════════
//  Crear SuperAdmin
// ════════════════════════════════════════════════════════════════════
function abrirModalCrear() {
    limpiarFormCrear();
    document.getElementById('modal-crear').classList.add('activo');
}

function limpiarFormCrear() {
    ['c-nombres','c-apellidos','c-numero-id','c-email',
     'c-telefono','c-password'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('c-tipo-id').value = '';
    document.getElementById('c-cargo').value    = 'SuperAdministrador';
}

async function crearSuperAdmin() {
    const btn = document.getElementById('btn-crear');

    const datos = {
        nombres:               document.getElementById('c-nombres').value.trim(),
        apellidos:             document.getElementById('c-apellidos').value.trim(),
        tipo_identificacion:   document.getElementById('c-tipo-id').value,
        numero_identificacion: document.getElementById('c-numero-id').value.trim(),
        email:                 document.getElementById('c-email').value.trim(),
        telefono:              document.getElementById('c-telefono').value.trim(),
        cargo:                 document.getElementById('c-cargo').value.trim(),
        password:              document.getElementById('c-password').value,
        rol:                   document.getElementById('c-rol').value,
    };

    // Validación básica en cliente
    if (!datos.nombres || !datos.apellidos || !datos.tipo_identificacion ||
        !datos.numero_identificacion || !datos.email || !datos.password) {
        return mostrarToast('Completa todos los campos obligatorios.', 'error');
    }
    if (datos.password.length < 8) {
        return mostrarToast('La contraseña debe tener al menos 8 caracteres.', 'error');
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Creando...';

    try {
        const body = new FormData();
        body.append('accion', 'crear');
        body.append('csrf_token', CSRF_TOKEN);
        body.append('tab_id', TAB_ID);
        Object.entries(datos).forEach(([k,v]) => body.append(k, v));

        const res  = await fetch('/ajax/superadmin_usuarios', { method:'POST', body });
        const json = await res.json();

        if (!json.ok) throw new Error(json.error);

        cerrarModales();
        mostrarToast(json.data.mensaje, 'ok');
        cargarAdmins();

    } catch (e) {
        mostrarToast(e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Crear SuperAdmin';
    }
}

// ════════════════════════════════════════════════════════════════════
//  Toggle activo
// ════════════════════════════════════════════════════════════════════
async function toggleActivo(usuarioId, nuevoEstado) {
    try {
        const body = new FormData();
        body.append('accion',      'toggle_activo');
        body.append('csrf_token',  CSRF_TOKEN);
        body.append('tab_id',      TAB_ID);
        body.append('usuario_id',  usuarioId);
        body.append('activo',      nuevoEstado);

        const res  = await fetch('/ajax/superadmin_usuarios', { method:'POST', body });
        const json = await res.json();

        if (!json.ok) throw new Error(json.error);

        mostrarToast(json.data.mensaje, 'ok');
        await cargarAdmins();

    } catch (e) {
        mostrarToast(e.message, 'error');
        await cargarAdmins();
    }
}

// ════════════════════════════════════════════════════════════════════
//  Reset password
// ════════════════════════════════════════════════════════════════════
function abrirModalReset(usuarioId, nombre) {
    document.getElementById('reset-usuario-id').value    = usuarioId;
    document.getElementById('reset-nombre-label').textContent =
        `Nueva contraseña para ${nombre}.`;
    document.getElementById('reset-password').value = '';
    document.getElementById('modal-reset').classList.add('activo');
}

async function confirmarReset() {
    const id       = document.getElementById('reset-usuario-id').value;
    const password = document.getElementById('reset-password').value;

    if (password.length < 8) {
        return mostrarToast('La contraseña debe tener al menos 8 caracteres.', 'error');
    }

    try {
        const body = new FormData();
        body.append('accion',          'reset_password');
        body.append('csrf_token',      CSRF_TOKEN);
        body.append('tab_id',          TAB_ID);
        body.append('usuario_id',      id);
        body.append('nueva_password',  password);

        const res  = await fetch('/ajax/superadmin_usuarios', { method:'POST', body });
        const json = await res.json();

        if (!json.ok) throw new Error(json.error);

        cerrarModales();
        mostrarToast(json.data.mensaje, 'ok');

    } catch (e) {
        mostrarToast(e.message, 'error');
    }
}

// ════════════════════════════════════════════════════════════════════
//  Helpers
// ════════════════════════════════════════════════════════════════════
function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('activo'));
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtFecha(f) {
    if (!f) return '—';
    return new Date(f).toLocaleDateString('es-CO', { dateStyle:'medium' });
}

function mostrarToast(msg, tipo) {
    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `<i class="fas fa-${tipo === 'ok' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

// Cerrar modales con Escape o click fuera
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModales(); });
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) cerrarModales(); });
});
</script>

</body>
</html>