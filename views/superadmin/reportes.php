<?php
// Vista — la lógica está en ReportesController::dashboard()
// Guard de seguridad — doble verificación en la vista
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
    <title>SGV — Reportes SuperAdmin</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        :root {
            --verde:        #1a5c38;
            --verde-medio:  #2d7a4f;
            --verde-claro:  #3d9e68;
            --dorado:       #c9a84c;
            --dorado-claro: #e8c97a;
            --crema:        #f8f4ed;
            --blanco:       #ffffff;
            --texto:        #eef3ee;
            --texto-sub:    rgba(238, 243, 238, 0.6);
            --borde:        rgba(255, 255, 255, 0.12);
            --fondo:        rgba(0, 0, 0, 0.22);
            --vidrio:       rgba(20, 34, 26, 0.45);
            --danger:       #ef4444;
            --success:      #10b981;
            --warning:      #f59e0b;
            --radius:       14px;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--texto);
            min-height: 100vh;

            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.6) 0%, rgba(6, 14, 10, 0.78) 55%, rgba(6, 14, 10, 0.9) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* ── Header ─────────────────────────────────────────────────────── */
        .header {
            background: rgba(15, 53, 34, 0.6);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
            border-bottom: 1px solid rgba(201,168,76,0.2);
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
        }

        .header-brand {
            display: flex; align-items: center; gap: 14px; text-decoration: none;
        }

        .header-brand img {
            width: 40px; height: 40px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--dorado);
        }

        .header-brand-texto {
            color: var(--blanco); font-size: 0.8rem; font-weight: 500;
            letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; line-height: 1.4;
        }

        .header-brand-texto strong {
            display: block; font-size: 0.92rem;
            color: var(--dorado-claro); letter-spacing: 0.04em;
        }

        .header-usuario { display: flex; align-items: center; gap: 14px; }

        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(201,168,76,0.25); border: 2px solid var(--dorado);
            color: var(--dorado-claro); font-size: 0.85rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; letter-spacing: 0.05em;
        }

        .header-nombre { color: var(--blanco); font-size: 0.88rem; font-weight: 500; opacity: 0.9; }

        .header-rol {
            background: rgba(201,168,76,0.2);
            border: 1px solid rgba(201,168,76,0.4);
            color: var(--dorado-claro);
            padding: 3px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 600;
            letter-spacing: 0.08em; text-transform: uppercase;
        }

        .btn-logout {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco); padding: 7px 14px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }

        .btn-logout:hover { background: rgba(255,255,255,0.2); }

        /* ── Main ────────────────────────────────────────────────────────── */
        .main { max-width: 1200px; margin: 0 auto; padding: 36px 24px 60px; }

        /* ── Bienvenida ──────────────────────────────────────────────────── */
        .bienvenida { margin-bottom: 28px; }

        .bienvenida h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem; font-weight: 700;
            color: var(--texto); margin-bottom: 4px;
        }

        .bienvenida h1 span { color: var(--dorado-claro); }

        .bienvenida p { color: var(--texto-sub); font-size: 0.9rem; font-weight: 300; }

        /* ── Filtros ─────────────────────────────────────────────────────── */
        .filtros-panel {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }

        .filtros-panel-titulo {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: var(--texto-sub); margin-bottom: 14px;
            display: flex; align-items: center; gap: 6px;
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            align-items: end;
        }

        .filtro-group label {
            display: block;
            font-size: 0.78rem; font-weight: 500;
            color: var(--texto-sub); margin-bottom: 5px;
        }

        .filtro-group input,
        .filtro-group select {
            width: 100%;
            background: rgba(0,0,0,0.28);
            border: 1.5px solid var(--borde);
            border-radius: 8px;
            color: var(--texto);
            padding: 0.5rem 0.75rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .filtro-group input:focus,
        .filtro-group select:focus { border-color: var(--dorado); box-shadow: 0 0 0 3px rgba(201,168,76,0.15); }
        .filtro-group select option { background: #14221a; color: var(--texto); }

        .btn-aplicar {
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado)); color: #2c2107;
            border: none; border-radius: 8px;
            padding: 0.55rem 1.25rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem; font-weight: 700;
            cursor: pointer; width: 100%;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            box-shadow: 0 4px 14px rgba(201,168,76,0.2);
        }

        .btn-aplicar:hover   { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(201,168,76,0.35); }
        .btn-aplicar:active  { transform: scale(0.97); }

        /* ── Métricas resumen ────────────────────────────────────────────── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .metric-card {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: var(--radius);
            padding: 20px 20px 16px;
            display: flex; flex-direction: column; gap: 6px;
            transition: box-shadow 0.2s;
        }

        .metric-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.3); }

        .metric-card .m-icon { font-size: 1.1rem; margin-bottom: 2px; }
        .metric-card .m-label {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--texto-sub);
        }

        .metric-card .m-value {
            font-size: 2rem; font-weight: 700; line-height: 1;
        }

        .metric-card .m-sub { font-size: 0.75rem; color: var(--texto-sub); }

        .metric-card.mc-total    .m-icon,
        .metric-card.mc-total    .m-value  { color: var(--texto); }
        .metric-card.mc-citas    .m-icon,
        .metric-card.mc-citas    .m-value  { color: var(--verde-claro); }
        .metric-card.mc-espon    .m-icon,
        .metric-card.mc-espon    .m-value  { color: var(--dorado-claro); }
        .metric-card.mc-noshow   .m-icon,
        .metric-card.mc-noshow   .m-value  { color: #fca5a5; }

        /* ── Secciones de reporte ────────────────────────────────────────── */
        .reporte-section {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: var(--radius);
            margin-bottom: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }

        .reporte-header {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid transparent;
            cursor: pointer; user-select: none;
            transition: background 0.15s;
        }

        .reporte-header:hover { background: rgba(255,255,255,0.04); }

        .reporte-header.open {
            border-bottom-color: var(--borde);
        }

        .reporte-header h2 {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
            color: var(--texto);
        }

        .reporte-icon {
            width: 30px; height: 30px; border-radius: 8px;
            background: rgba(201,168,76,0.18); color: var(--dorado-claro);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; flex-shrink: 0;
        }

        .reporte-actions { display: flex; gap: 6px; align-items: center; }

        .btn-export {
            padding: 5px 12px; border-radius: 6px;
            border: 1.5px solid var(--borde);
            background: rgba(255,255,255,0.06); color: var(--texto-sub);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.75rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; gap: 4px;
        }

        .btn-export:hover { border-color: var(--dorado); color: var(--dorado-claro); background: rgba(201,168,76,0.1); }

        .chevron {
            color: var(--texto-sub); font-size: 0.75rem;
            transition: transform 0.2s; margin-left: 4px;
        }

        .chevron.open { transform: rotate(180deg); }

        .reporte-body {
            padding: 1.5rem;
            display: none;
        }

        .reporte-body.open { display: block; }

        /* ── Gráficos ────────────────────────────────────────────────────── */
        .chart-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 1.5rem;
        }

        /* ── Tablas ──────────────────────────────────────────────────────── */
        .tabla-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--borde);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        thead th {
            background: rgba(0,0,0,0.2);
            padding: 0.65rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--texto-sub);
            border-bottom: 1px solid var(--borde);
        }

        tbody tr { border-bottom: 1px solid rgba(255,255,255,0.06); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.04); }
        tbody td { padding: 0.65rem 1rem; color: var(--texto); }

        /* ── Badges ──────────────────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 600; white-space: nowrap;
        }

        .badge-cita       { background: rgba(52,211,153,0.16); color: #6ee7b7; border: 1px solid rgba(110,231,183,0.3); }
        .badge-espontanea { background: rgba(201,168,76,0.16); color: var(--dorado-claro); border: 1px solid rgba(201,168,76,0.4); }
        .badge-cancelada  { background: rgba(248,113,113,0.16); color: #fca5a5; border: 1px solid rgba(252,165,165,0.3); }
        .badge-reprog     { background: rgba(251,191,36,0.16); color: #fcd34d; border: 1px solid rgba(251,191,36,0.35); }
        .badge-contraprop { background: rgba(96,165,250,0.18); color: #93c5fd; border: 1px solid rgba(147,197,253,0.35); }

        /* ── Estado vacío / cargando ─────────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 3rem;
            color: var(--texto-sub); font-size: 0.9rem;
        }

        .empty-state i {
            font-size: 2rem; color: rgba(255,255,255,0.25);
            display: block; margin-bottom: 10px;
        }

        .loading {
            display: flex; align-items: center;
            justify-content: center; gap: 10px;
            padding: 2.5rem; color: var(--texto-sub); font-size: 0.875rem;
        }

        .spinner {
            width: 20px; height: 20px;
            border: 2px solid var(--borde);
            border-top-color: var(--dorado);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        @keyframes subirEntrar {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ──────────────────────────────────────────────────── */
        @media (max-width: 900px) {
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .mc-satisfaccion .m-icon,
        .mc-satisfaccion .m-value { color: #fbbf24; }
        .mc-asistencia   .m-icon,
        .mc-asistencia   .m-value { color: var(--verde-claro); }

        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .main   { padding: 20px 14px 48px; }
            .header-nombre, .header-rol { display: none; }
            .filtros-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
            .metrics-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .filtros-grid { grid-template-columns: 1fr; }
            .btn-export   { display: none; }
            .bienvenida h1 { font-size: 1.35rem; }
            .bienvenida p  { font-size: 0.82rem; }
            .header-nav .nav-link span { display: none; }
            .tabla-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 480px; font-size: 0.82rem; }
            .seccion-reportes { padding: 16px; }
        }

        body::-webkit-scrollbar { width: 8px; }
        body::-webkit-scrollbar-track { background: transparent; }
        body::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.4); border-radius: 4px; }
        body::-webkit-scrollbar-thumb:hover { background: var(--dorado); }
        .header-nav { display:flex; align-items:center; gap:8px; }
        .nav-link {
            color:rgba(255,255,255,0.75); text-decoration:none; padding:6px 14px;
            border-radius:8px; font-size:0.85rem; font-weight:500;
            transition:all 0.2s; display:flex; align-items:center; gap:6px;
        }
        .nav-link:hover { background:rgba(255,255,255,0.1); color:var(--blanco); }
        .nav-link.activo { background:rgba(255,255,255,0.15); color:var(--blanco); }
    </style>
</head>
<body>

<!-- ── Header ───────────────────────────────────────────────────────────────── -->
<header class="header">
    <div class="header-brand">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="header-brand-texto">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </div>
    <nav class="header-nav">
            <a href="/superadmin/reportes" class="nav-link activo">
                <i class="fas fa-chart-bar"></i> <span>Reportes</span>
            </a>
            <a href="/superadmin/usuarios" class="nav-link">
                <i class="fas fa-users-cog"></i> <span>Usuarios</span>
            </a>
    </nav>
    <div class="header-usuario">
        <div class="avatar"><?= htmlspecialchars($iniciales) ?></div>
        <span class="header-nombre"><?= htmlspecialchars($nombreAdmin) ?></span>
        <span class="header-rol">SuperAdmin</span>
        <a href="/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</header>
<!-- ── Contenido principal ──────────────────────────────────────────────────── -->
<main class="main">
    <div class="bienvenida">
        <h1>Bienvenido, <span><?= htmlspecialchars($primerNombre) ?></span></h1>
        <p>Panel de reportes y estadísticas — Módulo de Visitas</p>
    </div>

    <!-- Filtros globales -->
    <div class="filtros-panel">
        <div class="filtros-panel-titulo">
            <i class="fas fa-filter"></i> Filtros globales
        </div>
        <div class="filtros-grid">
            <div class="filtro-group">
                <label>Desde</label>
                <input type="date" id="fecha_inicio" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="filtro-group">
                <label>Hasta</label>
                <input type="date" id="fecha_fin" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="filtro-group">
                <label>Dependencia</label>
                <select id="dependencia_id">
                    <option value="">Todas las dependencias</option>
                </select>
            </div>
            <div class="filtro-group">
                <label>Funcionario</label>
                <select id="funcionario_id">
                    <option value="">Todos los funcionarios</option>
                </select>
            </div>
            <div class="filtro-group">
                <label>&nbsp;</label>
                <button class="btn-aplicar" onclick="aplicarFiltros()">
                    <i class="fas fa-search"></i> Aplicar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Métricas resumen -->
    <div class="metrics-grid">
        <div class="metric-card mc-total">
            <div class="m-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="m-label">Total visitas</div>
            <div class="m-value" id="m-total">—</div>
            <div class="m-sub">en el período</div>
        </div>
        <div class="metric-card mc-citas">
            <div class="m-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="m-label">Citas agendadas</div>
            <div class="m-value" id="m-citas">—</div>
            <div class="m-sub" id="m-citas-pct">del total</div>
        </div>
        <div class="metric-card mc-espon">
            <div class="m-icon"><i class="fas fa-walking"></i></div>
            <div class="m-label">Espontáneas</div>
            <div class="m-value" id="m-espontaneas">—</div>
            <div class="m-sub" id="m-espontaneas-pct">del total</div>
        </div>
        <div class="metric-card mc-noshow">
            <div class="m-icon"><i class="fas fa-user-times"></i></div>
            <div class="m-label">No-shows</div>
            <div class="m-value" id="m-noshow">—</div>
            <div class="m-sub">inasistencias</div>
        </div>
        <div class="metric-card mc-asistencia">
            <div class="m-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="m-label">Tasa de asistencia</div>
            <div class="m-value" id="m-asistencia">—</div>
            <div class="m-sub" id="m-asistencia-sub">de citas programadas</div>
        </div>
        <div class="metric-card mc-satisfaccion">
            <div class="m-icon"><i class="fas fa-star"></i></div>
            <div class="m-label">Satisfacción promedio</div>
            <div class="m-value" id="m-satisfaccion">—</div>
            <div class="m-sub" id="m-satisfaccion-sub">de 5 estrellas</div>
        </div>
    </div>

    <!-- ── R1: Ocupación por dependencia ──────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header open" onclick="toggleSeccion('ocupacion', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-building"></i></div>
                Ocupación por dependencia
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('ocupacion', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('ocupacion', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron open"></i>
            </div>
        </div>
        <div class="reporte-body open" id="body-ocupacion">
            <div class="loading" id="load-ocupacion"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-ocupacion">
                <div class="chart-wrapper" style="height:300px">
                    <canvas id="chart-ocupacion" role="img"
                        aria-label="Barras apiladas con visitas por dependencia">
                        Número de visitas por dependencia.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-ocupacion">
                        <thead><tr>
                            <th>Dependencia</th><th>Total</th>
                            <th>Citas</th><th>Espontáneas</th><th>% citas</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R2: Horas pico ─────────────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header open" onclick="toggleSeccion('horas', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-clock"></i></div>
                Horas pico
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('horas_pico', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('horas_pico', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron open"></i>
            </div>
        </div>
        <div class="reporte-body open" id="body-horas">
            <div class="loading" id="load-horas"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-horas">
                <div class="chart-wrapper" style="height:260px">
                    <canvas id="chart-horas" role="img"
                        aria-label="Línea de afluencia por hora del día">
                        Afluencia de visitantes por hora del día.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-horas">
                        <thead><tr>
                            <th>Hora</th><th>Total</th><th>Citas</th><th>Espontáneas</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R9: Satisfacción general ───────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('satisfaccion', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-star"></i></div>
                Satisfacción general
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('satisfaccion', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('satisfaccion', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-satisfaccion">
            <div class="loading" id="load-satisfaccion"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-satisfaccion">
                <div id="satisfaccion-resumen" style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;margin-bottom:1.25rem;padding:1rem 1.25rem;background:var(--fondo);border-radius:10px;border:1px solid var(--borde)"></div>
                <div class="chart-wrapper" style="height:280px">
                    <canvas id="chart-satisfaccion" role="img"
                        aria-label="Distribución de calificaciones por número de estrellas">
                        Distribución de calificaciones.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-satisfaccion">
                        <thead><tr>
                            <th>Calificación</th><th>Cantidad</th><th>%</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R10: Calificación por funcionario ──────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('califfunc', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-user-check"></i></div>
                Calificación por funcionario
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('satisfaccion_funcionario', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('satisfaccion_funcionario', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-califfunc">
            <div class="loading" id="load-califfunc"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-califfunc">
                <div class="chart-wrapper" style="height:300px">
                    <canvas id="chart-califfunc" role="img"
                        aria-label="Promedio de estrellas por funcionario">
                        Promedio de calificación por funcionario.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-califfunc">
                        <thead><tr>
                            <th>Funcionario</th><th>Dependencia</th>
                            <th>Valoraciones</th><th>Promedio</th><th>% resueltos</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R3: Tiempo de espera ───────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('espera', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-hourglass-half"></i></div>
                Tiempo de espera promedio
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('espera', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('espera', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-espera">
            <div class="loading" id="load-espera"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-espera">
                <div class="chart-wrapper" style="height:300px">
                    <canvas id="chart-espera" role="img"
                        aria-label="Barras horizontales con tiempo de espera por funcionario">
                        Tiempo de espera promedio por funcionario en minutos.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-espera">
                        <thead><tr>
                            <th>Funcionario</th><th>Dependencia</th>
                            <th>Citas atendidas</th><th>Espera prom. (min)</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R4: Duración de visitas ────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('duracion', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-ruler-horizontal"></i></div>
                Duración de visitas
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('duracion', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('duracion', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-duracion">
            <div class="loading" id="load-duracion"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-duracion">
                <div class="chart-wrapper" style="height:300px">
                    <canvas id="chart-duracion" role="img"
                        aria-label="Barras con duración promedio de visitas por dependencia">
                        Duración promedio de visitas por dependencia en minutos.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-duracion">
                        <thead><tr>
                            <th>Dependencia</th><th>Tipo</th><th>Visitas</th>
                            <th>Duración prom.</th><th>Mínima</th><th>Máxima</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R5: Motivos ────────────────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('motivos', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-list-ol"></i></div>
                Ranking de motivos de visita
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('motivos', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('motivos', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-motivos">
            <div class="loading" id="load-motivos"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-motivos">
                <div class="chart-wrapper" style="height:320px">
                    <canvas id="chart-motivos" role="img"
                        aria-label="Dona con motivos de visita más frecuentes">
                        Ranking de motivos de visita más frecuentes.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-motivos">
                        <thead><tr>
                            <th>#</th><th>Motivo</th><th>Frecuencia</th>
                            <th>Citas</th><th>Espontáneas</th><th>%</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R6: No-show ────────────────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('noshow', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-user-slash"></i></div>
                Inasistencias (No-show)
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('no_show', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('no_show', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-noshow">
            <div class="loading" id="load-noshow"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-noshow">
                <div class="chart-wrapper" style="height:300px">
                    <canvas id="chart-noshow" role="img"
                        aria-label="Barras con porcentaje de no-show por funcionario">
                        Porcentaje de inasistencias por funcionario.
                    </canvas>
                </div>
                <div class="tabla-wrapper">
                    <table id="tabla-noshow">
                        <thead><tr>
                            <th>Funcionario</th><th>Dependencia</th>
                            <th>Programadas</th><th>No-shows</th><th>% inasistencia</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R7: Trazabilidad ───────────────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('trazabilidad', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-search"></i></div>
                Trazabilidad: rechazadas y reprogramadas
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('trazabilidad', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('trazabilidad', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-trazabilidad">
            <div class="loading" id="load-trazabilidad"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-trazabilidad">
                <div class="tabla-wrapper">
                    <table id="tabla-trazabilidad">
                        <thead><tr>
                            <th>#</th><th>Ciudadano</th><th>Funcionario</th>
                            <th>Dependencia</th><th>Fecha original</th><th>Estado</th>
                            <th>Motivo</th><th>Reprogramada para</th><th>Cancelado por</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── R8: Exportación completa ───────────────────────────────────────── -->
    <div class="reporte-section">
        <div class="reporte-header" onclick="toggleSeccion('exportacion', this)">
            <h2>
                <div class="reporte-icon"><i class="fas fa-file-export"></i></div>
                Exportación completa de datos
            </h2>
            <div class="reporte-actions">
                <button class="btn-export" onclick="exportarExcel('exportacion', event)">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export" onclick="exportarPDF('exportacion', event)">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
        </div>
        <div class="reporte-body" id="body-exportacion">
            <div class="loading" id="load-exportacion"><div class="spinner"></div> Cargando...</div>
            <div style="display:none" id="content-exportacion">
                <div class="tabla-wrapper">
                    <table id="tabla-exportacion">
                        <thead><tr>
                            <th>Tipo</th><th>Ciudadano</th><th>Identificación</th>
                            <th>Funcionario</th><th>Dependencia</th><th>Motivo</th>
                            <th>Fecha</th><th>Ingreso</th><th>Salida</th>
                            <th>Estado</th><th>Duración (min)</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
const TAB_ID = window.TAB_ID || '';

// ════════════════════════════════════════════════════════════════════
//  Paleta institucional para Chart.js
// ════════════════════════════════════════════════════════════════════
const C = {
    verde:   '#2d7a4f',
    dorado:  '#c9a84c',
    rojo:    '#ef4444',
    azul:    '#3b82f6',
    morado:  '#8b5cf6',
    naranja: '#f97316',
    cyan:    '#06b6d4',
    rosa:    '#ec4899',
    lima:    '#84cc16',
    gris:    '#94a3b8',
    gridLine:'rgba(255,255,255,0.08)',
    tickColor:'rgba(238,243,238,0.6)',
};

const CHART_DEFAULTS = {
    scales: {
        x: { ticks: { color: C.tickColor, font: { family: "'DM Sans', sans-serif", size: 11 } }, grid: { color: C.gridLine } },
        y: { ticks: { color: C.tickColor, font: { family: "'DM Sans', sans-serif", size: 11 } }, grid: { color: C.gridLine }, beginAtZero: true },
    },
    plugins: {
        legend: { labels: { color: 'rgba(238,243,238,0.75)', font: { family: "'DM Sans', sans-serif", size: 12 }, boxWidth: 12, padding: 16 } },
    },
    responsive: true,
    maintainAspectRatio: false,
};

// ════════════════════════════════════════════════════════════════════
//  Estado global
// ════════════════════════════════════════════════════════════════════
const STATE = { filtros: {}, data: {}, charts: {} };

const REPORTE_MAP = {
    ocupacion:    'ocupacion',
    horas:        'horas_pico',
    espera:       'espera',
    duracion:     'duracion',
    motivos:      'motivos',
    noshow:       'no_show',
    trazabilidad: 'trazabilidad',
    exportacion:  'exportacion',
    satisfaccion: 'satisfaccion',
    califfunc:    'satisfaccion_funcionario',
};

// ════════════════════════════════════════════════════════════════════
//  Init
// ════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    cargarFiltros();
    aplicarFiltros();
});

// ════════════════════════════════════════════════════════════════════
//  Filtros
// ════════════════════════════════════════════════════════════════════
async function cargarFiltros() {
    try {
        const res  = await fetch('/ajax/reportes?reporte=filtros&tab_id='+TAB_ID);
        const json = await res.json();
        if (!json.ok) return;

        const selDep = document.getElementById('dependencia_id');
        json.data.dependencias.forEach(d => {
            selDep.appendChild(new Option(d.nombre, d.id_dependencia));
        });
        poblarFuncionarios(json.data.funcionarios);

        selDep.addEventListener('change', async () => {
            const depId = selDep.value;
            const url   = depId
                ? `/ajax/reportes?reporte=filtros&dependencia_id=${depId}&tab_id=${TAB_ID}`
                : `/ajax/reportes?reporte=filtros&tab_id=${TAB_ID}`;
            const r = await fetch(url);
            const j = await r.json();
            if (j.ok) poblarFuncionarios(j.data.funcionarios);
        });
    } catch (e) { console.error('Error cargando filtros:', e); }
}

function poblarFuncionarios(lista) {
    const sel = document.getElementById('funcionario_id');
    sel.innerHTML = '<option value="">Todos los funcionarios</option>';
    lista.forEach(f => sel.appendChild(new Option(f.nombre, f.id_funcionario)));
}

function aplicarFiltros() {
    STATE.filtros = {
        fecha_inicio:   document.getElementById('fecha_inicio').value,
        fecha_fin:      document.getElementById('fecha_fin').value,
        dependencia_id: document.getElementById('dependencia_id').value,
        funcionario_id: document.getElementById('funcionario_id').value,
    };

    // Limpiar caché y gráficos
    STATE.data = {};
    Object.keys(STATE.charts).forEach(k => {
        STATE.charts[k].destroy();
        delete STATE.charts[k];
    });

    // Resetear métricas visualmente
    ['m-total','m-citas','m-espontaneas','m-noshow'].forEach(id => {
        document.getElementById(id).textContent = '—';
    });
    document.getElementById('m-citas-pct').textContent     = 'del total';
    document.getElementById('m-espontaneas-pct').textContent = 'del total';

    // Recargar todas las secciones abiertas
    Object.keys(REPORTE_MAP).forEach(nombre => {
        const body = document.getElementById(`body-${nombre}`);
        if (body && body.classList.contains('open')) {
            const loadEl = document.getElementById(`load-${nombre}`);
            const contEl = document.getElementById(`content-${nombre}`);
            if (loadEl) loadEl.style.display = 'flex';
            if (contEl) contEl.style.display = 'none';
            cargarReporte(nombre);
        }
    });

    cargarMetricasResumen();
}

// ════════════════════════════════════════════════════════════════════
//  Fetch con caché
// ════════════════════════════════════════════════════════════════════
async function fetchReporte(reporte) {
    if (STATE.data[reporte]) return STATE.data[reporte];

    const p = new URLSearchParams({ reporte, ...STATE.filtros, tab_id: TAB_ID });
    [...p.entries()].forEach(([k, v]) => { if (!v && k !== 'tab_id') p.delete(k); });

    const res  = await fetch(`/ajax/reportes?${p}`);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error ?? 'Error desconocido');

    STATE.data[reporte] = json.data;
    return json.data;
}

// ════════════════════════════════════════════════════════════════════
//  Toggle secciones con carga lazy
// ════════════════════════════════════════════════════════════════════
function toggleSeccion(nombre, headerEl) {
    const body = document.getElementById(`body-${nombre}`);
    const chev = headerEl.querySelector('.chevron');
    const abierto = body.classList.toggle('open');
    headerEl.classList.toggle('open', abierto);
    chev.classList.toggle('open', abierto);
    if (abierto && REPORTE_MAP[nombre]) cargarReporte(nombre);
}

async function cargarReporte(nombre) {
    const key    = REPORTE_MAP[nombre];
    const loadEl = document.getElementById(`load-${nombre}`);
    const contEl = document.getElementById(`content-${nombre}`);
    if (!loadEl || !contEl) return;

    loadEl.style.display = 'flex';
    contEl.style.display = 'none';

    try {
        const data = await fetchReporte(key);
        const renders = {
            ocupacion: renderOcupacion, horas: renderHoras,
            espera: renderEspera,       duracion: renderDuracion,
            motivos: renderMotivos,     noshow: renderNoShow,
            trazabilidad: renderTrazabilidad, exportacion: renderExportacion,
            satisfaccion: renderSatisfaccion, califfunc: renderCalifFunc,
        };
        if (renders[nombre]) renders[nombre](data);
        loadEl.style.display = 'none';
        contEl.style.display = 'block';
    } catch (e) {
        loadEl.innerHTML = `<i class="fas fa-exclamation-circle" style="color:var(--danger)"></i>
            <span style="color:var(--danger)">Error: ${e.message}</span>`;
    }
}

// ════════════════════════════════════════════════════════════════════
//  Métricas resumen
// ════════════════════════════════════════════════════════════════════
async function cargarMetricasResumen() {
    try {
        const [ocupacion, noshow, satisfaccion, asistencia] = await Promise.all([
            fetchReporte('ocupacion'),
            fetchReporte('no_show'),
            fetchReporte('satisfaccion'),
            fetchReporte('asistencia'),
        ]);
        const total       = ocupacion.reduce((s, r) => s + parseInt(r.total_visitas),   0);
        const citas       = ocupacion.reduce((s, r) => s + parseInt(r.total_citas),     0);
        const espontaneas = ocupacion.reduce((s, r) => s + parseInt(r.total_espontaneas), 0);
        const noshows     = noshow.reduce((s, r)    => s + parseInt(r.no_shows),         0);

        document.getElementById('m-total').textContent       = total.toLocaleString('es-CO');
        document.getElementById('m-citas').textContent       = citas.toLocaleString('es-CO');
        document.getElementById('m-espontaneas').textContent = espontaneas.toLocaleString('es-CO');
        document.getElementById('m-noshow').textContent      = noshows.toLocaleString('es-CO');

        if (total > 0) {
            document.getElementById('m-citas-pct').textContent       = `${Math.round(citas / total * 100)}% del total`;
            document.getElementById('m-espontaneas-pct').textContent = `${Math.round(espontaneas / total * 100)}% del total`;
        }

        // Tasa de asistencia real: completada+finalizada+en_curso vs no_asistio
        if (asistencia.length) {
            const a = asistencia[0];
            const tot = parseInt(a.total);
            if (tot > 0) {
                const asi  = parseInt(a.asistidas);
                const tasa = Math.round(asi / tot * 100);
                document.getElementById('m-asistencia').textContent     = `${tasa}%`;
                document.getElementById('m-asistencia-sub').textContent = `${asi} de ${tot} con resultado`;
            }
        }

        // Satisfacción promedio (sin decimales)
        const totalResp = satisfaccion.reduce((s, r) => s + parseInt(r.cantidad), 0);
        if (totalResp > 0) {
            const suma = satisfaccion.reduce((s, r) => s + parseInt(r.estrellas) * parseInt(r.cantidad), 0);
            const prom = Math.round(suma / totalResp);
            document.getElementById('m-satisfaccion').textContent     = `${prom} ★`;
            document.getElementById('m-satisfaccion-sub').textContent = `${totalResp} valoraciones`;
        }
    } catch (e) { console.error('Métricas:', e); }
}

// ════════════════════════════════════════════════════════════════════
//  Helpers
// ════════════════════════════════════════════════════════════════════
function destroyChart(id) {
    if (STATE.charts[id]) { STATE.charts[id].destroy(); delete STATE.charts[id]; }
}

function fmtHora(h)  { return `${String(h).padStart(2,'0')}:00`; }
function fmtFecha(f) { if (!f) return '—'; return new Date(f).toLocaleDateString('es-CO'); }
function fmtTS(ts)   { if (!ts) return '—'; return new Date(ts).toLocaleString('es-CO', { dateStyle:'short', timeStyle:'short' }); }
function fmtNum(n)   { return parseInt(n).toLocaleString('es-CO'); }

function badgeTipo(tipo) {
    return tipo === 'cita'
        ? '<span class="badge badge-cita"><i class="fas fa-calendar-check"></i> Cita</span>'
        : '<span class="badge badge-espontanea"><i class="fas fa-walking"></i> Espontánea</span>';
}

function badgeEstado(estado) {
    const map = {
        cancelada:                 '<span class="badge badge-cancelada">Cancelada</span>',
        propuesta_reprogramacion:  '<span class="badge badge-reprog">Prop. reprogramación</span>',
        contrapropuesta_ciudadano: '<span class="badge badge-contraprop">Contrapropuesta</span>',
    };
    return map[estado] ?? `<span class="badge" style="background:rgba(255,255,255,0.08);border:1px solid var(--borde);color:var(--texto-sub)">${estado}</span>`;
}

function filaVacia(cols, msg = 'Sin datos para el período seleccionado') {
    return `<tr><td colspan="${cols}" style="text-align:center;padding:2.5rem;color:var(--texto-sub)">
        <i class="fas fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:8px;color:rgba(255,255,255,0.25)"></i>
        ${msg}</td></tr>`;
}

// ════════════════════════════════════════════════════════════════════
//  R1 — Ocupación por dependencia
// ════════════════════════════════════════════════════════════════════
function renderOcupacion(data) {
    destroyChart('ocupacion');
    STATE.charts['ocupacion'] = new Chart(document.getElementById('chart-ocupacion'), {
        type: 'bar',
        data: {
            labels: data.map(r => r.dependencia),
            datasets: [
                { label: 'Citas',       data: data.map(r => parseInt(r.total_citas)),       backgroundColor: C.verde  },
                { label: 'Espontáneas', data: data.map(r => parseInt(r.total_espontaneas)), backgroundColor: C.dorado },
            ],
        },
        options: { ...CHART_DEFAULTS, scales: { ...CHART_DEFAULTS.scales, x: { ...CHART_DEFAULTS.scales.x, stacked: true }, y: { ...CHART_DEFAULTS.scales.y, stacked: true } } },
    });

    const tbody = document.querySelector('#tabla-ocupacion tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(5); return; }
    tbody.innerHTML = data.map(r => {
        const pct = r.total_visitas > 0 ? Math.round(r.total_citas / r.total_visitas * 100) : 0;
        return `<tr>
            <td><strong>${r.dependencia}</strong></td>
            <td><strong>${fmtNum(r.total_visitas)}</strong></td>
            <td>${fmtNum(r.total_citas)}</td>
            <td>${fmtNum(r.total_espontaneas)}</td>
            <td>${pct}%</td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R2 — Horas pico
// ════════════════════════════════════════════════════════════════════
function renderHoras(data) {
    destroyChart('horas');
    STATE.charts['horas'] = new Chart(document.getElementById('chart-horas'), {
        type: 'line',
        data: {
            labels: data.map(r => fmtHora(r.hora)),
            datasets: [
                { label: 'Total',       data: data.map(r => parseInt(r.total_visitas)), borderColor: C.verde,  backgroundColor: 'rgba(45,122,79,0.08)', fill: true, tension: 0.4, pointBackgroundColor: C.verde },
                { label: 'Citas',       data: data.map(r => parseInt(r.citas)),         borderColor: C.azul,   borderDash: [4,4], fill: false, tension: 0.4, pointBackgroundColor: C.azul  },
                { label: 'Espontáneas', data: data.map(r => parseInt(r.espontaneas)),   borderColor: C.dorado, borderDash: [2,2], fill: false, tension: 0.4, pointBackgroundColor: C.dorado},
            ],
        },
        options: CHART_DEFAULTS,
    });

    const tbody = document.querySelector('#tabla-horas tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(4); return; }
    tbody.innerHTML = data.map(r => `<tr>
        <td><strong>${fmtHora(r.hora)}</strong></td>
        <td>${r.total_visitas}</td>
        <td>${r.citas}</td>
        <td>${r.espontaneas}</td>
    </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R3 — Tiempo de espera
// ════════════════════════════════════════════════════════════════════
function renderEspera(data) {
    destroyChart('espera');

    const altura = Math.max(300, data.length * 45);
    document.getElementById('chart-espera')
        .closest('.chart-wrapper').style.height = altura + 'px';

    STATE.charts['espera'] = new Chart(document.getElementById('chart-espera'), {
        type: 'bar',
        data: {
            labels: data.map(r => r.funcionario),
            datasets: [{
                label: 'Espera prom. (min)',
                data:  data.map(r => parseFloat(r.espera_promedio_minutos)),
                backgroundColor: data.map(r =>
                    parseFloat(r.espera_promedio_minutos) > 15 ? C.rojo : C.verde
                ),
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.x} min promedio`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: C.tickColor, callback: v => v + ' min' },
                    grid: { color: C.gridLine },
                },
                y: {
                    ticks: { color: C.tickColor, font: { size: 11 } },
                    grid: { display: false },
                },
            },
        },
    });

    // Actualizar cabecera de tabla para incluir min/max
    document.querySelector('#tabla-espera thead tr').innerHTML = `
        <th>Funcionario</th>
        <th>Citas atendidas</th>
        <th>Espera mínima</th>
        <th>Espera promedio</th>
        <th>Espera máxima</th>
    `;

    const tbody = document.querySelector('#tabla-espera tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(5); return; }

    tbody.innerHTML = data.map(r => {
        const alto = parseFloat(r.espera_promedio_minutos) > 15;
        return `<tr>
            <td><strong>${r.funcionario}</strong></td>
            <td>${r.total_citas}</td>
            <td>${r.espera_minima} min</td>
            <td style="color:${alto ? 'var(--danger)' : 'var(--success)'}; font-weight:600">
                ${r.espera_promedio_minutos} min
            </td>
            <td>${r.espera_maxima} min</td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R4 — Duración
// ════════════════════════════════════════════════════════════════════
function renderDuracion(data) {
    const porDep = {};
    data.forEach(r => {
        if (!porDep[r.dependencia]) porDep[r.dependencia] = [];
        porDep[r.dependencia].push(parseFloat(r.duracion_promedio_minutos));
    });
    const labels = Object.keys(porDep);
    const values = labels.map(d => {
        const v = porDep[d];
        return Math.round(v.reduce((a,b) => a+b, 0) / v.length * 10) / 10;
    });

    destroyChart('duracion');
    STATE.charts['duracion'] = new Chart(document.getElementById('chart-duracion'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: 'Duración prom. (min)', data: values, backgroundColor: C.morado }],
        },
        options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } } },
    });

    const tbody = document.querySelector('#tabla-duracion tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(6); return; }
    tbody.innerHTML = data.map(r => `<tr>
        <td><strong>${r.dependencia}</strong></td>
        <td>${badgeTipo(r.tipo_registro)}</td>
        <td>${r.total_visitas}</td>
        <td><strong>${r.duracion_promedio_minutos} min</strong></td>
        <td>${r.duracion_minima} min</td>
        <td>${r.duracion_maxima} min</td>
    </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R5 — Motivos
// ════════════════════════════════════════════════════════════════════
function renderMotivos(data) {
    const top10   = data.slice(0, 10);
    const palette = [C.verde, C.dorado, C.azul, C.rojo, C.morado, C.naranja, C.cyan, C.rosa, C.lima, C.gris];

    destroyChart('motivos');
    STATE.charts['motivos'] = new Chart(document.getElementById('chart-motivos'), {
        type: 'doughnut',
        data: {
            labels: top10.map(r => r.motivo),
            datasets: [{
                data:            top10.map(r => parseInt(r.frecuencia)),
                backgroundColor: palette,
                borderColor:     'rgba(20,34,26,0.9)',
                borderWidth:     2,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { color: 'rgba(238,243,238,0.75)', font: { family: "'DM Sans', sans-serif", size: 12 }, boxWidth: 12 } } } },
    });

    const tbody = document.querySelector('#tabla-motivos tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(6); return; }
    tbody.innerHTML = data.map((r, i) => `<tr>
        <td style="color:var(--texto-sub);font-weight:600">${i + 1}</td>
        <td style="text-transform:capitalize">${r.motivo}</td>
        <td><strong>${r.frecuencia}</strong></td>
        <td>${r.desde_citas}</td>
        <td>${r.desde_espontaneas}</td>
        <td>${r.porcentaje}%</td>
    </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R6 — No-show
// ════════════════════════════════════════════════════════════════════
function renderNoShow(data) {
    destroyChart('noshow');
    STATE.charts['noshow'] = new Chart(document.getElementById('chart-noshow'), {
        type: 'bar',
        data: {
            labels: data.map(r => r.funcionario),
            datasets: [
                { label: 'Programadas', data: data.map(r => parseInt(r.total_programadas)), backgroundColor: C.verde },
                { label: 'No-shows',    data: data.map(r => parseInt(r.no_shows)),          backgroundColor: C.rojo  },
            ],
        },
        options: CHART_DEFAULTS,
    });

    const tbody = document.querySelector('#tabla-noshow tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(5); return; }
    tbody.innerHTML = data.map(r => {
        const alto = parseFloat(r.porcentaje_no_show) > 20;
        return `<tr>
            <td>${r.funcionario}</td>
            <td>${r.dependencia}</td>
            <td>${r.total_programadas}</td>
            <td>${r.no_shows}</td>
            <td style="color:${alto ? 'var(--danger)' : 'var(--texto)'}; font-weight:${alto ? '700' : '400'}">
                ${r.porcentaje_no_show}%
            </td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R7 — Trazabilidad
// ════════════════════════════════════════════════════════════════════
function renderTrazabilidad(data) {
    const tbody = document.querySelector('#tabla-trazabilidad tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(9); return; }
    tbody.innerHTML = data.map(r => {
        const motivo = r.motivo_cancelacion || r.motivo_reprogramacion || '—';
        const reprog = r.fecha_propuesta ? `${fmtFecha(r.fecha_propuesta)} ${r.hora_propuesta ?? ''}` : '—';
        return `<tr>
            <td style="color:var(--texto-sub)">${r.id_cita}</td>
            <td>${r.ciudadano}</td>
            <td>${r.funcionario}</td>
            <td>${r.dependencia}</td>
            <td>${fmtFecha(r.fecha_original)}</td>
            <td>${badgeEstado(r.estado)}</td>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                title="${motivo}">${motivo}</td>
            <td>${reprog}</td>
            <td>${r.cancelado_por_ciudadano ? 'Ciudadano' : 'Institución'}</td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R8 — Exportación plana
// ════════════════════════════════════════════════════════════════════
function renderExportacion(data) {
    const tbody = document.querySelector('#tabla-exportacion tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(11); return; }
    tbody.innerHTML = data.map(r => `<tr>
        <td>${badgeTipo(r.tipo_registro)}</td>
        <td>${r.ciudadano}</td>
        <td>${r.numero_identificacion}</td>
        <td>${r.funcionario}</td>
        <td>${r.dependencia}</td>
        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
            title="${r.motivo}">${r.motivo}</td>
        <td>${fmtFecha(r.fecha)}</td>
        <td>${fmtTS(r.hora_ingreso)}</td>
        <td>${fmtTS(r.hora_salida)}</td>
        <td>${r.estado}</td>
        <td>${r.duracion_minutos ?? '—'}</td>
    </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R9 — Satisfacción general
// ════════════════════════════════════════════════════════════════════
function estrellasHTML(valor) {
    const n = Math.round(parseFloat(valor));
    let s = '';
    for (let i = 1; i <= 5; i++) {
        s += `<i class="fas fa-star" style="color:${i <= n ? '#fbbf24' : 'rgba(255,255,255,0.18)'}"></i>`;
    }
    return s;
}

function renderSatisfaccion(data) {
    destroyChart('satisfaccion');

    const total    = data.reduce((s, r) => s + parseInt(r.cantidad), 0);
    const suma     = data.reduce((s, r) => s + parseInt(r.estrellas) * parseInt(r.cantidad), 0);
    const promedio = total > 0 ? (suma / total) : 0;

    document.getElementById('satisfaccion-resumen').innerHTML = `
        <div>
            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--texto-sub)">Promedio general</div>
            <div style="font-size:2rem;font-weight:700;color:var(--texto);line-height:1.1">${Math.round(promedio)} <span style="font-size:1rem;color:var(--texto-sub)">/ 5</span></div>
            <div style="font-size:1rem">${estrellasHTML(promedio)}</div>
        </div>
        <div>
            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--texto-sub)">Total de respuestas</div>
            <div style="font-size:2rem;font-weight:700;color:var(--verde-claro);line-height:1.1">${fmtNum(total)}</div>
            <div style="font-size:0.75rem;color:var(--texto-sub)">ciudadanos calificaron</div>
        </div>
    `;

    // Asegurar las 5 categorías aunque alguna tenga 0
    const mapa = {};
    data.forEach(r => { mapa[parseInt(r.estrellas)] = parseInt(r.cantidad); });
    const cats = [5, 4, 3, 2, 1];
    const colores = { 5: C.verde, 4: C.lima, 3: C.dorado, 2: C.naranja, 1: C.rojo };

    STATE.charts['satisfaccion'] = new Chart(document.getElementById('chart-satisfaccion'), {
        type: 'bar',
        data: {
            labels: cats.map(e => `${e} ★`),
            datasets: [{
                label: 'Calificaciones',
                data: cats.map(e => mapa[e] || 0),
                backgroundColor: cats.map(e => colores[e]),
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { color: C.tickColor, precision: 0 }, grid: { color: C.gridLine } },
                y: { ticks: { color: C.tickColor, font: { size: 12 } }, grid: { display: false } },
            },
        },
    });

    const tbody = document.querySelector('#tabla-satisfaccion tbody');
    if (!total) { tbody.innerHTML = filaVacia(3, 'Aún no hay valoraciones respondidas en el período'); return; }
    tbody.innerHTML = cats.map(e => `<tr>
        <td>${estrellasHTML(e)} <span style="color:var(--texto-sub);margin-left:6px">${e} estrella${e > 1 ? 's' : ''}</span></td>
        <td><strong>${fmtNum(mapa[e] || 0)}</strong></td>
        <td>${total > 0 ? Math.round((mapa[e] || 0) / total * 100) : 0}%</td>
    </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════
//  R10 — Calificación por funcionario
// ════════════════════════════════════════════════════════════════════
function renderCalifFunc(data) {
    destroyChart('califfunc');

    const altura = Math.max(300, data.length * 45);
    document.getElementById('chart-califfunc')
        .closest('.chart-wrapper').style.height = altura + 'px';

    const colorPorProm = p => p >= 4 ? C.verde : (p >= 3 ? C.dorado : C.rojo);

    STATE.charts['califfunc'] = new Chart(document.getElementById('chart-califfunc'), {
        type: 'bar',
        data: {
            labels: data.map(r => r.funcionario),
            datasets: [{
                label: 'Promedio (estrellas)',
                data: data.map(r => parseFloat(r.promedio_estrellas)),
                backgroundColor: data.map(r => colorPorProm(parseFloat(r.promedio_estrellas))),
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} de 5 estrellas` } },
            },
            scales: {
                x: { beginAtZero: true, max: 5, ticks: { color: C.tickColor, stepSize: 1 }, grid: { color: C.gridLine } },
                y: { ticks: { color: C.tickColor, font: { size: 11 } }, grid: { display: false } },
            },
        },
    });

    const tbody = document.querySelector('#tabla-califfunc tbody');
    if (!data.length) { tbody.innerHTML = filaVacia(5, 'Aún no hay valoraciones respondidas en el período'); return; }
    tbody.innerHTML = data.map(r => {
        const prom = parseFloat(r.promedio_estrellas);
        const col  = prom >= 4 ? 'var(--success)' : (prom >= 3 ? 'var(--warning)' : 'var(--danger)');
        return `<tr>
            <td><strong>${r.funcionario}</strong></td>
            <td>${r.dependencia}</td>
            <td>${r.total_valoraciones}</td>
            <td style="white-space:nowrap">
                <span style="color:${col};font-weight:700">${Math.round(prom)} ★</span>
                <span style="margin-left:6px">${estrellasHTML(prom)}</span>
            </td>
            <td>${r.pct_solucionado}%</td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════════════════════
//  Exportar CSV
// ════════════════════════════════════════════════════════════════════
async function exportarExcel(reporte, event) {
    event.stopPropagation();
    try {
        const params = new URLSearchParams({
            reporte,
            fecha_inicio:   STATE.filtros.fecha_inicio,
            fecha_fin:      STATE.filtros.fecha_fin,
            dependencia_id: STATE.filtros.dependencia_id ?? '',
            funcionario_id: STATE.filtros.funcionario_id ?? '',
            tab_id:         TAB_ID,
        });

        const url = `/reportes/excel?${params.toString()}`;
        const a   = Object.assign(document.createElement('a'), {
            href:     url,
            download: '',
        });
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } catch (e) {
        alert('Error exportando Excel: ' + e.message);
    }
}

// ════════════════════════════════════════════════════════════════════
//  Exportar PDF
// ════════════════════════════════════════════════════════════════════
async function exportarPDF(reporte, event) {
    event.stopPropagation();
    try {
        const data = await fetchReporte(reporte);
        if (!data.length) return alert('No hay datos para exportar.');

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });

        // ── Logo ──────────────────────────────────────────────
        const logoUrl    = '/imagenes/logoalcaldia.jpg';
        const logoBase64 = await imagenABase64(logoUrl);
        doc.addImage(logoBase64, 'JPEG', 14, 6, 18, 18);

        // ── Encabezado ────────────────────────────────────────
        doc.setFontSize(15);
        doc.setTextColor(26, 92, 56);
        doc.text('Alcaldía Municipal de Monterrey · Casanare', 36, 14);

        doc.setFontSize(11);
        doc.setTextColor(40);
        doc.text(`Reporte: ${reporte.replace(/_/g,' ').toUpperCase()}`, 36, 21);

        doc.setFontSize(8);
        doc.setTextColor(100);
        doc.text(
            `Período: ${STATE.filtros.fecha_inicio} — ${STATE.filtros.fecha_fin}   |   Generado: ${new Date().toLocaleString('es-CO')}`,
            36, 27
        );

        // ── Gráfica ───────────────────────────────────────────
        const canvasId = {
            ocupacion:  'chart-ocupacion',
            horas_pico: 'chart-horas',
            espera:     'chart-espera',
            duracion:   'chart-duracion',
            motivos:    'chart-motivos',
            no_show:    'chart-noshow',
            satisfaccion:             'chart-satisfaccion',
            satisfaccion_funcionario: 'chart-califfunc',
        }[reporte];

        let startY = 35;

        if (canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                const imgData = canvas.toDataURL('image/png');
                // Página landscape: 297mm ancho. Dejamos márgenes de 14 cada lado → 269mm
                // Alto proporcional: 269 * (canvas.height / canvas.width)
                const maxW  = 269;
                const ratio = canvas.height / canvas.width;
                const imgH  = Math.min(maxW * ratio, 100); // máximo 100mm de alto
                doc.addImage(imgData, 'PNG', 14, 33, maxW, imgH);
                startY = 33 + imgH + 6;
            }
        }

        // ── Tabla ─────────────────────────────────────────────
        const headers = Object.keys(data[0]);
        doc.autoTable({
            startY,
            head:       [headers],
            body:       data.map(r => headers.map(h => r[h] ?? '')),
            styles:     { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [26, 92, 56], textColor: [201, 168, 76] },
            alternateRowStyles: { fillColor: [248, 249, 252] },
        });

        doc.save(`reporte_${reporte}_${STATE.filtros.fecha_inicio}_${STATE.filtros.fecha_fin}.pdf`);
    } catch (e) { alert('Error exportando PDF: ' + e.message); }
}

function imagenABase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width  = img.width;
            canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url;
    });
}
</script>

</body>
</html>
