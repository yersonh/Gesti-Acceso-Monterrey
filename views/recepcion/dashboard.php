<?php
// Vista — la lógica está en RecepcionController::dashboard()
// Variables: $registros, $alertas, $dependencias, $mensaje, $error
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCV — Recepción</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde: #1a5c38;
            --verde-medio: #2d7a4f;
            --verde-claro: #3d9e68;
            --dorado: #c9a84c;
            --dorado-claro: #e8c97a;
            --blanco: #ffffff;
            --texto: #eef3ee;
            --texto-sub: rgba(238, 243, 238, 0.6);
            --borde: rgba(255, 255, 255, 0.12);
            --fondo: rgba(0, 0, 0, 0.22);
            --vidrio: rgba(20, 34, 26, 0.45);
            --rojo: #ef4444;
            --amarillo: #f59e0b;
            --azul: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .header {
            background: rgba(20, 34, 26, 0.5);
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
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
        }

        .header-brand { display: flex; align-items: center; gap: 14px; text-decoration: none; }
        .header-brand img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--dorado); }
        .header-brand-texto { color: var(--blanco); font-size: 0.8rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; line-height: 1.4; }
        .header-brand-texto strong { display: block; font-size: 0.92rem; color: var(--dorado-claro); letter-spacing: 0.04em; }

        .header-titulo { font-weight: 700; font-size: 0.95rem; color: var(--blanco); display: flex; align-items: center; gap: 8px; }
        .header-titulo i { color: var(--dorado-claro); }

        .header-usuario { display: flex; align-items: center; gap: 20px; }
        .info-funcionario { text-align: right; }
        .funcionario-nombre { color: var(--blanco); font-weight: 600; font-size: 0.9rem; }
        .funcionario-cargo { color: var(--dorado-claro); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(201,168,76,0.25); border: 2px solid var(--dorado);
            color: var(--dorado-claro); font-size: 0.85rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-logout {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco); padding: 7px 14px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.2); }

        .main { max-width: 1300px; margin: 0 auto; padding: 36px 24px 60px; }

        .alertas { margin-bottom: 24px; }
        .alerta {
            padding: 14px 18px; border-radius: 10px; display: flex; align-items: center; gap: 10px;
            font-size: 0.9rem; font-weight: 500; animation: slideIn 0.3s ease; margin-bottom: 10px;
        }
        .alerta-success { background: rgba(52,211,153,0.14); border: 1px solid rgba(110,231,183,0.4); border-left: 4px solid #34d399; color: #6ee7b7; }
        .alerta-error { background: rgba(248,113,113,0.14); border: 1px solid rgba(252,165,165,0.4); border-left: 4px solid #ef4444; color: #fca5a5; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .alertas-panel { margin-bottom: 24px; }
        .alerta-fila {
            background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.35); border-radius: 10px;
            padding: 12px 16px; display: flex; align-items: center; gap: 12px;
            font-size: 0.85rem; color: #fcd34d; margin-bottom: 8px;
        }
        .alerta-fila i { color: var(--amarillo); font-size: 1rem; flex-shrink: 0; }
        .alerta-fila strong { color: var(--blanco); }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde); border-radius: 16px;
            padding: 24px; display: flex; align-items: center; gap: 16px; transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.35); }
        .stat-icono { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        .stat-card.total .stat-icono { background: rgba(251,191,36,0.18); color: #fcd34d; }
        .stat-card.curso .stat-icono { background: rgba(52,211,153,0.16); color: #6ee7b7; }
        .stat-card.final .stat-icono { background: rgba(255,255,255,0.08); color: var(--texto-sub); }
        .stat-card.espon .stat-icono { background: rgba(96,165,250,0.18); color: #93c5fd; }
        .stat-info h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; color: var(--texto); }
        .stat-info p { color: var(--texto-sub); font-size: 0.85rem; font-weight: 500; }

        .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .buscador { position: relative; flex: 1; min-width: 240px; max-width: 380px; }
        .buscador i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--texto-sub); font-size: 0.85rem; }
        .buscador input {
            width: 100%; padding: 10px 14px 10px 36px; border: 1.5px solid var(--borde); border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.87rem; background: rgba(0,0,0,0.28); color: var(--texto);
        }
        .buscador input::placeholder { color: rgba(255,255,255,0.4); }
        .buscador input:focus { outline: none; border-color: var(--dorado); box-shadow: 0 0 0 3px rgba(201,168,76,0.18); background: rgba(0,0,0,0.38); }

        .btn {
            padding: 10px 20px; border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center;
            gap: 8px; transition: all 0.2s; text-decoration: none;
        }
        .btn-primary { background: linear-gradient(135deg, var(--dorado-claro), var(--dorado)); color: #2c2107; box-shadow: 0 4px 14px rgba(201,168,76,0.2); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(201,168,76,0.35); }
        .btn-danger { background: rgba(248,113,113,0.12); border: 1.5px solid rgba(248,113,113,0.4); color: #fca5a5; }
        .btn-danger:hover { background: var(--rojo); color: white; }
        .btn-warning { background: rgba(251,191,36,0.12); border: 1.5px solid rgba(251,191,36,0.4); color: #fcd34d; }
        .btn-warning:hover { background: var(--amarillo); color: #2c2107; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .btn-outline { background: rgba(255,255,255,0.06); border: 1px solid var(--borde); color: var(--texto-sub); }
        .btn-outline:hover { border-color: var(--dorado); color: var(--dorado-claro); background: rgba(201,168,76,0.1); }

        .card { background: var(--vidrio); backdrop-filter: blur(16px) saturate(140%); -webkit-backdrop-filter: blur(16px) saturate(140%); border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; }
        .card-header {
            padding: 20px 24px; border-bottom: 1px solid var(--borde);
            background: rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h2 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--texto); display: flex; align-items: center; gap: 8px; }
        .card-header i { color: var(--dorado-claro); font-size: 1.2rem; }
        .card-header .badge { background: rgba(201,168,76,0.18); color: var(--dorado-claro); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .card-body { padding: 0; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
            color: var(--texto-sub); padding: 12px 24px; border-bottom: 1px solid var(--borde); background: rgba(0,0,0,0.2);
        }
        tbody td { padding: 12px 24px; font-size: 0.85rem; color: var(--texto); border-bottom: 1px solid rgba(255,255,255,0.06); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.04); }

        .persona-nombre { font-weight: 600; color: var(--texto); }
        .persona-sub { font-size: 0.76rem; color: var(--texto-sub); margin-top: 2px; }

        .badge-estado { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; white-space: nowrap; }
        .badge-estado::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-estado.en_curso    { background: rgba(52,211,153,0.16); color: #6ee7b7; }
        .badge-estado.confirmada  { background: rgba(96,165,250,0.18); color: #93c5fd; }
        .badge-estado.finalizada  { background: rgba(255,255,255,0.08); color: var(--texto-sub); }
        .badge-estado.cancelada   { background: rgba(248,113,113,0.16); color: #fca5a5; }
        .badge-estado.no_asistio  { background: rgba(251,191,36,0.18); color: #fcd34d; }
        .badge-estado.pendiente,
        .badge-estado.propuesta_reprogramacion,
        .badge-estado.contrapropuesta_ciudadano { background: rgba(201,168,76,0.16); color: var(--dorado-claro); }

        .badge-tipo { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; }
        .badge-tipo.cita { background: rgba(52,211,153,0.16); color: #6ee7b7; }
        .badge-tipo.espontanea { background: rgba(96,165,250,0.18); color: #93c5fd; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--texto-sub); }
        .empty-state i { font-size: 2.4rem; margin-bottom: 12px; opacity: 0.4; }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center;
            z-index: 1000; backdrop-filter: blur(4px);
        }
        .modal {
            background: rgba(20, 34, 26, 0.75);
            backdrop-filter: blur(20px) saturate(140%);
            -webkit-backdrop-filter: blur(20px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: 20px; padding: 0; max-width: 640px; width: 92%;
            max-height: 90vh; overflow-y: auto; animation: modalSlideIn 0.3s ease;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        }
        @keyframes modalSlideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        .modal-header { padding: 24px 32px 0; display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--texto); display: flex; align-items: center; gap: 8px; }
        .modal-close { background: none; border: none; font-size: 1.1rem; color: var(--texto-sub); cursor: pointer; }
        .modal-close:hover { color: #fca5a5; }
        .modal-body { padding: 20px 32px; }
        .modal-acciones, .modal-footer { padding: 0 32px 24px; display: flex; justify-content: flex-end; gap: 12px; }

        .btn-modal-cancelar {
            padding: 12px 24px; background: rgba(255,255,255,0.06); border: 1.5px solid var(--borde); border-radius: 8px;
            color: var(--texto); font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-modal-cancelar:hover { border-color: var(--dorado); color: var(--dorado-claro); }
        .btn-modal-confirmar {
            padding: 12px 24px; border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-weight: 700; color: #2c2107; cursor: pointer; transition: all 0.2s;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }
        .btn-modal-confirmar:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.85rem; color: var(--texto); }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.9rem; background: rgba(0,0,0,0.28); color: var(--texto);
        }
        .form-group input::placeholder, .form-group textarea::placeholder { color: rgba(255,255,255,0.4); }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--dorado); box-shadow: 0 0 0 3px rgba(201,168,76,0.18); background: rgba(0,0,0,0.38); }
        .form-group input:disabled, .form-group select:disabled { background: rgba(255,255,255,0.04); color: var(--texto-sub); }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .grupo-verificar { display: flex; gap: 8px; align-items: flex-end; }
        .grupo-verificar .form-group { flex: 1; margin-bottom: 0; }
        #verif-estado { font-size: 0.8rem; margin-top: 8px; min-height: 18px; color: var(--texto-sub); }

        /* ── Scrollbar global ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.4); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--dorado); }

        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
            .main { padding: 24px 16px 48px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .main { padding: 16px 12px 48px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 16px; gap: 10px; }
            .stat-icono { width: 40px; height: 40px; font-size: 1.2rem; }
            .stat-info h3 { font-size: 1.3rem; }
            .info-funcionario { display: none; }
            .btn-logout span { display: none; }
            table { font-size: 0.78rem; }
            thead th, tbody td { padding: 9px 12px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/recepcion" class="header-brand">
            <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            <div class="header-brand-texto">
                Alcaldía Municipal<br>
                <strong>Monterrey · Casanare</strong>
            </div>
        </a>
        <div class="header-titulo"><i class="fas fa-desktop"></i> Recepción</div>
        <div class="header-usuario">
            <div class="info-funcionario">
                <div class="funcionario-nombre"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Recepcionista') ?></div>
                <div class="funcionario-cargo">Recepcionista</div>
            </div>
            <div class="avatar">
                <?php
                    $nombreSesion = trim($_SESSION['usuario_nombre'] ?? 'R');
                    $partesNombre = explode(' ', $nombreSesion);
                    echo strtoupper(substr($partesNombre[0] ?? 'R', 0, 1) . substr($partesNombre[1] ?? '', 0, 1));
                ?>
            </div>
            <a href="/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> <span>Salir</span></a>
        </div>
    </header>

    <main class="main">

        <?php if ($mensaje): ?>
        <div class="alertas">
            <div class="alerta alerta-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alertas">
            <div class="alerta alerta-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        </div>
        <?php endif; ?>

        <div class="alertas-panel" id="alertas-panel">
            <?php foreach ($alertas as $a): ?>
            <div class="alerta-fila">
                <i class="fas fa-bell"></i>
                <span>
                    <strong><?= htmlspecialchars(($a['nombres_ciudadano'] ?? '') . ' ' . ($a['apellidos_ciudadano'] ?? '')) ?></strong>
                    tiene cita a las
                    <strong><?= !empty($a['hora_inicio']) ? date('h:i A', strtotime($a['hora_inicio'])) : '—' ?></strong>
                    con <?= htmlspecialchars(($a['nombres_funcionario'] ?? '') . ' ' . ($a['apellidos_funcionario'] ?? '')) ?>
                    (<?= htmlspecialchars($a['dependencia'] ?? '') ?>)
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="stats-grid" id="stats-grid">
            <div class="stat-card total">
                <div class="stat-icono"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-info"><h3 id="stat-total"><?= count($registros) ?></h3><p>Registros Hoy</p></div>
            </div>
            <div class="stat-card curso">
                <div class="stat-icono"><i class="fas fa-walking"></i></div>
                <div class="stat-info"><h3 id="stat-curso"><?= count(array_filter($registros, fn($r) => $r['estado'] === 'en_curso')) ?></h3><p>En Curso</p></div>
            </div>
            <div class="stat-card final">
                <div class="stat-icono"><i class="fas fa-check-double"></i></div>
                <div class="stat-info"><h3 id="stat-final"><?= count(array_filter($registros, fn($r) => $r['estado'] === 'finalizada')) ?></h3><p>Finalizadas</p></div>
            </div>
            <div class="stat-card espon">
                <div class="stat-icono"><i class="fas fa-user-plus"></i></div>
                <div class="stat-info"><h3 id="stat-espon"><?= count(array_filter($registros, fn($r) => $r['tipo_registro'] === 'espontanea')) ?></h3><p>Espontáneas</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="buscador">
                <i class="fas fa-search"></i>
                <input type="text" id="buscador-input" placeholder="Buscar por nombre o identificación…">
            </div>
            <button class="btn btn-primary" style="margin-left:auto;" onclick="abrirModalEspontanea()">
                <i class="fas fa-user-plus"></i> Visita Espontánea
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-clipboard-list"></i> Registros de Hoy</h2>
                <span class="badge" id="registros-badge"><?= count($registros) ?></span>
            </div>
            <div class="card-body">
                <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Identificación</th>
                            <th>Funcionario</th>
                            <th>Dependencia</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Ingreso</th>
                            <th>Salida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="registros-tbody">
                        <?php
                        $etiquetasEstado = [
                            'en_curso' => 'En curso', 'confirmada' => 'Confirmada', 'finalizada' => 'Finalizada',
                            'cancelada' => 'Cancelada', 'no_asistio' => 'No asistió', 'pendiente' => 'Pendiente',
                            'propuesta_reprogramacion' => 'Propuesta enviada', 'contrapropuesta_ciudadano' => 'Contrapropuesta',
                        ];
                        $fmtHora = function ($valor) {
                            if (!$valor) return '—';
                            $ts = strtotime($valor);
                            return $ts ? date('h:i A', $ts) : $valor;
                        };
                        ?>
                        <?php foreach ($registros as $r): ?>
                        <?php
                            $nombre      = trim(($r['nombres_ciudadano'] ?? '') . ' ' . ($r['apellidos_ciudadano'] ?? ''));
                            $funcionario = trim(($r['nombres_funcionario'] ?? '') . ' ' . ($r['apellidos_funcionario'] ?? ''));
                            $horario = $r['tipo_registro'] === 'cita'
                                ? (!empty($r['fecha_cita']) ? date('d/m/Y', strtotime($r['fecha_cita'])) : '') . ' · ' . $fmtHora($r['hora_inicio'] ?? null)
                                : 'Espontánea · ' . $fmtHora($r['hora_ingreso'] ?? null);
                            $estado = $r['estado'];
                        ?>
                        <tr>
                            <td>
                                <div class="persona-nombre"><?= htmlspecialchars($nombre ?: '—') ?></div>
                                <div class="persona-sub">
                                    <span class="badge-tipo <?= htmlspecialchars($r['tipo_registro']) ?>">
                                        <?= $r['tipo_registro'] === 'cita' ? 'Cita' : 'Espontánea' ?>
                                    </span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars(trim(($r['tipo_identificacion'] ?? '') . ' ' . ($r['numero_identificacion'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars($funcionario ?: '—') ?></td>
                            <td><?= htmlspecialchars($r['dependencia'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($horario) ?></td>
                            <td><span class="badge-estado <?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($etiquetasEstado[$estado] ?? $estado) ?></span></td>
                            <td><?= htmlspecialchars($fmtHora($r['hora_ingreso'] ?? null)) ?></td>
                            <td><?= htmlspecialchars($fmtHora($r['hora_salida'] ?? null)) ?></td>
                            <td>
                                <?php if ($r['tipo_registro'] === 'cita' && $estado === 'confirmada'): ?>
                                <form method="POST" action="/recepcion" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <?= tab_id_field() ?>
                                    <input type="hidden" name="accion" value="registrar_ingreso">
                                    <input type="hidden" name="id_cita" value="<?= (int)$r['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingreso</button>
                                </form>
                                <?php endif; ?>
                                <?php if (!($r['tipo_registro'] === 'cita' && $estado === 'confirmada')): ?>—<?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php if (empty($registros)): ?>
                <div class="empty-state" id="tabla-vacia">
                    <i class="fas fa-inbox"></i>
                    <p>No hay registros para hoy.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal Visita Espontánea -->
    <div class="modal-overlay" id="modal-espontanea">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus" style="color:var(--verde-medio);"></i> Visita Espontánea</h3>
                <button class="modal-close" onclick="cerrarModal('modal-espontanea')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="/recepcion" id="form-espontanea">
                <?= csrf_field() ?>
                <?= tab_id_field() ?>
                <input type="hidden" name="accion" value="visita_espontanea">
                <input type="hidden" name="ciudadano_id" id="esp-ciudadano_id" value="0">
                <div class="modal-body">

                    <div class="grupo-verificar">
                        <div class="form-group" style="max-width:110px;">
                            <label>Tipo ID *</label>
                            <select name="tipo_identificacion" id="esp-tipo_identificacion" required>
                                <option value="CC">CC</option>
                                <option value="TI">TI</option>
                                <option value="CE">CE</option>
                                <option value="PA">PA</option>
                                <option value="RC">RC</option>
                                <option value="NIT">NIT</option>
                                <option value="PEP">PEP</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de identificación *</label>
                            <input type="text" name="numero_identificacion" id="esp-numero_identificacion" required maxlength="20">
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="verificarCiudadano()">
                            <i class="fas fa-search"></i> Verificar
                        </button>
                    </div>
                    <div id="verif-estado"></div>

                    <div class="form-row" style="margin-top:16px;">
                        <div class="form-group">
                            <label>Nombres *</label>
                            <input type="text" name="nombres" id="esp-nombres" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos *</label>
                            <input type="text" name="apellidos" id="esp-apellidos" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="esp-telefono">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="esp-email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="text" name="whatsapp" id="esp-whatsapp">
                        </div>
                        <div class="form-group">
                            <label>Proveniencia</label>
                            <input type="text" name="proveniencia" id="esp-proveniencia" placeholder="Ej: Yopal, Villavicencio…">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" id="esp-direccion">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Dependencia *</label>
                            <select name="dependencia_id" id="esp-dependencia_id" required onchange="cargarFuncionarios()">
                                <option value="">Seleccione...</option>
                                <?php foreach ($dependencias as $dep): ?>
                                <option value="<?= (int)$dep['id_dependencia'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Funcionario *</label>
                            <select name="funcionario_id" id="esp-funcionario_id" required disabled>
                                <option value="">Seleccione una dependencia primero</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Motivo de la visita *</label>
                        <textarea name="motivo" id="esp-motivo" required placeholder="Describa el motivo de la visita"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Notas (opcional)</label>
                        <textarea name="notas" id="esp-notas" placeholder="Observaciones adicionales"></textarea>
                    </div>

                </div>
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modal-espontanea')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar"><i class="fas fa-save"></i> Registrar Visita</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const TAB_ID = window.TAB_ID;

        function abrirModalEspontanea() {
            document.getElementById('modal-espontanea').style.display = 'flex';
        }
        function cerrarModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // ── Verificar ciudadano existente ────────────────────────────────
        function verificarCiudadano() {
            const numero = document.getElementById('esp-numero_identificacion').value.trim();
            const estadoDiv = document.getElementById('verif-estado');
            if (!numero) { estadoDiv.textContent = ''; return; }

            estadoDiv.textContent = 'Verificando…';
            estadoDiv.style.color = 'var(--texto-sub)';

            fetch('/recepcion/verificar?numero=' + encodeURIComponent(numero) + '&tab_id=' + TAB_ID)
                .then(r => r.json())
                .then(data => {
                    const campos = ['nombres', 'apellidos', 'telefono', 'email', 'whatsapp', 'direccion', 'proveniencia'];
                    if (data.encontrado) {
                        const c = data.ciudadano;
                        document.getElementById('esp-ciudadano_id').value = c.id_ciudadano;
                        document.getElementById('esp-tipo_identificacion').value = c.tipo_identificacion;

                        campos.forEach(campo => {
                            const input = document.getElementById('esp-' + campo);
                            const valor = c[campo] || '';
                            input.value = valor;
                            // Bloqueado si ya tiene valor, editable si vacío (para completar datos faltantes)
                            input.disabled = valor !== '';
                        });

                        estadoDiv.innerHTML = '<i class="fas fa-check-circle" style="color:var(--verde-medio);"></i> Ciudadano encontrado — datos cargados';
                        estadoDiv.style.color = 'var(--verde-medio)';
                    } else {
                        document.getElementById('esp-ciudadano_id').value = 0;
                        campos.forEach(campo => {
                            const input = document.getElementById('esp-' + campo);
                            input.disabled = false;
                        });
                        estadoDiv.innerHTML = '<i class="fas fa-info-circle" style="color:var(--dorado);"></i> No encontrado — se registrará como nuevo';
                        estadoDiv.style.color = 'var(--dorado)';
                    }
                })
                .catch(() => {
                    estadoDiv.textContent = 'Error al verificar. Intenta de nuevo.';
                    estadoDiv.style.color = 'var(--rojo)';
                });
        }

        // ── Cargar funcionarios según dependencia ────────────────────────
        function cargarFuncionarios() {
            const depId = document.getElementById('esp-dependencia_id').value;
            const select = document.getElementById('esp-funcionario_id');

            if (!depId) {
                select.innerHTML = '<option value="">Seleccione una dependencia primero</option>';
                select.disabled = true;
                return;
            }

            select.disabled = true;
            select.innerHTML = '<option value="">Cargando…</option>';

            fetch('/agendar?action=funcionarios&dependencia_id=' + depId + '&tab_id=' + TAB_ID)
                .then(r => r.json())
                .then(funcionarios => {
                    if (!funcionarios.length) {
                        select.innerHTML = '<option value="">Sin funcionarios disponibles</option>';
                        return;
                    }
                    select.innerHTML = '<option value="">Seleccione...</option>' +
                        funcionarios.map(f => `<option value="${f.id_funcionario}">${f.nombres} ${f.apellidos}${f.cargo ? ' — ' + f.cargo : ''}</option>`).join('');
                    select.disabled = false;
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Error al cargar</option>';
                });
        }

        // ── Buscador con debounce ─────────────────────────────────────────
        let terminoActual = '';
        let debounceId;
        document.getElementById('buscador-input').addEventListener('input', function() {
            clearTimeout(debounceId);
            const valor = this.value.trim();
            debounceId = setTimeout(() => {
                terminoActual = valor;
                actualizarDatos();
            }, 300);
        });

        // ── Polling / actualización de tabla, stats y alertas ────────────
        function estadoBadge(estado) {
            const etiquetas = {
                en_curso: 'En curso', confirmada: 'Confirmada', finalizada: 'Finalizada',
                cancelada: 'Cancelada', no_asistio: 'No asistió', pendiente: 'Pendiente',
                propuesta_reprogramacion: 'Propuesta enviada', contrapropuesta_ciudadano: 'Contrapropuesta',
            };
            return `<span class="badge-estado ${estado}">${etiquetas[estado] || estado}</span>`;
        }

        function formatoHora(valor) {
            if (!valor) return '—';
            const d = new Date(valor.includes('T') ? valor : valor.replace(' ', 'T'));
            if (isNaN(d)) return valor;
            return d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
        }

        function renderFila(r) {
            const nombre = `${r.nombres_ciudadano || ''} ${r.apellidos_ciudadano || ''}`.trim();
            const funcionario = `${r.nombres_funcionario || ''} ${r.apellidos_funcionario || ''}`.trim();
            const horario = r.tipo_registro === 'cita'
                ? (r.fecha_cita ? new Date(r.fecha_cita).toLocaleDateString('es-CO') : '') + ' · ' + formatoHora(r.hora_inicio)
                : 'Espontánea · ' + formatoHora(r.hora_ingreso);

            let acciones = '';
            if (r.tipo_registro === 'cita' && r.estado === 'confirmada') {
                acciones += `<form method="POST" action="/recepcion" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="${window.CSRF_TOKEN}">
                    <input type="hidden" name="tab_id" value="${TAB_ID}">
                    <input type="hidden" name="accion" value="registrar_ingreso">
                    <input type="hidden" name="id_cita" value="${r.id}">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingreso</button>
                </form> `;
            }

            return `<tr>
                <td>
                    <div class="persona-nombre">${nombre || '—'}</div>
                    <div class="persona-sub"><span class="badge-tipo ${r.tipo_registro}">${r.tipo_registro === 'cita' ? 'Cita' : 'Espontánea'}</span></div>
                </td>
                <td>${r.tipo_identificacion || ''} ${r.numero_identificacion || ''}</td>
                <td>${funcionario || '—'}</td>
                <td>${r.dependencia || '—'}</td>
                <td>${horario}</td>
                <td>${estadoBadge(r.estado)}</td>
                <td>${formatoHora(r.hora_ingreso)}</td>
                <td>${formatoHora(r.hora_salida)}</td>
                <td>${acciones || '—'}</td>
            </tr>`;
        }

        function actualizarDatos() {
            const params = new URLSearchParams({ tab_id: TAB_ID });
            if (terminoActual) params.set('q', terminoActual);

            fetch('/ajax/recepcion_poll?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) return;

                    const tbody = document.getElementById('registros-tbody');
                    tbody.innerHTML = data.registros.map(renderFila).join('');

                    const vacio = document.getElementById('tabla-vacia');
                    if (data.registros.length === 0) {
                        if (!vacio) {
                            const div = document.createElement('div');
                            div.className = 'empty-state';
                            div.id = 'tabla-vacia';
                            div.innerHTML = '<i class="fas fa-inbox"></i><p>No hay registros para hoy.</p>';
                            tbody.closest('.card-body').appendChild(div);
                        }
                    } else if (vacio) {
                        vacio.remove();
                    }

                    document.getElementById('stat-total').textContent = data.stats.total;
                    document.getElementById('stat-curso').textContent = data.stats.en_curso;
                    document.getElementById('stat-final').textContent = data.stats.finalizadas;
                    document.getElementById('stat-espon').textContent = data.stats.espontaneas;
                    document.getElementById('registros-badge').textContent = data.stats.total;

                    const panel = document.getElementById('alertas-panel');
                    panel.innerHTML = data.alertas.map(a => `
                        <div class="alerta-fila">
                            <i class="fas fa-bell"></i>
                            <span>
                                <strong>${(a.nombres_ciudadano || '') + ' ' + (a.apellidos_ciudadano || '')}</strong>
                                tiene cita a las
                                <strong>${formatoHora(a.hora_inicio)}</strong>
                                con ${(a.nombres_funcionario || '') + ' ' + (a.apellidos_funcionario || '')}
                                (${a.dependencia || ''})
                            </span>
                        </div>
                    `).join('');
                })
                .catch(err => console.error('Error de polling:', err));
        }

        window.CSRF_TOKEN = document.querySelector('#form-espontanea input[name="csrf_token"]').value;
        setInterval(actualizarDatos, 10000);
    </script>
</body>
</html>
