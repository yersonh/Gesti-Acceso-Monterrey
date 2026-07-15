<?php
// Guard — must be Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    redirect('/login');
}

$nombreAdmin = $_SESSION['usuario_nombre'] ?? 'Administrador';
$partes      = explode(' ', trim($nombreAdmin));
$iniciales   = strtoupper(
    substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : '')
);

// Flash messages — read and immediately clear
$flashMensaje = $_SESSION['flash_mensaje'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_mensaje'], $_SESSION['flash_error']);

$tituloSeccion = $tituloSeccion ?? 'Admin';
$seccionActiva = $seccionActiva ?? '';

// Embed tab_id in sidebar links so auth_load() identifies the tab on every navigation.
// auth_tab_id() already returns the current tab from $_GET['tab_id'] of this request.
$_tid  = auth_tab_id();
$_tabQ = ($_tid !== 'default') ? '?tab_id=' . urlencode($_tid) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});});})();window.CSRF_TOKEN='<?= csrf_token() ?>';</script>
<title>SGV — <?= htmlspecialchars($tituloSeccion) ?></title>
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
    --sidebar-bg:   #0f3522;
    --sidebar-w:    260px;
    --header-h:     60px;
    --blanco:       #ffffff;
    --texto:        #1e293b;
    --texto-sub:    #64748b;
    --borde:        #e2e8f0;
    --fondo:        #f1f5f9;
    --radius:       12px;
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--fondo);
    color: var(--texto);
    min-height: 100vh;
}

/* ═══════════════════════════════════
   SIDEBAR
═══════════════════════════════════ */
.sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--sidebar-bg);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    z-index: 200;
    border-right: 1px solid rgba(201,168,76,0.15);
}

.sidebar-top {
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.sidebar-logo {
    width: 64px; height: 64px;
    border-radius: 50%;
    border: 2.5px solid var(--dorado);
    object-fit: cover;
    box-shadow: 0 0 0 5px rgba(201,168,76,0.12);
}

.sidebar-brand { text-align: center; }

.sidebar-brand-title {
    font-family: 'Playfair Display', serif;
    color: var(--blanco);
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    line-height: 1.3;
}

.sidebar-brand-sub {
    color: rgba(255,255,255,0.45);
    font-size: 0.7rem;
    font-weight: 400;
    letter-spacing: 0.05em;
    margin-top: 2px;
}

/* NAV */
.sidebar-nav {
    flex: 1;
    padding: 16px 0;
    overflow-y: auto;
}

.nav-section {
    padding: 0 12px;
    margin-bottom: 6px;
}

.nav-section-label {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.28);
    padding: 12px 8px 6px;
    display: block;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 12px;
    border-radius: 9px;
    color: rgba(255,255,255,0.6);
    font-size: 0.87rem;
    font-weight: 500;
    transition: all 0.18s;
    text-decoration: none;
    margin-bottom: 1px;
}

.nav-item i {
    width: 18px;
    font-size: 0.88rem;
    flex-shrink: 0;
    text-align: center;
}

.nav-item:hover {
    background: rgba(255,255,255,0.07);
    color: var(--blanco);
}

.nav-item.activo {
    background: rgba(201,168,76,0.18);
    color: var(--dorado-claro);
    font-weight: 600;
}

.nav-item.activo i { color: var(--dorado); }

/* SIDEBAR FOOTER */
.sidebar-footer {
    padding: 14px 16px;
    border-top: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(201,168,76,0.2);
    border: 1.5px solid var(--dorado);
    color: var(--dorado-claro);
    font-size: 0.78rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.sidebar-user-info { flex: 1; min-width: 0; }

.sidebar-user-name {
    color: var(--blanco);
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-rol {
    color: var(--dorado);
    font-size: 0.67rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.sidebar-logout {
    background: none;
    border: none;
    color: rgba(255,255,255,0.35);
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: all 0.18s;
    flex-shrink: 0;
    text-decoration: none;
    display: flex; align-items: center; justify-content: center;
}
.sidebar-logout:hover { color: #ef4444; background: rgba(239,68,68,0.1); }

/* ═══════════════════════════════════
   MAIN WRAP
═══════════════════════════════════ */
.main-wrap {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
}

/* TOPBAR — fixed so it never overlaps content when scrolling */
.topbar {
    height: var(--header-h);
    background: var(--blanco);
    border-bottom: 1px solid var(--borde);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    position: fixed;
    top: 0;
    left: var(--sidebar-w);
    right: 0;
    z-index: 100;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
}

.topbar::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
}

.topbar-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--texto);
}

.topbar-title span { color: var(--verde-medio); }

.topbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar-badge {
    background: rgba(26,92,56,0.08);
    border: 1px solid rgba(26,92,56,0.15);
    color: var(--verde-medio);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
}

/* MAIN CONTENT — padding-top compensa el topbar fixed (60px) + espacio visual (32px) */
.main {
    padding: calc(var(--header-h) + 32px) 36px 40px;
}

/* ── Page header ── */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 20px;
    margin-bottom: 28px;
    border-bottom: 1.5px solid var(--borde);
    flex-wrap: wrap;
    gap: 12px;
}

.page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--texto);
}

.page-header p {
    color: var(--texto-sub);
    font-size: 0.87rem;
    margin-top: 2px;
}

/* ── Buttons ── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--verde);
    color: var(--blanco);
    border: none;
    padding: 10px 20px;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.87rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-primary:hover { background: var(--verde-medio); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,92,56,0.25); }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--blanco);
    color: var(--texto);
    border: 1.5px solid var(--borde);
    padding: 9px 18px;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.87rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary:hover { border-color: var(--verde); color: var(--verde); }

/* ── Cards KPI ── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.kpi-card {
    background: var(--blanco);
    border: 1px solid var(--borde);
    border-radius: var(--radius);
    padding: 20px;
    transition: box-shadow 0.2s;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }

.kpi-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--texto-sub);
    text-transform: uppercase;
    letter-spacing: 0.09em;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--texto);
    line-height: 1;
}

.kpi-card.verde  .kpi-value { color: var(--verde-medio); }
.kpi-card.dorado .kpi-value { color: #b45309; }
.kpi-card.azul   .kpi-value { color: #2563eb; }
.kpi-card.gris   .kpi-value { color: var(--texto-sub); }

/* ── Tabla ── */
.table-card {
    background: var(--blanco);
    border: 1px solid var(--borde);
    border-radius: var(--radius);
    overflow: hidden;
}

.table-toolbar {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--borde);
    flex-wrap: wrap;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: var(--fondo);
    padding: 10px 16px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--texto-sub);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    text-align: left;
    border-bottom: 1px solid var(--borde);
}

tbody td {
    padding: 9px 16px;
    font-size: 0.85rem;
    color: var(--texto);
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    white-space: nowrap;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #f8fafc; }

/* ── Badges ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    white-space: nowrap;
}
.badge-activo      { background:#d1fae5; color:#065f46; }
.badge-inactivo    { background:#fee2e2; color:#991b1b; }
.badge-rol         { background:rgba(26,92,56,0.1); color:var(--verde-medio); }
.badge-recurrente  { background:#dbeafe; color:#1d4ed8; }
.badge-no-recurrente { background:#f1f5f9; color:var(--texto-sub); }

/* ── Action buttons ── */
.table-actions { display: flex; gap: 6px; flex-wrap: wrap; }

.action-btn {
    width: 30px; height: 30px;
    border-radius: 7px;
    border: 1px solid var(--borde);
    background: var(--blanco);
    color: var(--texto-sub);
    cursor: pointer;
    font-size: 0.78rem;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.18s;
}
.action-btn:hover { border-color: var(--verde); color: var(--verde); background: rgba(26,92,56,0.05); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }
.action-btn.warn { color: #d97706; border-color: #fde68a; background: #fffbeb; }
.action-btn.warn:hover { border-color: #f59e0b; color: #b45309; background: #fef3c7; }
.action-btn.danger-soft { color: #dc2626; border-color: #fecaca; }
.action-btn.danger-soft:hover { border-color: #ef4444; background: rgba(239,68,68,0.05); }
.btn-danger {
    display: inline-flex; align-items: center; gap: 7px;
    background: #dc2626; color: #fff; border: none;
    padding: 10px 20px; border-radius: 9px;
    font-family: 'DM Sans', sans-serif; font-size: 0.87rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
}
.btn-danger:hover { background: #b91c1c; }

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--texto-sub);
}
.empty-state i { font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 14px; }
.empty-state h4 { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--texto); margin-bottom: 6px; }
.empty-state p  { font-size: 0.87rem; }

/* ── Flash alerts ── */
.alerta-ok, .alerta-error {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 0.87rem;
    font-weight: 500;
    animation: fadeUp 0.3s ease both;
}
.alerta-ok    { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
.alerta-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
.alerta-ok i, .alerta-error i { font-size: 1rem; flex-shrink: 0; }

@keyframes fadeUp {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ── Modals ── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 500;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-overlay.open { display: flex; }

.modal-card {
    background: var(--blanco);
    border-radius: 14px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: fadeUp 0.25s ease both;
    overflow: hidden;
}

.modal-header {
    padding: 16px 20px 14px;
    border-bottom: 1px solid var(--borde);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-shrink: 0;
}

.modal-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--texto);
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--texto-sub);
    font-size: 1rem;
    padding: 4px 6px;
    border-radius: 6px;
    transition: all 0.18s;
}
.modal-close:hover { color: #ef4444; background: rgba(239,68,68,0.08); }

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    scrollbar-width: thin;
    scrollbar-color: rgba(26,92,56,0.25) transparent;
}
.modal-body::-webkit-scrollbar { width: 4px; }
.modal-body::-webkit-scrollbar-track { background: transparent; }
.modal-body::-webkit-scrollbar-thumb { background: rgba(26,92,56,0.25); border-radius: 99px; }
.modal-body::-webkit-scrollbar-thumb:hover { background: var(--verde-medio); }

.modal-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--borde);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}

/* ── Form fields ── */
.form-group { margin-bottom: 16px; }

.form-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--texto);
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    border: 1.5px solid var(--borde);
    border-radius: 8px;
    padding: 9px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.87rem;
    color: var(--texto);
    background: var(--blanco);
    outline: none;
    transition: border-color 0.18s;
}
.form-control:focus { border-color: var(--verde-medio); }
.form-control::placeholder { color: #adb5bd; }
.field-error { display:none; font-size:0.77rem; color:#dc2626; margin-top:3px; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.87rem;
    color: var(--texto);
    cursor: pointer;
}
.form-check input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: var(--verde);
    cursor: pointer;
}

/* ── Config cards (horarios) ── */
.toggle-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.config-card {
    background: var(--blanco);
    border: 1px solid var(--borde);
    border-radius: var(--radius);
    padding: 20px 24px;
}

.config-card h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--texto);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.config-card h4 i { color: var(--verde-medio); font-size: 0.9rem; }

.config-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--fondo);
    gap: 12px;
}
.config-row:last-child { border-bottom: none; }

.config-row-label {
    font-size: 0.87rem;
    color: var(--texto);
    font-weight: 500;
}

.config-row-sub {
    font-size: 0.75rem;
    color: var(--texto-sub);
}

/* ── Pagination ── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    flex-wrap: wrap;
}

.page-btn {
    min-width: 34px; height: 34px;
    border-radius: 7px;
    border: 1.5px solid var(--borde);
    background: var(--blanco);
    color: var(--texto);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    transition: all 0.18s;
    padding: 0 8px;
}
.page-btn:hover    { border-color: var(--verde); color: var(--verde); }
.page-btn.activo   { background: var(--verde); border-color: var(--verde); color: var(--blanco); }
.page-btn:disabled { opacity: 0.45; cursor: default; }

/* ── Filter bar ── */
.filter-bar {
    background: var(--blanco);
    border: 1px solid var(--borde);
    border-radius: var(--radius);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-group { display: flex; flex-direction: column; gap: 5px; }
.filter-label { font-size: 0.78rem; font-weight: 600; color: var(--texto-sub); }

.filter-input {
    border: 1.5px solid var(--borde);
    border-radius: 8px;
    padding: 8px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    color: var(--texto);
    background: var(--blanco);
    outline: none;
    transition: border-color 0.18s;
}
.filter-input:focus { border-color: var(--verde-medio); }

/* ── Scrollbar global ── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--fondo); border-radius: 3px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--verde-medio); }

/* ── Dependencias scroll horizontal ── */
.deps-scroll {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 5px;
    padding-bottom: 4px;
    scrollbar-width: thin;
    scrollbar-color: rgba(26,92,56,0.25) transparent;
}
.deps-scroll::-webkit-scrollbar { height: 3px; }
.deps-scroll::-webkit-scrollbar-track { background: transparent; }
.deps-scroll::-webkit-scrollbar-thumb {
    background: rgba(26,92,56,0.25);
    border-radius: 99px;
}
.deps-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--verde-medio);
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
    .sidebar.open { transform: translateX(0); }
    .main-wrap { margin-left: 0; }
    .main { padding: 20px 16px; }
    .topbar { padding: 0 16px; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .form-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía" class="sidebar-logo">
        <div class="sidebar-brand">
            <div class="sidebar-brand-title">Sistema de Visitas</div>
            <div class="sidebar-brand-sub">Alcaldía Municipal Monterrey</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- GESTIÓN -->
        <div class="nav-section">
            <span class="nav-section-label">Gestión</span>
            <a href="/admin/usuarios<?= $_tabQ ?>"  class="nav-item <?= $seccionActiva === 'usuarios'    ? 'activo' : '' ?>">
                <i class="fas fa-users"></i> Usuarios
            </a>
            <a href="/admin/personal<?= $_tabQ ?>"  class="nav-item <?= $seccionActiva === 'personal'    ? 'activo' : '' ?>">
                <i class="fas fa-user"></i> Personal
            </a>
            <a href="/admin/funcionarios<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'funcionarios' ? 'activo' : '' ?>">
                <i class="fas fa-user-tie"></i> Funcionarios
            </a>
            <a href="/admin/ciudadanos<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'ciudadanos' ? 'activo' : '' ?>">
                <i class="fas fa-id-card"></i> Ciudadanos
            </a>
        </div>

        <!-- PARAMETRIZACIÓN -->
        <div class="nav-section">
            <span class="nav-section-label">Parametrización</span>
            <a href="/admin/dependencias<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'dependencias'    ? 'activo' : '' ?>">
                <i class="fas fa-building"></i> Dependencias
            </a>
            <a href="/admin/func-dependencia<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'func-dependencia' ? 'activo' : '' ?>">
                <i class="fas fa-sitemap"></i> Func. por Dependencia
            </a>
        </div>

        <!-- CONFIGURACIÓN -->
        <div class="nav-section">
            <span class="nav-section-label">Configuración</span>
            <a href="/admin/horarios<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'horarios' ? 'activo' : '' ?>">
                <i class="fas fa-clock"></i> Horarios
            </a>
            <a href="/admin/festivos<?= $_tabQ ?>" class="nav-item <?= $seccionActiva === 'festivos' ? 'activo' : '' ?>">
                <i class="fas fa-calendar-times"></i> Días Festivos
            </a>
        </div>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-avatar"><?= htmlspecialchars($iniciales) ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($nombreAdmin) ?></div>
            <div class="sidebar-user-rol">Administrador</div>
        </div>
        <a href="/logout<?= $_tabQ ?>" class="sidebar-logout" title="Cerrar sesión">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</aside>

<!-- ══ MAIN WRAP ══ -->
<div class="main-wrap">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-title">
            Panel <span><?= htmlspecialchars($tituloSeccion) ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-badge">Administrador</span>
        </div>
    </header>

    <main class="main">

<?php if ($flashMensaje): ?>
<div class="alerta-ok" id="flash-ok">
    <i class="fas fa-check-circle"></i>
    <?= htmlspecialchars($flashMensaje) ?>
</div>
<?php endif; ?>

<?php if ($flashError): ?>
<div class="alerta-error" id="flash-err">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars($flashError) ?>
</div>
<?php endif; ?>

<script>
setTimeout(function(){
    var ok  = document.getElementById('flash-ok');
    var err = document.getElementById('flash-err');
    if (ok)  { ok.style.transition='opacity 0.5s'; ok.style.opacity='0'; setTimeout(function(){ok.remove();},500); }
    if (err) { err.style.transition='opacity 0.5s'; err.style.opacity='0'; setTimeout(function(){err.remove();},500); }
}, 4000);
</script>
