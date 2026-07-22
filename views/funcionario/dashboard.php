<?php
// Vista — la lógica está en FuncionarioController::dashboard()
// Variables: $funcionario, $funcionarioId, $usuario, $resumen
// $solicitudesPendientes, $dependencias, $horariosBloqueados, $mensaje, $error
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
<script>
window.configSistema = <?= json_encode([
    'diasHabiles' => $diasHabiles   ?? [],
    '_festivos'   => $festivosConfig ?? [],
]) ?>;
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Panel Funcionario</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            --texto-sub: rgba(255, 255, 255, 0.6);
            --borde: rgba(255, 255, 255, 0.12);
            --fondo: rgba(0, 0, 0, 0.22);
            --vidrio: rgba(20, 34, 26, 0.45);
            --rojo: #ef4444;
            --amarillo: #f59e0b;
            --azul: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
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
            background: rgba(20, 34, 26, 0.55);
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border-bottom: 1px solid var(--borde);
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
            display: block; font-size: 0.92rem; color: var(--dorado-claro); letter-spacing: 0.04em;
        }
        
        .header-usuario {
            display: flex; align-items: center; gap: 20px;
        }
        
        .info-funcionario {
            text-align: right;
        }
        
        .funcionario-nombre {
            color: var(--blanco);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .funcionario-cargo {
            color: var(--dorado-claro);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(201,168,76,0.25); border: 2px solid var(--dorado);
            color: var(--dorado-claro); font-size: 0.85rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco); padding: 7px 14px; border-radius: 8px;
            font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }
        
        .btn-logout:hover { background: rgba(255,255,255,0.2); }
        
        .main {
            max-width: 1300px;
            margin: 0 auto;
            padding: 36px 24px 60px;
        }
        
        .bienvenida {
            margin-bottom: 32px;
        }
        
        .bienvenida h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--texto);
            margin-bottom: 8px;
        }
        
        .bienvenida h1 span {
            color: var(--verde-medio);
        }
        
        .bienvenida p {
            color: var(--texto-sub);
            font-size: 0.95rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 34px rgba(0,0,0,0.35);
        }

        .stat-icono {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card.pendientes .stat-icono {
            background: rgba(251,191,36,0.18);
            color: #fcd34d;
        }

        .stat-card.hoy .stat-icono {
            background: rgba(96,165,250,0.18);
            color: #93c5fd;
        }

        .stat-card.proximas .stat-icono {
            background: rgba(52,211,153,0.16);
            color: #6ee7b7;
        }

        .stat-card.historial .stat-icono {
            background: rgba(255,255,255,0.08);
            color: var(--texto-sub);
        }
        
        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-info p {
            color: var(--texto-sub);
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .alertas {
            margin-bottom: 24px;
        }
        
        .alerta {
            padding: 14px 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        .alerta-success {
            background: rgba(52,211,153,0.14);
            border-left: 4px solid #34d399;
            color: #6ee7b7;
        }

        .alerta-error {
            background: rgba(248,113,113,0.14);
            border-left: 4px solid #f87171;
            color: #fca5a5;
        }
        
        .grid-2-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .card {
            background: var(--vidrio);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--borde);
            background: rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--texto);
        }
        
        .card-header i {
            color: var(--verde-medio);
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 20px 24px;
        }
        
        /* ===== NUEVO ESTILO PARA SCROLL EN SOLICITUDES ===== */
        .solicitudes-scroll {
            max-height: 520px; /* Altura para mostrar aproximadamente 2 citas */
            overflow-y: auto;
            padding-right: 8px; /* Espacio para el scroll */
            scrollbar-width: thin;
            scrollbar-color: rgba(201,168,76,0.4) transparent;
        }

        .solicitudes-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .solicitudes-scroll::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 3px;
        }

        .solicitudes-scroll::-webkit-scrollbar-thumb {
            background: rgba(201,168,76,0.4);
            border-radius: 3px;
        }

        .solicitudes-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--dorado);
        }

        .solicitud-item {
            padding: 16px;
            border: 1px solid var(--borde);
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
            background: rgba(0,0,0,0.15);
        }

        .solicitud-item:last-child {
            margin-bottom: 0;
        }

        .solicitud-item:hover {
            border-color: var(--dorado);
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }

        .solicitud-item.oculto {
            opacity: 0.6;
            background: rgba(0,0,0,0.28);
            position: relative;
            overflow: hidden;
        }
        
        .solicitud-item.oculto::before {
            content: '🔒 No visible hasta las 6:00 AM';
            position: absolute;
            top: 0;
            right: 0;
            background: var(--amarillo);
            color: white;
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 0 0 0 12px;
            font-weight: 600;
        }
        
        .solicitud-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .ciudadano-avatar {
            width: 40px;
            height: 40px;
            background: var(--verde-claro);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .ciudadano-info h4 {
            font-size: 1rem;
            margin-bottom: 4px;
        }
        
        .ciudadano-info p {
            font-size: 0.8rem;
            color: var(--texto-sub);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .solicitud-detalles {
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            padding: 12px;
            margin: 12px 0;
            font-size: 0.9rem;
        }

        .detalle-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .detalle-item i {
            width: 20px;
            color: var(--verde-claro);
        }
        
        .solicitud-acciones {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            font-weight: 700;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(201,168,76,0.35);
        }

        .btn-danger {
            background: rgba(248,113,113,0.12);
            border: 1.5px solid rgba(248,113,113,0.5);
            color: #fca5a5;
        }

        .btn-danger:hover {
            background: var(--rojo);
            color: white;
        }

        .btn-warning {
            background: rgba(251,191,36,0.12);
            border: 1.5px solid rgba(251,191,36,0.5);
            color: #fcd34d;
        }

        .btn-warning:hover {
            background: var(--amarillo);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--borde);
            color: var(--texto-sub);
        }

        .btn-outline:hover {
            background: rgba(201,168,76,0.08);
            border-color: var(--dorado);
            color: var(--dorado-claro);
        }
        
        .proxima-cita-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px;
            border-bottom: 1px solid var(--borde);
        }
        
        .proxima-cita-item:last-child {
            border-bottom: none;
        }
        
        .fecha-badge {
            background: var(--verde);
            color: white;
            padding: 8px;
            border-radius: 10px;
            text-align: center;
            min-width: 60px;
        }
        
        .fecha-badge .dia {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .fecha-badge .mes {
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        
        .proxima-info h4 {
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        
        .proxima-info p {
            font-size: 0.8rem;
            color: var(--texto-sub);
        }
        
        .hora-badge {
            background: rgba(255,255,255,0.08);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--dorado-claro);
        }

        .bloqueo-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border-bottom: 1px solid var(--borde);
        }
        
        .bloqueo-item:last-child {
            border-bottom: none;
        }
        
        .bloqueo-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .bloqueo-fecha {
            background: var(--amarillo);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .bloqueo-motivo {
            color: var(--texto);
            font-size: 0.9rem;
        }
        
        .bloqueo-hora {
            color: var(--texto-sub);
            font-size: 0.8rem;
        }
        
        .btn-eliminar-bloqueo {
            background: transparent;
            border: none;
            color: var(--rojo);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .btn-eliminar-bloqueo:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .banner-propuesta-enviada {
            background: rgba(96,165,250,0.14);
            border: 1px solid rgba(147,197,253,0.35);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .banner-propuesta-enviada i {
            color: #93c5fd;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .banner-propuesta-enviada .prop-titulo {
            font-weight: 700;
            color: #93c5fd;
            display: block;
            margin-bottom: 4px;
        }
        .banner-propuesta-enviada .prop-fecha {
            color: #93c5fd;
            font-weight: 600;
        }
        .badge-esperando {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(96,165,250,0.14);
            border: 1.5px solid rgba(147,197,253,0.4);
            color: #93c5fd;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            width: 100%;
            justify-content: center;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2-columns { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
            .main { padding: 24px 16px 48px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .bloqueo-info { flex-direction: column; align-items: flex-start; gap: 4px; }
            .solicitudes-scroll { max-height: 400px; }
            .bienvenida h1 { font-size: 1.6rem; }
        }

        @media (max-width: 480px) {
            .main { padding: 16px 12px 48px; }
            .bienvenida h1 { font-size: 1.35rem; }
            .bienvenida p { font-size: 0.82rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 16px; gap: 10px; }
            .stat-icono { width: 40px; height: 40px; font-size: 1.2rem; }
            .stat-info h3 { font-size: 1.3rem; }
            .card-header { padding: 16px 16px; }
            .card-body { padding: 16px; }
            .solicitudes-scroll { max-height: 350px; }
            .info-funcionario { display: none; }
            .btn-logout span { display: none; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/funcionario/dashboard" class="header-brand">
            <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            <div class="header-brand-texto">
                Alcaldía Municipal<br>
                <strong>Monterrey · Casanare</strong>
            </div>
        </a>
        
        <div class="header-usuario">
            <div class="info-funcionario">
                <div class="funcionario-nombre"><?= htmlspecialchars($funcionario['nombres'] ?? $usuario['nombres'] . ' ' . ($funcionario['apellidos'] ?? $usuario['apellidos'])) ?></div>
                <div class="funcionario-cargo"><?= htmlspecialchars($funcionario['cargo'] ?? 'Funcionario') ?></div>
            </div>
            <div class="avatar">
                <?= strtoupper(substr($funcionario['nombres'] ?? $usuario['nombres'], 0, 1) . substr($funcionario['apellidos'] ?? $usuario['apellidos'], 0, 1)) ?>
            </div>
            <a href="/logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>
    
    <main class="main">
        <div class="bienvenida">
            <h1>Panel de <span><?= htmlspecialchars($funcionario['cargo'] ?? 'Funcionario') ?></span></h1>
            <p>Gestiona las solicitudes de citas y tu agenda laboral</p>
        </div>
        
        <?php if (!empty($dependencias)): ?>
        <div style="margin-bottom: 20px; background: var(--vidrio); backdrop-filter: blur(16px) saturate(140%); -webkit-backdrop-filter: blur(16px) saturate(140%); padding: 12px 20px; border-radius: 8px; border: 1px solid var(--borde);">
            <strong><i class="fas fa-building"></i> Dependencias:</strong> 
            <?php 
            $nombresDep = array_column($dependencias, 'nombre');
            echo htmlspecialchars(implode(' • ', $nombresDep));
            ?>
        </div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
        <div class="alertas">
            <div class="alerta alerta-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alertas">
            <div class="alerta alerta-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($passwordTemporal)): ?>
        <div style="background:rgba(251,191,36,0.12);border:1.5px solid rgba(251,191,36,0.45);border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;">
            <i class="fas fa-exclamation-triangle" style="color:#fcd34d;font-size:1.3rem;flex-shrink:0;"></i>
            <div style="flex:1;">
                <strong style="color:#fcd34d;">Contraseña temporal activa</strong>
                <p style="margin:4px 0 0;color:rgba(255,255,255,0.75);font-size:0.9rem;">
                    Estás usando una contraseña temporal que expira en 24 horas.
                    Por favor cámbiala ahora para no perder el acceso.
                </p>
            </div>
            <a href="/cambiar-contrasena" style="background:linear-gradient(135deg, var(--dorado-claro), var(--dorado));color:#2c2107;padding:8px 16px;border-radius:7px;text-decoration:none;font-size:0.88rem;font-weight:700;white-space:nowrap;">
                Cambiar contraseña
            </a>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card pendientes">
                <div class="stat-icono"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?= $resumen['pendientes'] ?></h3>
                    <p>Solicitudes Pendientes</p>
                </div>
            </div>
            
            <div class="stat-card hoy">
                <div class="stat-icono"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?= $resumen['hoy_confirmadas'] ?></h3>
                    <p>Citas Confirmadas Hoy</p>
                </div>
            </div>
            
            <div class="stat-card proximas">
                <div class="stat-icono"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <h3><?= count($resumen['proximas']) ?></h3>
                    <p>Próximas Citas</p>
                </div>
            </div>
            
            <div class="stat-card historial">
                <div class="stat-icono"><i class="fas fa-history"></i></div>
                <div class="stat-info">
                    <h3><?= count($horariosBloqueados) ?></h3>
                    <p>Bloqueos Activos</p>
                </div>
            </div>
        </div>
        
        <!-- Grid principal -->
        <div class="grid-2-columns">
            <!-- Solicitudes Pendientes CON SCROLL (siempre visible) -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Solicitudes Pendientes</h2>
        <span class="badge"><?= $resumen['pendientes'] ?></span>
    </div>
    <div class="card-body" style="padding: 20px 16px 20px 24px;">
        <!-- El contenedor scroll siempre existe, tenga o no citas -->
        <div class="solicitudes-scroll">
            <?php if (empty($solicitudesPendientes)): ?>
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--verde-claro); margin-bottom: 16px;"></i>
                    <p style="color: var(--texto-sub);">No hay solicitudes pendientes</p>
                </div>
            <?php else: ?>
                <?php foreach ($solicitudesPendientes as $solicitud): ?>
                <?php
                    $esPropuesta    = $solicitud['estado'] === 'propuesta_reprogramacion';
                    $esContra       = $solicitud['estado'] === 'contrapropuesta_ciudadano';
                    $borderColor    = $esPropuesta ? '#3b82f6' : ($esContra ? '#f59e0b' : '');
                    $borderStyle    = $borderColor ? "border-left: 4px solid {$borderColor};" : '';
                ?>
                <div class="solicitud-item" id="solicitud-<?= $solicitud['id_cita'] ?>" style="<?= $borderStyle ?>">

                    <?php if ($esContra): ?>
                    <div style="background: rgba(251,191,36,0.14); border: 1px solid rgba(251,191,36,0.35); border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; font-size: 0.85rem;">
                        <strong style="color: #fcd34d;"><i class="fas fa-reply"></i> El ciudadano propone nueva fecha:</strong>
                        <span style="color: #fcd34d; font-weight: 700; margin-left: 8px;">
                            <?= date('d/m/Y', strtotime($solicitud['fecha_propuesta'])) ?>
                            · <?= date('h:i A', strtotime($solicitud['hora_propuesta'])) ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ($esPropuesta): ?>
                    <div class="banner-propuesta-enviada">
                        <i class="fas fa-paper-plane"></i>
                        <div>
                            <span class="prop-titulo">Propuesta enviada — esperando respuesta del ciudadano</span>
                            Nueva fecha propuesta:
                            <span class="prop-fecha">
                                <?= date('d/m/Y', strtotime($solicitud['fecha_propuesta'])) ?>
                                · <?= date('h:i A', strtotime($solicitud['hora_propuesta'])) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="solicitud-header">
                        <div class="ciudadano-avatar">
                            <?= strtoupper(substr($solicitud['ciudadano_nombres'] ?? 'U', 0, 1) . substr($solicitud['ciudadano_apellidos'] ?? 'S', 0, 1)) ?>
                        </div>
                        <div class="ciudadano-info">
                            <h4><?= htmlspecialchars(($solicitud['ciudadano_nombres'] ?? '') . ' ' . ($solicitud['ciudadano_apellidos'] ?? '')) ?></h4>
                            <p><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($solicitud['ciudadano_telefono'] ?? '') ?></p>
                        </div>
                    </div>
                    
                    <div class="solicitud-detalles">
                        <div class="detalle-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= isset($solicitud['fecha']) ? date('d/m/Y', strtotime($solicitud['fecha'])) : '' ?></span>
                        </div>
                        <div class="detalle-item">
                            <i class="fas fa-clock"></i>
                            <span><?= isset($solicitud['hora_inicio']) ? date('h:i A', strtotime($solicitud['hora_inicio'])) : '' ?></span>
                        </div>
                        <div class="detalle-item">
                            <i class="fas fa-building"></i>
                            <span><?= htmlspecialchars($solicitud['dependencia'] ?? 'No especificada') ?></span>
                        </div>
                        <div class="detalle-item">
                            <i class="fas fa-comment"></i>
                            <span><?php 
                                $motivo = $solicitud['motivo'] ?? '';
                                if (strlen($motivo) > 100) {
                                    echo htmlspecialchars(substr($motivo, 0, 100)) . '...';
                                } else {
                                    echo htmlspecialchars($motivo);
                                }
                            ?></span>
                        </div>
                    </div>
                    
                    <div class="solicitud-acciones">
                        <?php if ($esPropuesta): ?>
                        <span class="badge-esperando">
                            <i class="fas fa-hourglass-half"></i>
                            Esperando respuesta del ciudadano…
                        </span>
                        <?php else: ?>
                        <button class="btn btn-primary btn-sm" onclick="abrirModalAprobar(<?= $solicitud['id_cita'] ?? 0 ?>)">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="abrirModalReprogramar(<?= $solicitud['id_cita'] ?? 0 ?>, '<?= htmlspecialchars($solicitud['fecha'] ?? '') ?>', '<?= htmlspecialchars($solicitud['hora_inicio'] ?? '') ?>')">
                            <i class="fas fa-calendar-alt"></i> Reprogramar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="abrirModalRechazar(<?= $solicitud['id_cita'] ?? 0 ?>)">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
            <!-- En Curso Ahora -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-circle" style="color: var(--verde-claro); font-size: 0.7rem;"></i> En Curso Ahora</h2>
                    <span class="badge" id="en-curso-badge"><?= count($citasEnCurso) ?></span>
                </div>
                <div class="card-body" id="en-curso-body">
                    <?php if (empty($citasEnCurso)): ?>
                        <div id="en-curso-vacio" style="text-align: center; padding: 40px 20px;">
                            <i class="fas fa-door-open" style="font-size: 3rem; color: var(--texto-sub); margin-bottom: 16px;"></i>
                            <p style="color: var(--texto-sub);">No hay visitas en curso en este momento</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($citasEnCurso as $cita): ?>
                        <div class="solicitud-item" id="en-curso-<?= $cita['tipo'] ?>-<?= $cita['id'] ?>" style="border-left: 4px solid var(--verde-medio);" data-hora-ingreso="<?= htmlspecialchars($cita['hora_ingreso'] ?? '') ?>">
                            <div class="solicitud-header">
                                <div class="ciudadano-avatar" style="background: var(--verde-medio);">
                                    <?= strtoupper(substr($cita['ciudadano_nombres'] ?? 'U', 0, 1) . substr($cita['ciudadano_apellidos'] ?? 'S', 0, 1)) ?>
                                </div>
                                <div class="ciudadano-info">
                                    <h4><?= htmlspecialchars($cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos']) ?></h4>
                                    <p><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($cita['ciudadano_telefono'] ?? '') ?></p>
                                </div>
                            </div>
                            <div class="solicitud-detalles">
                                <div class="detalle-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= date('d/m/Y', strtotime($cita['fecha'])) ?></span>
                                </div>
                                <div class="detalle-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Ingresó: <?= !empty($cita['hora_ingreso']) ? date('h:i A', strtotime($cita['hora_ingreso'])) : '—' ?></span>
                                </div>
                                <div class="detalle-item">
                                    <i class="fas fa-building"></i>
                                    <span><?= htmlspecialchars($cita['dependencia'] ?? '') ?></span>
                                </div>
                                <div class="detalle-item">
                                    <i class="fas fa-comment"></i>
                                    <span><?= htmlspecialchars(substr($cita['motivo'] ?? '', 0, 100)) ?></span>
                                </div>
                            </div>
                            <div class="solicitud-acciones">
                                <button class="btn btn-primary btn-sm" onclick="abrirModalSalida(<?= $cita['id'] ?>, '<?= $cita['tipo'] ?>', '<?= htmlspecialchars(addslashes($cita['ciudadano_nombres'] . ' ' . $cita['ciudadano_apellidos'])) ?>')">
                                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Próximas Citas con scroll -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Próximas Citas</h2>
                    <span class="badge"><?= count($resumen['proximas']) ?></span>
                </div>
                <div class="card-body" style="padding: 20px 16px 20px 24px;">
                    <div class="solicitudes-scroll" style="max-height: 520px; overflow-y: auto;">
                        <?php if (empty($resumen['proximas'])): ?>
                            <div style="text-align: center; padding: 40px 20px;">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--texto-sub); margin-bottom: 16px;"></i>
                                <p style="color: var(--texto-sub);">No hay citas programadas</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($resumen['proximas'] as $cita): ?>
                            <div class="proxima-cita-item">
                                <div class="fecha-badge">
                                    <div class="dia"><?= isset($cita['fecha']) ? date('d', strtotime($cita['fecha'])) : '' ?></div>
                                    <div class="mes"><?= isset($cita['fecha']) ? date('M', strtotime($cita['fecha'])) : '' ?></div>
                                </div>
                                <div class="proxima-info">
                                    <h4><?= htmlspecialchars(($cita['ciudadano_nombres'] ?? '') . ' ' . ($cita['ciudadano_apellidos'] ?? '')) ?></h4>
                                    <p><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($cita['ciudadano_telefono'] ?? '') ?></p>
                                </div>
                                <div class="hora-badge">
                                    <i class="fas fa-clock"></i> <?= isset($cita['hora_inicio']) ? date('h:i A', strtotime($cita['hora_inicio'])) : '' ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gestión de Agenda - Horarios Bloqueados -->
        <div class="card" style="margin-top: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Gestión de Agenda</h2>
                <button class="btn btn-primary btn-sm" onclick="abrirModalBloquear()">
                    <i class="fas fa-lock"></i> Bloquear Horario
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($horariosBloqueados)): ?>
                    <p style="color: var(--texto-sub); text-align: center; padding: 20px;">
                        No hay horarios bloqueados. Utiliza el botón "Bloquear Horario" para gestionar tu agenda.
                    </p>
                <?php else: ?>
                    <div style="margin-bottom: 16px;">
                        <h3 style="font-size: 1rem; color: var(--texto); margin-bottom: 12px;">Horarios bloqueados:</h3>
                        <?php foreach ($horariosBloqueados as $bloqueo): ?>
                        <div class="bloqueo-item">
                            <div class="bloqueo-info">
                                <span class="bloqueo-fecha"><?= date('d/m/Y', strtotime($bloqueo['fecha'])) ?></span>
                                <span class="bloqueo-hora"><?= date('h:i A', strtotime($bloqueo['hora_inicio'])) ?> - <?= date('h:i A', strtotime($bloqueo['hora_fin'])) ?></span>
                                <span class="bloqueo-motivo"><?= htmlspecialchars($bloqueo['motivo']) ?></span>
                            </div>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este bloqueo?')">
                                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                                <input type="hidden" name="action" value="eliminar_bloqueo">
                                <input type="hidden" name="bloqueo_id" value="<?= $bloqueo['id_bloqueo'] ?>">
                                <button type="submit" class="btn-eliminar-bloqueo" title="Eliminar bloqueo">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Modal Registrar Salida -->
    <div class="modal-overlay" id="modalSalida">
        <div class="modal">
            <h3><i class="fas fa-sign-out-alt" style="color: var(--verde-medio);"></i> Registrar Salida</h3>
            <p id="modalSalida-texto">¿Confirmas la salida del visitante?</p>
            <form method="POST" action="" id="formSalida">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="action"  value="registrar_salida">
                <input type="hidden" name="cita_id" id="salida_cita_id">
                <input type="hidden" name="tipo"    id="salida_tipo">
                <div class="form-group" style="margin-top:16px;">
                    <label for="salida_resultado">Resultado de la visita *</label>
                    <textarea name="resultado" id="salida_resultado" required
                        placeholder="Describe brevemente cómo se resolvió o qué se acordó en la visita"
                        style="width:100%;min-height:90px;resize:vertical;"></textarea>
                </div>
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalSalida')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar" style="background: var(--verde);">
                        <i class="fas fa-check"></i> Confirmar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Aprobar -->
    <div class="modal-overlay" id="modalAprobar">
        <div class="modal">
            <h3>Confirmar Aprobación</h3>
            <p>¿Estás seguro de aprobar esta cita?</p>
            <form method="POST" action="">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="action" value="aprobar">
                <input type="hidden" name="cita_id" id="aprobar_cita_id">
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalAprobar')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar" style="background: var(--verde);">
                        <i class="fas fa-check"></i> Aprobar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Reprogramar Cita -->
    <div class="modal-overlay" id="modalReprogramar">
        <div class="modal modal-lg">
            <h3><i class="fas fa-calendar-alt" style="color: var(--amarillo);"></i> Reprogramar Cita</h3>
            <p>Selecciona la nueva fecha y hora para la cita:</p>
            
            <div style="background: rgba(0,0,0,0.22); padding: 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--amarillo);">
                <p style="margin-bottom: 8px;"><strong>Cita actual:</strong></p>
                <p id="cita-original-info" style="color: var(--texto-sub); font-size: 0.9rem;"></p>
            </div>

            <form method="POST" action="" id="formReprogramar">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="action" value="reprogramar">
                <input type="hidden" name="cita_id" id="reprogramar_cita_id">

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nueva fecha <span class="req">*</span></label>
                    <input type="date" name="nueva_fecha" id="nueva_fecha" required
                        min="<?= date('Y-m-d') ?>"
                        style="width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px; font-family: 'Outfit', sans-serif; background: rgba(0,0,0,0.28); color: var(--texto);">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nueva hora <span class="req">*</span></label>
                    <select name="nueva_hora" id="nueva_hora" required style="width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px; background: rgba(0,0,0,0.28); color: var(--texto);">
                        <option value="">Primero selecciona una fecha</option>
                    </select>
                    <div id="loading-horarios" style="display: none; margin-top: 10px; color: var(--texto-sub);">
                        <i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Motivo de la reprogramación <span class="req">*</span></label>
                    <textarea name="motivo_reprogramacion" required
                            placeholder="Ej: Por reunión interna, por disponibilidad de sala, etc."
                            style="width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px; min-height: 100px; background: rgba(0,0,0,0.28); color: var(--texto);"></textarea>
                </div>
                
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalReprogramar')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar" style="background: var(--amarillo);">
                        <i class="fas fa-calendar-check"></i> Reprogramar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Aviso (festivo / día no hábil / etc.) -->
    <div class="modal-overlay" id="modalAviso" style="z-index:1100;">
        <div class="modal" style="max-width:420px;text-align:center;">
            <div id="aviso-icon" style="font-size:2.5rem;margin-bottom:14px;"></div>
            <h3 id="aviso-titulo" style="margin-bottom:10px;"></h3>
            <p id="aviso-mensaje" style="margin-bottom:24px;text-align:center;"></p>
            <div class="modal-acciones" style="justify-content:center;">
                <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalAviso')">Entendido</button>
            </div>
        </div>
    </div>

    <!-- Modal Rechazar -->
    <div class="modal-overlay" id="modalRechazar">
        <div class="modal">
            <h3>Rechazar Cita</h3>
            <p>Por favor indica el motivo del rechazo:</p>
            <form method="POST" action="">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="action" value="rechazar">
                <input type="hidden" name="cita_id" id="rechazar_cita_id">
                <textarea name="motivo_rechazo" placeholder="Ej: Horario no disponible, trámite no corresponde, etc." required style="width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px; margin-bottom: 20px; min-height: 100px; background: rgba(0,0,0,0.28); color: var(--texto);"></textarea>
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalRechazar')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar" style="background: var(--rojo);">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Bloquear Horario -->
    <div class="modal-overlay" id="modalBloquear">
        <div class="modal">
            <h3>Bloquear Horario</h3>
            <p>Selecciona el horario que deseas bloquear:</p>
            <form method="POST" action="">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="action" value="bloquear">
                
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Fecha</label>
                <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border: 1.5px solid var(--borde); border-radius: 8px; margin-bottom: 16px; background: rgba(0,0,0,0.28); color: var(--texto);">

                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Hora Inicio</label>
                <select name="hora_inicio" required style="width: 100%; padding: 10px; border: 1.5px solid var(--borde); border-radius: 8px; margin-bottom: 16px; background: rgba(0,0,0,0.28); color: var(--texto);">
                    <?php
                    $horas = ['07:00','07:15','07:30','07:45',
                '08:00','08:15','08:30','08:45',
                '09:00','09:15','09:30','09:45',
                '10:00','10:15','10:30','10:45',
                '11:00','11:15','11:30','11:45',
                '14:00','14:15','14:30','14:45',
                '15:00','15:15','15:30','15:45',
                '16:00','16:15','16:30','16:45'];
                    foreach ($horas as $hora):
                    ?>
                    <option value="<?= $hora ?>"><?= date('h:i A', strtotime($hora)) ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Hora Fin</label>
                <select name="hora_fin" required style="width: 100%; padding: 10px; border: 1.5px solid var(--borde); border-radius: 8px; margin-bottom: 16px; background: rgba(0,0,0,0.28); color: var(--texto);">
                    <?php foreach ($horas as $hora): ?>
                    <option value="<?= $hora ?>"><?= date('h:i A', strtotime($hora)) ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Motivo del bloqueo</label>
                <textarea name="motivo_bloqueo" required placeholder="Ej: Reunión, capacitación, permiso personal, etc." style="width: 100%; padding: 12px; border: 1.5px solid var(--borde); border-radius: 8px; margin-bottom: 20px; min-height: 80px; background: rgba(0,0,0,0.28); color: var(--texto);"></textarea>
                
                <div class="modal-acciones">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal('modalBloquear')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirmar" style="background: var(--verde-medio);">
                        <i class="fas fa-lock"></i> Bloquear
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }
        
        .modal {
            background: rgba(20, 34, 26, 0.75);
            backdrop-filter: blur(20px) saturate(140%);
            -webkit-backdrop-filter: blur(20px) saturate(140%);
            border: 1px solid var(--borde);
            border-radius: 20px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
            color: var(--texto);
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--texto);
        }
        
        .modal p {
            color: var(--texto-sub);
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        .modal-acciones {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn-modal-cancelar {
            padding: 12px 24px;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid var(--borde);
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            color: var(--texto);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-cancelar:hover {
            border-color: var(--dorado);
            color: var(--dorado-claro);
        }
        
        .btn-modal-confirmar {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-modal-confirmar:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .badge {
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Alertas de visita prolongada */
        .en-curso-alerta-amarillo {
            border-left-color: #f59e0b !important;
            background: rgba(251,191,36,0.1);
        }
        .en-curso-alerta-rojo {
            border-left-color: #ef4444 !important;
            background: rgba(248,113,113,0.1);
        }
        .alerta-visita-banner {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .alerta-visita-banner.amarillo {
            background: rgba(251,191,36,0.18);
            color: #fcd34d;
        }
        .alerta-visita-banner.rojo {
            background: rgba(248,113,113,0.18);
            color: #fca5a5;
            animation: parpadeo 1.5s infinite;
        }
        @keyframes parpadeo {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.6; }
        }
        select option { background: #fff; color: #1a1a1a; }
    </style>
    
    <script>
        
        function abrirModalSalida(citaId, tipo, nombre) {
            document.getElementById('salida_cita_id').value = citaId;
            document.getElementById('salida_tipo').value = tipo;
            document.getElementById('salida_resultado').value = '';
            document.getElementById('modalSalida-texto').textContent =
                '¿Confirmas la salida de ' + nombre + '? Se registrará la hora actual.';
            document.getElementById('modalSalida').style.display = 'flex';
        }

        function abrirModalAprobar(citaId) {
            document.getElementById('aprobar_cita_id').value = citaId;
            document.getElementById('modalAprobar').style.display = 'flex';
        }
        
        function abrirModalRechazar(citaId) {
            document.getElementById('rechazar_cita_id').value = citaId;
            document.getElementById('modalRechazar').style.display = 'flex';
        }
        
        function abrirModalBloquear() {
            document.getElementById('modalBloquear').style.display = 'flex';
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        function mostrarAviso(titulo, mensaje, tipo) {
            var iconos = {
                error:   '<i class="fas fa-calendar-times" style="color:#ef4444;"></i>',
                warning: '<i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>',
                info:    '<i class="fas fa-info-circle" style="color:var(--verde-medio);"></i>'
            };
            document.getElementById('aviso-icon').innerHTML    = iconos[tipo] || iconos.info;
            document.getElementById('aviso-titulo').textContent = titulo;
            document.getElementById('aviso-mensaje').textContent = mensaje;
            document.getElementById('modalAviso').style.display = 'flex';
        }
        function formatFecha(fecha) {
            const d = new Date(fecha + 'T00:00:00');
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
        // ¡AGREGA ESTA FUNCIÓN!
        function formatHora(hora) {
            if (!hora) return '';
            const [h, m] = hora.split(':');
            const hNum = parseInt(h);
            const ampm = hNum >= 12 ? 'PM' : 'AM';
            const h12 = hNum > 12 ? hNum - 12 : (hNum === 0 ? 12 : hNum);
            return `${h12}:${m} ${ampm}`;
        }
        function abrirModalReprogramar(citaId, fechaOriginal, horaOriginal) {
            document.getElementById('reprogramar_cita_id').value = citaId;
            document.getElementById('cita-original-info').innerHTML = `
                <i class="fas fa-calendar"></i> ${formatFecha(fechaOriginal)} - <i class="fas fa-clock"></i> ${formatHora(horaOriginal)}
            `;
            document.getElementById('modalReprogramar').style.display = 'flex';
            
            // Resetear selects
            document.getElementById('nueva_fecha').value = '';
            document.getElementById('nueva_hora').innerHTML = '<option value="">Primero selecciona una fecha</option>';
        }

        // Validación client-side de festivos y días no hábiles
        (function() {
            var conf = window.configSistema || { diasHabiles: [], _festivos: [] };
            window._esFestivoFunc = function(fechaStr) {
                var festivos = conf._festivos || [];
                var mmdd = fechaStr.substring(5);
                for (var i = 0; i < festivos.length; i++) {
                    var f = festivos[i];
                    var esRec = f.recurrente === 't' || f.recurrente === true || f.recurrente === 1 || f.recurrente === '1';
                    if (esRec ? f.clave === mmdd : f.clave === fechaStr) return f.descripcion;
                }
                return null;
            };
            window._esHabilFunc = function(fechaStr) {
                var diasHabiles = conf.diasHabiles || [];
                if (!diasHabiles.length) return true;
                var d = new Date(fechaStr + 'T00:00:00');
                return diasHabiles.includes(d.getDay());
            };
        })();

        // Cargar horarios disponibles cuando se selecciona una fecha
        document.getElementById('nueva_fecha').addEventListener('change', function() {
            const fecha = this.value;
            const citaId = document.getElementById('reprogramar_cita_id').value;
            const funcionarioId = <?= $funcionarioId ?>;

            if (!fecha) return;

            // Validar festivo y día hábil antes de llamar al AJAX
            const festivoNombre = window._esFestivoFunc(fecha);
            if (festivoNombre) {
                this.value = '';
                mostrarAviso('Día festivo', 'Ese día es festivo (' + festivoNombre + ') y la alcaldía no tiene atención. Por favor elige otra fecha.', 'error');
                return;
            }
            if (!window._esHabilFunc(fecha)) {
                this.value = '';
                mostrarAviso('Día no hábil', 'La alcaldía no atiende ese día. Por favor elige una fecha entre los días hábiles.', 'warning');
                return;
            }

            const selectHora = document.getElementById('nueva_hora');
            selectHora.innerHTML = '<option value="">Cargando horarios...</option>';
            document.getElementById('loading-horarios').style.display = 'block';
            
            fetch(`/ajax/horarios_disponibles?funcionario_id=${funcionarioId}&fecha=${fecha}&excluir_cita=${citaId}&tab_id=${window.TAB_ID}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading-horarios').style.display = 'none';
                    
                    if (data.error) {
                        document.getElementById('nueva_fecha').value = '';
                        selectHora.innerHTML = '<option value="">Primero selecciona una fecha</option>';
                        var err = data.error.toLowerCase();
                        if (err.includes('festivo')) {
                            mostrarAviso('Día festivo', data.error, 'error');
                        } else if (err.includes('no se atiende') || err.includes('no atiende') || err.includes('no h')) {
                            mostrarAviso('Día no hábil', data.error, 'warning');
                        } else {
                            mostrarAviso('Aviso', data.error, 'info');
                        }
                        return;
                    }
                    
                    let options = '<option value="">Selecciona una hora...</option>';
                    
                    // Combinar mañana y tarde
                    [...data.manana, ...data.tarde].forEach(slot => {
                        if (slot.disponible) {
                            options += `<option value="${slot.hora}">${formatHora(slot.hora)}</option>`;
                        }
                    });
                    
                    if (options === '<option value="">Selecciona una hora...</option>') {
                        options = '<option value="">No hay horarios disponibles</option>';
                    }
                    
                    selectHora.innerHTML = options;
                })
                .catch(() => {
                    document.getElementById('loading-horarios').style.display = 'none';
                    selectHora.innerHTML = '<option value="">Error al cargar horarios</option>';
                });
        });
        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
        
        // Cerrar al hacer clic fuera del modal
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>


<script>
const FUNCIONARIO_ID = <?= (int)$funcionarioId ?>;
let ultimoId    = 0;
let ultimoCheck = Math.floor(Date.now() / 1000); // Unix timestamp del último poll
let intervalo   = null;

// Obtener el ID más alto de las citas EXISTENTES en el DOM
function actualizarUltimoIdDesdeDOM() {
    let maxId = 0;
    document.querySelectorAll('.solicitud-item').forEach(item => {
        let match = item.id.match(/solicitud-(\d+)/);
        if (match) {
            let id = parseInt(match[1]);
            if (id > maxId) maxId = id;
        }
    });
    if (maxId > ultimoId) ultimoId = maxId;
}

// Verificar citas nuevas + contrapropuestas
async function verificarNuevasCitas() {
    try {
        const response = await fetch(`/ajax/check_nuevas_citas?funcionario_id=${FUNCIONARIO_ID}&ultimo_id=${ultimoId}&ultimo_check=${ultimoCheck}&t=${Date.now()}&tab_id=${window.TAB_ID}`);
        const data = await response.json();

        // Citas nuevas pendientes
        if (data.ok && data.citas && data.citas.length > 0) {
            data.citas.forEach(cita => {
                if (cita.id_cita > ultimoId) {
                    agregarCitaAlDOM(cita);
                    mostrarNotificacion(cita);
                    ultimoId = cita.id_cita;
                }
            });
        }

        // Contrapropuestas del ciudadano
        if (data.ok && data.contrapropuestas && data.contrapropuestas.length > 0) {
            data.contrapropuestas.forEach(cita => {
                actualizarCartaContrapropuesta(cita);
                mostrarNotificacionContrapropuesta(cita);
            });
        }

        // Visitas en curso: reconciliar DOM con snapshot del servidor
        if (data.ok && data.en_curso !== undefined) {
            reconciliarEnCurso(data.en_curso);
        }

        // Actualizar timestamp tras procesar
        ultimoCheck = Math.floor(Date.now() / 1000);
    } catch (error) {
        // Silencioso en producción — errores de red son temporales
    }
}

function reconciliarEnCurso(lista) {
    const body  = document.getElementById('en-curso-body');
    const badge = document.getElementById('en-curso-badge');
    if (!body) return;

    const claveSet = new Set(lista.map(v => `en-curso-${v.tipo}-${v.id}`));

    // Eliminar los que ya no están en curso
    body.querySelectorAll('.solicitud-item[id^="en-curso-"]').forEach(el => {
        if (!claveSet.has(el.id)) el.remove();
    });

    // Agregar los nuevos
    lista.forEach(v => {
        const clave = `en-curso-${v.tipo}-${v.id}`;
        if (document.getElementById(clave)) return; // ya existe

        // Quitar mensaje vacío si lo hay
        const vacio = document.getElementById('en-curso-vacio');
        if (vacio) vacio.remove();

        const nombre = escapeHtml((v.ciudadano_nombres || '') + ' ' + (v.ciudadano_apellidos || ''));
        const iniciales = ((v.ciudadano_nombres || 'U')[0] + (v.ciudadano_apellidos || 'S')[0]).toUpperCase();
        const fechaFmt = v.fecha ? v.fecha.substring(8,10)+'/'+v.fecha.substring(5,7)+'/'+v.fecha.substring(0,4) : '—';
        const horaFmt  = v.hora_ingreso ? formatHora(v.hora_ingreso.substring(0,5)) : '—';

        const div = document.createElement('div');
        div.className = 'solicitud-item nueva-solicitud';
        div.id = clave;
        div.style.borderLeft = '4px solid var(--verde-medio)';
        div.dataset.horaIngreso = v.hora_ingreso || '';
        div.innerHTML = `
            <div class="solicitud-header">
                <div class="ciudadano-avatar" style="background:var(--verde-medio);">${iniciales}</div>
                <div class="ciudadano-info">
                    <h4>${nombre}</h4>
                    <p><i class="fas fa-phone-alt"></i> ${escapeHtml(v.ciudadano_telefono || '')}</p>
                </div>
            </div>
            <div class="solicitud-detalles">
                <div class="detalle-item"><i class="fas fa-calendar"></i><span>${fechaFmt}</span></div>
                <div class="detalle-item"><i class="fas fa-clock"></i><span>Ingresó: ${horaFmt}</span></div>
                <div class="detalle-item"><i class="fas fa-building"></i><span>${escapeHtml(v.dependencia || '')}</span></div>
                <div class="detalle-item"><i class="fas fa-comment"></i><span>${escapeHtml((v.motivo || '').substring(0,100))}</span></div>
            </div>
            <div class="solicitud-acciones">
                <button class="btn btn-primary btn-sm" onclick="abrirModalSalida(${v.id},'${v.tipo}','${nombre.replace(/'/g,"\\'")}')">
                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                </button>
            </div>`;
        body.appendChild(div);

        // Toast de notificación
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:10000;background:var(--verde-medio);color:white;padding:12px 20px;border-radius:10px;font-family:Outfit,sans-serif;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
        toast.innerHTML = `<i class="fas fa-door-open"></i> ${nombre} está en curso`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    });

    // Mostrar vacío si la lista quedó sin items
    if (lista.length === 0 && !body.querySelector('.solicitud-item')) {
        if (!document.getElementById('en-curso-vacio')) {
            const vacio = document.createElement('div');
            vacio.id = 'en-curso-vacio';
            vacio.style.cssText = 'text-align:center;padding:40px 20px;';
            vacio.innerHTML = '<i class="fas fa-door-open" style="font-size:3rem;color:var(--texto-sub);margin-bottom:16px;"></i><p style="color:var(--texto-sub);">No hay visitas en curso en este momento</p>';
            body.appendChild(vacio);
        }
    }

    // Actualizar badge
    if (badge) badge.textContent = lista.length;
}

// Agregar SOLO las citas NUEVAS al DOM
function agregarCitaAlDOM(cita) {
    let scroll = document.querySelector('.solicitudes-scroll');
    if (!scroll) return;
    
    if (document.getElementById(`solicitud-${cita.id_cita}`)) {
        return;
    }
    
    // Eliminar mensaje de "no hay solicitudes" si existe
    const vacio = scroll.querySelector('[style*="text-align: center"]');
    if (vacio) vacio.remove();

    const div = document.createElement('div');
    div.className = 'solicitud-item nueva-solicitud';
    div.id = `solicitud-${cita.id_cita}`;
    div.style.borderLeft = '4px solid var(--verde-claro)';
    div.innerHTML = `
        <div class="solicitud-header">
            <div class="ciudadano-avatar">${(cita.ciudadano_nombres[0] + (cita.ciudadano_apellidos?.[0] || '')).toUpperCase()}</div>
            <div class="ciudadano-info">
                <h4>${escapeHtml(cita.ciudadano_nombres + ' ' + (cita.ciudadano_apellidos || ''))}</h4>
                <p><i class="fas fa-phone-alt"></i> ${escapeHtml(cita.ciudadano_telefono ?? '')}</p>
            </div>
        </div>
        <div class="solicitud-detalles">
            <div class="detalle-item"><i class="fas fa-calendar"></i><span>${formatFecha(cita.fecha)}</span></div>
            <div class="detalle-item"><i class="fas fa-clock"></i><span>${formatHora(cita.hora_inicio)}</span></div>
            <div class="detalle-item"><i class="fas fa-building"></i><span>${escapeHtml(cita.dependencia)}</span></div>
            <div class="detalle-item"><i class="fas fa-comment"></i><span>${escapeHtml((cita.motivo || '').substring(0, 80))}</span></div>
        </div>
        <div class="solicitud-acciones">
            <button class="btn btn-primary btn-sm" onclick="abrirModalAprobar(${cita.id_cita})"><i class="fas fa-check"></i> Aprobar</button>
            <button class="btn btn-warning btn-sm" onclick="abrirModalReprogramar(${cita.id_cita}, '${cita.fecha}', '${cita.hora_inicio}')"><i class="fas fa-calendar-alt"></i> Reprogramar</button>
            <button class="btn btn-danger btn-sm" onclick="abrirModalRechazar(${cita.id_cita})"><i class="fas fa-times"></i> Rechazar</button>
        </div>
    `;
    scroll.insertBefore(div, scroll.firstChild);
    actualizarContador();
}

// Actualiza (o crea) la tarjeta de una contrapropuesta en el DOM
function actualizarCartaContrapropuesta(cita) {
    const initials = (cita.ciudadano_nombres[0] + (cita.ciudadano_apellidos?.[0] || '')).toUpperCase();
    const inner = `
        <div style="background:rgba(251,191,36,0.14);border:1px solid rgba(251,191,36,0.35);border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:0.85rem;">
            <strong style="color:#fcd34d;"><i class="fas fa-reply"></i> El ciudadano propone nueva fecha:</strong>
            <span style="color:#fcd34d;font-weight:700;margin-left:8px;">
                ${formatFecha(cita.fecha_propuesta)} · ${formatHora(cita.hora_propuesta)}
            </span>
        </div>
        <div class="solicitud-header">
            <div class="ciudadano-avatar">${initials}</div>
            <div class="ciudadano-info">
                <h4>${escapeHtml(cita.ciudadano_nombres + ' ' + (cita.ciudadano_apellidos || ''))}</h4>
                <p><i class="fas fa-phone-alt"></i> ${escapeHtml(cita.ciudadano_telefono ?? '')}</p>
            </div>
        </div>
        <div class="solicitud-detalles">
            <div class="detalle-item"><i class="fas fa-calendar"></i><span>${formatFecha(cita.fecha)}</span></div>
            <div class="detalle-item"><i class="fas fa-clock"></i><span>${formatHora(cita.hora_inicio)}</span></div>
            <div class="detalle-item"><i class="fas fa-building"></i><span>${escapeHtml(cita.dependencia)}</span></div>
            <div class="detalle-item"><i class="fas fa-comment"></i><span>${escapeHtml((cita.motivo||'').substring(0,100))}</span></div>
        </div>
        <div class="solicitud-acciones">
            <button class="btn btn-primary btn-sm" onclick="abrirModalAprobar(${cita.id_cita})"><i class="fas fa-check"></i> Aprobar</button>
            <button class="btn btn-warning btn-sm" onclick="abrirModalReprogramar(${cita.id_cita},'${cita.fecha}','${cita.hora_inicio}')"><i class="fas fa-calendar-alt"></i> Reprogramar</button>
            <button class="btn btn-danger btn-sm" onclick="abrirModalRechazar(${cita.id_cita})"><i class="fas fa-times"></i> Rechazar</button>
        </div>`;

    const existing = document.getElementById(`solicitud-${cita.id_cita}`);
    if (existing) {
        existing.style.borderLeft = '4px solid #f59e0b';
        existing.innerHTML = inner;
    } else {
        const scroll = document.querySelector('.solicitudes-scroll');
        if (!scroll) return;
        const vacio = scroll.querySelector('[style*="text-align: center"]');
        if (vacio) vacio.remove();
        const div = document.createElement('div');
        div.className = 'solicitud-item';
        div.id = `solicitud-${cita.id_cita}`;
        div.style.borderLeft = '4px solid #f59e0b';
        div.innerHTML = inner;
        scroll.insertBefore(div, scroll.firstChild);
        actualizarContador();
    }
}

function mostrarNotificacionContrapropuesta(cita) {
    if (Notification.permission === 'granted') {
        new Notification('Contrapropuesta de ciudadano', {
            body: `${cita.ciudadano_nombres} propone: ${formatFecha(cita.fecha_propuesta)} ${formatHora(cita.hora_propuesta)}`,
            icon: '/imagenes/favicon.png',
            tag: `contrapropuesta-${cita.id_cita}`
        });
    }
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:10000;background:#f59e0b;color:white;padding:12px 20px;border-radius:10px;font-family:Outfit,sans-serif;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
    toast.innerHTML = `<i class="fas fa-reply"></i> ${escapeHtml(cita.ciudadano_nombres)} envió una contrapropuesta`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function mostrarNotificacion(cita) {
    if (Notification.permission === 'granted') {
        new Notification('Nueva solicitud de cita', {
            body: `${cita.ciudadano_nombres} - ${formatFecha(cita.fecha)} ${formatHora(cita.hora_inicio)}`,
            icon: '/imagenes/favicon.png',
            tag: `cita-${cita.id_cita}`
        });
    }
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:10000;background:#1a5c38;color:white;padding:12px 20px;border-radius:10px;font-family:Outfit,sans-serif;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
    toast.innerHTML = `<i class="fas fa-bell"></i> Nueva cita de ${escapeHtml(cita.ciudadano_nombres)}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function actualizarContador() {
    const total = document.querySelectorAll('.solicitud-item').length;
    const badge = document.querySelector('.card-header .badge');
    if (badge) badge.textContent = total;
    const stat = document.querySelector('.stat-card.pendientes .stat-info h3');
    if (stat) stat.textContent = total;
}

// Revisa cada tarjeta en curso y aplica alerta visual si lleva >30 o >60 min
function actualizarAlertasEnCurso() {
    const ahora = Date.now();
    document.querySelectorAll('#en-curso-body .solicitud-item[id^="en-curso-"]').forEach(el => {
        const ingreso = el.dataset.horaIngreso;
        if (!ingreso) return;
        const ms      = ahora - new Date(ingreso).getTime();
        const minutos = ms / 60000;

        el.classList.remove('en-curso-alerta-amarillo', 'en-curso-alerta-rojo');
        const prev = el.querySelector('.alerta-visita-banner');
        if (prev) prev.remove();

        if (minutos >= 60) {
            el.classList.add('en-curso-alerta-rojo');
            const banner = document.createElement('div');
            banner.className = 'alerta-visita-banner rojo';
            banner.innerHTML = `<i class="fas fa-exclamation-circle"></i> Visita sin cerrar hace más de ${Math.floor(minutos)} min — registra la salida`;
            el.insertBefore(banner, el.firstChild);
        } else if (minutos >= 30) {
            el.classList.add('en-curso-alerta-amarillo');
            const banner = document.createElement('div');
            banner.className = 'alerta-visita-banner amarillo';
            banner.innerHTML = `<i class="fas fa-clock"></i> La visita lleva ${Math.floor(minutos)} min en curso`;
            el.insertBefore(banner, el.firstChild);
        }
    });
}

function formatFecha(f) { if (!f) return ''; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }
function formatHora(h) { if (!h) return ''; const [hh,mm] = h.split(':'); const n = parseInt(hh); const ampm = n >= 12 ? 'PM' : 'AM'; const h12 = n > 12 ? n - 12 : (n === 0 ? 12 : n); return `${h12}:${mm} ${ampm}`; }
function escapeHtml(str) { return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// Inicializar
if (Notification.permission === 'default') Notification.requestPermission();

document.addEventListener('DOMContentLoaded', function() {
    actualizarUltimoIdDesdeDOM();
    actualizarAlertasEnCurso();

    // Polling cada 10s: nuevas citas + en curso
    intervalo = setInterval(() => {
        verificarNuevasCitas();
        actualizarAlertasEnCurso();
    }, 10000);
});

window.addEventListener('beforeunload', () => {
    if (intervalo) clearInterval(intervalo);
});
</script>
</body>
</html>