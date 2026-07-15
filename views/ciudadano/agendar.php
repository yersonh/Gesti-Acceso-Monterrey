<?php
// Vista — la lógica está en CitaController::agendar()
// Variables disponibles: $ciudadanoId, $ciudadanoNombre, $ciudadanoIniciales
// $dependencias, $error
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Agendar Cita</title>
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
            --texto:        #1e293b;
            --texto-sub:    #64748b;
            --borde:        #e2e8f0;
            --fondo:        #f8f9fc;
            --rojo:         #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--fondo); color: var(--texto); min-height: 100vh; }

        .header {
            background: var(--verde); padding: 0 32px; height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .header::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
        }
        .header-brand { display: flex; align-items: center; gap: 14px; text-decoration: none; }
        .header-brand img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--dorado); }
        .header-brand-texto { color: var(--blanco); font-size: 0.8rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; line-height: 1.4; }
        .header-brand-texto strong { display: block; font-size: 0.92rem; color: var(--dorado-claro); letter-spacing: 0.04em; }
        .header-usuario { display: flex; align-items: center; gap: 14px; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%; background: rgba(201,168,76,0.25);
            border: 2px solid var(--dorado); color: var(--dorado-claro); font-size: 0.85rem;
            font-weight: 700; display: flex; align-items: center; justify-content: center;
        }
        .header-nombre { color: var(--blanco); font-size: 0.88rem; font-weight: 500; opacity: 0.9; }
        .btn-logout {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco); padding: 7px 14px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.2); }

        .main { max-width: 860px; margin: 0 auto; padding: 40px 24px 80px; }

        .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 28px; font-size: 0.85rem; color: var(--texto-sub); }
        .breadcrumb a { color: var(--verde-medio); text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .breadcrumb a:hover { color: var(--verde); }
        .breadcrumb i { font-size: 0.7rem; color: #cbd5e1; }

        .encabezado { margin-bottom: 32px; }
        .encabezado h1 { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 700; color: var(--texto); margin-bottom: 6px; }
        .encabezado h1 span { color: var(--verde-medio); }
        .encabezado p { color: var(--texto-sub); font-size: 0.9rem; font-weight: 300; line-height: 1.6; }

        .pasos-indicador { display: flex; align-items: center; margin-bottom: 36px; }
        .paso-ind { display: flex; align-items: center; gap: 10px; flex: 1; }
        .paso-ind:last-child { flex: none; }
        .paso-circulo {
            width: 36px; height: 36px; border-radius: 50%; background: var(--borde); color: var(--texto-sub);
            font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: all 0.3s;
        }
        .paso-ind.activo     .paso-circulo { background: var(--verde); color: var(--blanco); box-shadow: 0 0 0 4px rgba(26,92,56,0.15); }
        .paso-ind.completado .paso-circulo { background: var(--verde-claro); color: var(--blanco); }
        .paso-label { font-size: 0.78rem; font-weight: 600; color: var(--texto-sub); text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap; }
        .paso-ind.activo     .paso-label { color: var(--verde); }
        .paso-ind.completado .paso-label { color: var(--verde-claro); }
        .paso-linea { flex: 1; height: 2px; background: var(--borde); margin: 0 12px; transition: background 0.3s; }
        .paso-linea.completado { background: var(--verde-claro); }

        .form-card { background: var(--blanco); border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.05); animation: subirEntrar 0.4s ease both; }
        .form-card-header {
            background: linear-gradient(135deg, var(--verde) 0%, var(--verde-medio) 100%);
            padding: 24px 32px; display: flex; align-items: center; gap: 14px;
        }
        .form-card-header-ico {
            width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 12px;
            display: flex; align-items: center; justify-content: center; color: var(--blanco); font-size: 1.1rem;
        }
        .form-card-header-texto h2 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--blanco); margin-bottom: 2px; }
        .form-card-header-texto p { color: rgba(255,255,255,0.7); font-size: 0.82rem; font-weight: 300; }
        .form-body { padding: 32px; }

        .alerta-error {
            background: #fef2f2; border-left: 4px solid var(--rojo); color: #991b1b;
            padding: 14px 16px; border-radius: 8px; margin-bottom: 28px;
            display: flex; align-items: center; gap: 10px; font-size: 0.9rem; animation: subirEntrar 0.3s ease both;
        }

        .form-seccion { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--borde); }
        .form-seccion:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .form-seccion-titulo {
            font-size: 0.75rem; font-weight: 700; color: var(--texto-sub); text-transform: uppercase;
            letter-spacing: 0.1em; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .form-seccion-titulo::after { content: ''; flex: 1; height: 1px; background: var(--borde); }

        .campos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .campo-full { grid-column: 1 / -1; }
        .campo { display: flex; flex-direction: column; gap: 7px; }
        .campo label { font-size: 0.8rem; font-weight: 600; color: var(--texto); letter-spacing: 0.03em; text-transform: uppercase; }
        .campo label .req { color: var(--rojo); margin-left: 2px; }
        .campo-input-wrap { position: relative; }
        .campo-input-wrap i.ico {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #c4bbb0; font-size: 0.9rem; pointer-events: none; transition: color 0.2s;
        }
        .campo-input-wrap:focus-within i.ico { color: var(--verde-medio); }
        .campo-input-wrap select,
        .campo-input-wrap input,
        .campo-input-wrap textarea {
            width: 100%; padding: 13px 14px 13px 40px; background: var(--blanco);
            border: 1.5px solid var(--borde); border-radius: 10px; font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem; color: var(--texto); outline: none; transition: all 0.25s; appearance: none;
        }
        .campo-input-wrap select:focus,
        .campo-input-wrap input:focus,
        .campo-input-wrap textarea:focus { border-color: var(--verde-medio); box-shadow: 0 0 0 3px rgba(45,122,79,0.1); }
        .campo-input-wrap select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
        .campo-input-wrap textarea { resize: vertical; min-height: 100px; padding-top: 12px; }
        .campo-hint { font-size: 0.78rem; color: var(--texto-sub); font-weight: 300; margin-top: 2px; }

        .horarios-loading { display: none; align-items: center; gap: 10px; color: var(--texto-sub); font-size: 0.88rem; padding: 16px 0; }
        .horarios-loading.visible { display: flex; }
        .horarios-placeholder {
            background: #f8fafc; border: 1.5px dashed var(--borde); border-radius: 10px;
            padding: 20px; text-align: center; color: var(--texto-sub); font-size: 0.88rem; font-weight: 300;
        }
        .horarios-grid-wrap { display: none; }
        .horarios-grid-wrap.visible { display: block; }
        .horario-bloque-titulo {
            font-size: 0.75rem; font-weight: 700; color: var(--texto-sub); text-transform: uppercase;
            letter-spacing: 0.08em; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;
        }
        .horario-bloque-titulo i { color: #94a3b8; }
        .horarios-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; margin-bottom: 20px; }
        .slot {
            padding: 10px 8px; border-radius: 8px; border: 1.5px solid var(--borde); background: var(--blanco);
            text-align: center; font-size: 0.85rem; font-weight: 600; color: var(--texto);
            cursor: pointer; transition: all 0.2s; user-select: none;
        }
        .slot:hover:not(.ocupado) { border-color: var(--verde-medio); background: #f0fdf4; color: var(--verde-medio); }
        .slot.seleccionado { background: var(--verde); border-color: var(--verde); color: var(--blanco); box-shadow: 0 2px 8px rgba(26,92,56,0.3); }
        .slot.ocupado { background: #f8fafc; border-color: #e2e8f0; color: #cbd5e1; cursor: not-allowed; text-decoration: line-through; }
        .leyenda-slots { display: flex; gap: 20px; margin-top: 4px; flex-wrap: wrap; }
        .leyenda-item { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: var(--texto-sub); }
        .leyenda-dot { width: 10px; height: 10px; border-radius: 3px; border: 1.5px solid; }
        .leyenda-dot.libre        { background: var(--blanco); border-color: var(--borde); }
        .leyenda-dot.seleccionado { background: var(--verde);  border-color: var(--verde); }
        .leyenda-dot.ocupado      { background: #f8fafc; border-color: #e2e8f0; }
        #hora_inicio { display: none; }

        .info-horario {
            background: rgba(201,168,76,0.08); border: 1px dashed var(--dorado); border-radius: 10px;
            padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start; margin-bottom: 24px;
        }
        .info-horario i { color: var(--dorado); margin-top: 2px; font-size: 0.95rem; }
        .info-horario span { font-size: 0.85rem; color: var(--texto-sub); line-height: 1.5; }
        .info-horario strong { color: var(--texto); }

        /* Badge cargo del funcionario */
        .funcionario-cargo {
            font-size: 0.75rem; color: var(--texto-sub); font-style: italic;
            margin-top: 4px; display: none;
        }

        .form-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 28px; border-top: 1px solid var(--borde); margin-top: 32px;
            gap: 16px; flex-wrap: wrap;
        }
        .btn-volver {
            display: flex; align-items: center; gap: 8px; color: var(--texto-sub); text-decoration: none;
            font-size: 0.9rem; font-weight: 500; padding: 12px 20px; border: 1.5px solid var(--borde);
            border-radius: 10px; background: var(--blanco); transition: all 0.2s;
            cursor: pointer; font-family: 'DM Sans', sans-serif;
        }
        .btn-volver:hover { border-color: var(--texto-sub); color: var(--texto); }
        .btn-agendar-submit {
            display: flex; align-items: center; gap: 10px; background: var(--verde); color: var(--blanco);
            border: none; padding: 14px 32px; border-radius: 10px; font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.25s;
            position: relative; overflow: hidden; box-shadow: 0 4px 14px rgba(26,92,56,0.25);
        }
        .btn-agendar-submit::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent); }
        .btn-agendar-submit:hover { background: var(--verde-medio); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,92,56,0.3); }
        .btn-agendar-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        @keyframes subirEntrar { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        body::-webkit-scrollbar { width: 8px; }
        body::-webkit-scrollbar-track { background: var(--fondo); }
        body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        body::-webkit-scrollbar-thumb:hover { background: var(--verde-medio); }

        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .main { padding: 24px 16px 60px; }
            .campos-grid { grid-template-columns: 1fr; }
            .campo-full { grid-column: 1; }
            .form-body { padding: 20px; }
            .pasos-indicador { display: none; }
            .header-nombre { display: none; }
        }
        @media (max-width: 480px) {
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
            .main { padding: 16px 12px 60px; }
            .breadcrumb { font-size: 0.8rem; margin-bottom: 16px; }
            .encabezado h1 { font-size: 1.45rem; }
            .encabezado { margin-bottom: 20px; }
            .form-card-header { padding: 18px 16px; }
            .form-card-header-ico { width: 36px; height: 36px; font-size: 0.95rem; border-radius: 10px; }
            .form-card-header-texto h2 { font-size: 1rem; }
            .form-body { padding: 16px; }
            .form-seccion { margin-bottom: 24px; padding-bottom: 24px; }
            .horarios-grid { grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 6px; }
            .slot { padding: 8px 6px; font-size: 0.8rem; }
            .leyenda-slots { gap: 12px; }
            .info-horario { padding: 10px 12px; font-size: 0.82rem; }
            .form-footer { flex-direction: column; gap: 12px; padding-top: 20px; margin-top: 20px; }
            .btn-volver, .btn-agendar-submit { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<header class="header">
    <a href="/dashboard" class="header-brand">
        <img src="../../imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="header-brand-texto">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </a>
    <div class="header-usuario">
        <div class="avatar"><?= htmlspecialchars($ciudadanoIniciales) ?></div>
        <span class="header-nombre"><?= htmlspecialchars($ciudadanoNombre) ?></span>
        <a href="/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</header>

<main class="main">

    <div class="breadcrumb">
        <a href="/dashboard"><i class="fas fa-home"></i> Mi Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Agendar Cita</span>
    </div>

    <div class="encabezado">
        <h1>Agendar <span>nueva cita</span></h1>
        <p>Selecciona la dependencia, funcionario, fecha y hora para tu visita a la alcaldía.</p>
    </div>

    <div class="pasos-indicador">
        <div class="paso-ind activo" id="paso-1">
            <div class="paso-circulo">1</div>
            <div class="paso-label">Dependencia</div>
        </div>
        <div class="paso-linea" id="linea-1"></div>
        <div class="paso-ind" id="paso-2">
            <div class="paso-circulo">2</div>
            <div class="paso-label">Funcionario</div>
        </div>
        <div class="paso-linea" id="linea-2"></div>
        <div class="paso-ind" id="paso-3">
            <div class="paso-circulo">3</div>
            <div class="paso-label">Fecha y hora</div>
        </div>
        <div class="paso-linea" id="linea-3"></div>
        <div class="paso-ind" id="paso-4">
            <div class="paso-circulo">4</div>
            <div class="paso-label">Motivo</div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-ico"><i class="fas fa-calendar-plus"></i></div>
            <div class="form-card-header-texto">
                <h2>Solicitud de cita</h2>
                <p>Completa todos los campos para agendar tu visita</p>
            </div>
        </div>

        <div class="form-body">

            <?php if ($error): ?>
            <div class="alerta-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="formAgendar">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <input type="hidden" name="hora_inicio" id="hora_inicio">

                <!-- Sección 1: Dependencia y funcionario -->
                <div class="form-seccion">
                    <div class="form-seccion-titulo"><i class="fas fa-building"></i> Dependencia y funcionario</div>
                    <div class="campos-grid">
                        <div class="campo">
                            <label>Dependencia <span class="req">*</span></label>
                            <div class="campo-input-wrap">
                                <i class="fas fa-building ico"></i>
                                <select name="dependencia_id" id="dependencia_id" required
                                        onchange="cargarFuncionarios(this.value)">
                                    <option value="">Selecciona una dependencia...</option>
                                    <?php foreach ($dependencias as $dep): ?>
                                    <option value="<?= $dep['id_dependencia'] ?>"
                                        <?= (isset($_POST['dependencia_id']) && $_POST['dependencia_id'] == $dep['id_dependencia']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dep['nombre']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="campo">
                            <label>Funcionario <span class="req">*</span></label>
                            <div class="campo-input-wrap">
                                <i class="fas fa-user-tie ico"></i>
                                <select name="funcionario_id" id="funcionario_id" required disabled
                                        onchange="mostrarCargo(this)">
                                    <option value="">Primero selecciona una dependencia...</option>
                                </select>
                            </div>
                            <span class="campo-hint" id="hint-funcionario">Selecciona primero una dependencia</span>
                            <!-- Muestra el cargo del funcionario seleccionado -->
                            <span class="funcionario-cargo" id="funcionario-cargo"></span>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Fecha y hora -->
                <div class="form-seccion">
                    <div class="form-seccion-titulo"><i class="fas fa-calendar-alt"></i> Fecha y horario</div>
                    <div class="info-horario">
                        <i class="fas fa-info-circle"></i>
                        <span id="info-horario-texto">Cargando información de horarios...</span>
                    </div>
                    <div class="campos-grid">
                        <div class="campo">
                            <label>Fecha <span class="req">*</span></label>
                            <div class="campo-input-wrap">
                                <i class="fas fa-calendar ico"></i>
                                <input type="date" name="fecha" id="fecha" required
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : '' ?>"
                                       onchange="cargarHorarios()">
                            </div>
                            <span class="campo-hint" id="hint-dias-habiles">Solo días hábiles</span>
                        </div>
                        <div class="campo">
                            <label>Hora seleccionada</label>
                            <div class="campo-input-wrap">
                                <i class="fas fa-clock ico"></i>
                                <input type="text" id="hora-display" readonly
                                       placeholder="Selecciona un horario abajo..."
                                       style="cursor:default; background:#f8fafc;">
                            </div>
                        </div>
                    </div>

                    <div id="horarios-wrap" style="margin-top:20px;">
                        <div class="horarios-loading" id="horarios-loading">
                            <i class="fas fa-spinner fa-spin"></i> Consultando disponibilidad...
                        </div>
                        <div class="horarios-placeholder" id="horarios-placeholder">
                            <i class="fas fa-calendar-day" style="margin-right:8px; color:#cbd5e1;"></i>
                            Selecciona un funcionario y una fecha para ver los horarios disponibles
                        </div>
                        <div class="horarios-grid-wrap" id="horarios-grid-wrap">
                            <div class="horario-bloque-titulo"><i class="fas fa-sun"></i> Mañana</div>
                            <div class="horarios-grid" id="slots-manana"></div>
                            <div class="horario-bloque-titulo"><i class="fas fa-cloud-sun"></i> Tarde</div>
                            <div class="horarios-grid" id="slots-tarde"></div>
                            <div class="leyenda-slots">
                                <div class="leyenda-item"><div class="leyenda-dot libre"></div> Disponible</div>
                                <div class="leyenda-item"><div class="leyenda-dot seleccionado"></div> Seleccionado</div>
                                <div class="leyenda-item"><div class="leyenda-dot ocupado"></div> Ocupado</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Motivo -->
                <div class="form-seccion">
                    <div class="form-seccion-titulo"><i class="fas fa-comment-alt"></i> Motivo de la visita</div>
                    <div class="campo campo-full">
                        <label>Describe el motivo de tu visita <span class="req">*</span></label>
                        <div class="campo-input-wrap">
                            <i class="fas fa-align-left ico" style="top:18px; transform:none;"></i>
                            <textarea name="motivo" id="motivo" required
                                      placeholder="Ej: Solicitud de certificado de residencia, consulta sobre impuesto predial..."
                                      oninput="actualizarContador()"><?= isset($_POST['motivo']) ? htmlspecialchars($_POST['motivo']) : '' ?></textarea>
                        </div>
                        <span class="campo-hint" id="contador-motivo">
                            Mínimo 10 caracteres · <span id="chars">0</span> escritos
                        </span>
                    </div>
                </div>

                <div class="form-footer">
                    <a href="/dashboard" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Volver al dashboard
                    </a>
                    <button type="submit" class="btn-agendar-submit" id="btnSubmit">
                        <i class="fas fa-calendar-check"></i> Confirmar cita
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
    const mapaCargos = {};
    let configSistema = null;

    // Cargar configuración al inicio
    fetch('/agendar?action=config&tab_id=' + window.TAB_ID)
        .then(r => r.json())
        .then(config => {
            configSistema = config;
            actualizarInfoHorario(config);
        })
        .catch(() => {
            // Si falla, mantener texto genérico
            document.getElementById('info-horario-texto').innerHTML =
                'Consulta los horarios de atención disponibles.';
        });
    // Bloquear fecha de hoy y anteriores — mínimo mañana
    function establecerFechaMinima() {
    const hoy    = new Date();
    const anio   = hoy.getFullYear();
    const mes    = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia    = String(hoy.getDate()).padStart(2, '0');
    const hoyStr = `${anio}-${mes}-${dia}`;

    // Sumar 1 día para el min
    const manana     = new Date(hoy);
    manana.setDate(hoy.getDate() + 1);
    const mananaStr  = `${manana.getFullYear()}-${String(manana.getMonth()+1).padStart(2,'0')}-${String(manana.getDate()).padStart(2,'0')}`;

    document.getElementById('fecha').min = mananaStr;
}

establecerFechaMinima();
    function actualizarInfoHorario(config) {
        // Construir lista de días laborables
        const nombresDias = {
            lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles',
            jueves: 'Jueves', viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo'
        };
        const diasActivos = Object.keys(nombresDias)
            .filter(d => config[d] === true || config[d] === 't' || config[d] === '1')
            .map(d => nombresDias[d]);

        const diasTexto = diasActivos.join(', ');

        // Formatear horas
        const fmtHora = h => {
            const [hh, mm] = h.split(':');
            const n = parseInt(hh);
            const ampm = n >= 12 ? 'PM' : 'AM';
            const h12 = n > 12 ? n - 12 : (n === 0 ? 12 : n);
            return `${h12}:${mm} ${ampm}`;
        };

        const mananaInicio = fmtHora(config.manana_inicio.substring(0, 5));
        const mananaFin    = fmtHora(config.manana_fin.substring(0, 5));
        const tardeInicio  = fmtHora(config.tarde_inicio.substring(0, 5));
        const tardeFin     = fmtHora(config.tarde_fin.substring(0, 5));

        document.getElementById('info-horario-texto').innerHTML =
            `Atención: <strong>${diasTexto}</strong> · 
             Mañana: <strong>${mananaInicio} – ${mananaFin}</strong> · 
             Tarde: <strong>${tardeInicio} – ${tardeFin}</strong> · 
             Duración: <strong>${config.intervalo_min} minutos</strong>`;

        document.getElementById('hint-dias-habiles').textContent =
            `Solo días hábiles (${diasTexto.toLowerCase()})`;
    }

    function esDiaHabil(fechaStr) {
        if (!configSistema) return true;
        const dia = new Date(fechaStr + 'T00:00:00').getDay();
        const mapaDias = {
            0: 'domingo', 1: 'lunes', 2: 'martes', 3: 'miercoles',
            4: 'jueves',  5: 'viernes', 6: 'sabado'
        };
        const campo = mapaDias[dia];
        const val = configSistema[campo];
        return val === true || val === 't' || val === '1';
    }

    function esFestivo(fechaStr) {
        if (!configSistema || !configSistema._festivos) return null;
        const [anio, mes, dia] = fechaStr.split('-');
        const claveFull = fechaStr;          // YYYY-MM-DD
        const claveAnual = `${mes}-${dia}`;  // MM-DD
        for (const f of configSistema._festivos) {
            if (f.recurrente === true || f.recurrente === 't' || f.recurrente === '1') {
                if (f.clave === claveAnual) return f.descripcion;
            } else {
                if (f.clave === claveFull) return f.descripcion;
            }
        }
        return null;
    }

    document.getElementById('fecha').addEventListener('input', function () {
    const hoy     = new Date();
    const anio    = hoy.getFullYear();
    const mes     = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia     = String(hoy.getDate()).padStart(2, '0');
    const hoyStr  = `${anio}-${mes}-${dia}`;

    // Bloquear hoy y fechas anteriores
    if (this.value <= hoyStr) {
        this.value = '';
        mostrarAviso('Fecha no válida', 'Solo puedes agendar citas a partir de mañana.', 'warn');
        resetHorarios();
        return;
    }

    // Verificar si es festivo
    const nombreFestivo = esFestivo(this.value);
    if (nombreFestivo) {
        this.value = '';
        mostrarAviso('Día festivo', `Ese día es festivo (${nombreFestivo}) y la alcaldía no tiene atención. Por favor elige otra fecha.`, 'error');
        resetHorarios();
        return;
    }

    // Validar día hábil
    if (!esDiaHabil(this.value)) {
        const nombresDias = {domingo:'domingo',lunes:'lunes',martes:'martes',miercoles:'miércoles',jueves:'jueves',viernes:'viernes',sabado:'sábado'};
        let diasDisponibles = '';
        if (configSistema) {
            const activos = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']
                .filter(d => configSistema[d] === true || configSistema[d] === 't' || configSistema[d] === '1')
                .map(d => nombresDias[d]);
            if (activos.length) diasDisponibles = ' Los días disponibles son: ' + activos.join(', ') + '.';
        }
        this.value = '';
        mostrarAviso('Sin atención ese día', `La alcaldía no atiende ese día.${diasDisponibles}`, 'error');
        resetHorarios();
    }
});

    function cargarFuncionarios(depId) {
        const sel   = document.getElementById('funcionario_id');
        const hint  = document.getElementById('hint-funcionario');
        const cargo = document.getElementById('funcionario-cargo');

        sel.disabled = true;
        sel.innerHTML = '<option value="">Cargando...</option>';
        cargo.style.display = 'none';
        resetHorarios();

        if (!depId) {
            sel.innerHTML = '<option value="">Primero selecciona una dependencia...</option>';
            hint.textContent = 'Selecciona primero una dependencia';
            actualizarPasos(1);
            return;
        }

        fetch(`/agendar?action=funcionarios&dependencia_id=${depId}&tab_id=${window.TAB_ID}`)
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    sel.innerHTML = '<option value="">Sin funcionarios disponibles</option>';
                    hint.textContent = 'Esta dependencia no tiene funcionarios activos';
                } else {
                    sel.innerHTML = '<option value="">Selecciona un funcionario...</option>';
                    data.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f.id_funcionario;
                        opt.textContent = f.nombres + ' ' + f.apellidos;
                        opt.dataset.cargo = f.cargo;
                        sel.appendChild(opt);
                        mapaCargos[f.id_funcionario] = f.cargo;
                    });
                    sel.disabled = false;
                    hint.textContent = data.length + ' funcionario(s) disponible(s)';
                    actualizarPasos(2);
                }
            })
            .catch(() => {
                sel.innerHTML = '<option value="">Error al cargar</option>';
                hint.textContent = 'Error al cargar funcionarios';
            });
    }

    function mostrarCargo(sel) {
        const cargo = document.getElementById('funcionario-cargo');
        const id = sel.value;
        if (id && mapaCargos[id]) {
            cargo.textContent = '' + mapaCargos[id];
            cargo.style.display = 'block';
        } else {
            cargo.style.display = 'none';
        }
        resetHorarios();
        if (document.getElementById('fecha').value) cargarHorarios();
    }

    function cargarHorarios() {
        const funcId = document.getElementById('funcionario_id').value;
        const fecha  = document.getElementById('fecha').value;
        if (!funcId || !fecha) return;

        document.getElementById('horarios-placeholder').style.display = 'none';
        document.getElementById('horarios-grid-wrap').classList.remove('visible');
        document.getElementById('horarios-loading').classList.add('visible');

        fetch(`/agendar?action=horarios&funcionario_id=${funcId}&fecha=${fecha}&tab_id=${window.TAB_ID}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('horarios-loading').classList.remove('visible');
                if (data.error) {
                    const err = data.error.toLowerCase();
                    if (err.includes('festivo')) {
                        document.getElementById('fecha').value = '';
                        resetHorarios();
                        mostrarAviso('Día festivo', data.error, 'error');
                    } else if (err.includes('no se atiende') || err.includes('no hábil') || err.includes('no habil')) {
                        document.getElementById('fecha').value = '';
                        resetHorarios();
                        const diasDisponibles = (() => {
                            if (!configSistema) return '';
                            const nombres = {lunes:'lunes',martes:'martes',miercoles:'miércoles',jueves:'jueves',viernes:'viernes',sabado:'sábado',domingo:'domingo'};
                            const activos = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']
                                .filter(d => configSistema[d] === true || configSistema[d] === 't' || configSistema[d] === '1')
                                .map(d => nombres[d]);
                            return activos.length ? ' Los días disponibles son: ' + activos.join(', ') + '.' : '';
                        })();
                        mostrarAviso('Sin atención ese día', 'La alcaldía no atiende ese día.' + diasDisponibles, 'error');
                    } else if (err.includes('hoy') || err.includes('anteriores') || err.includes('mañana')) {
                        document.getElementById('fecha').value = '';
                        resetHorarios();
                        mostrarAviso('Fecha no válida', 'Solo puedes agendar citas a partir de mañana.', 'warn');
                    } else {
                        document.getElementById('horarios-placeholder').textContent = data.error;
                        document.getElementById('horarios-placeholder').style.display = 'block';
                    }
                    return;
                }
                renderSlots('slots-manana', data.manana);
                renderSlots('slots-tarde',  data.tarde);
                document.getElementById('horarios-grid-wrap').classList.add('visible');
                actualizarPasos(3);
            })
            .catch(() => {
                document.getElementById('horarios-loading').classList.remove('visible');
                document.getElementById('horarios-placeholder').textContent = 'Error al consultar horarios.';
                document.getElementById('horarios-placeholder').style.display = 'block';
            });
    }

    function renderSlots(id, slots) {
        const c = document.getElementById(id);
        c.innerHTML = '';
        slots.forEach(s => {
            const d = document.createElement('div');
            d.className = 'slot' + (s.disponible ? '' : ' ocupado');
            d.textContent = formatHora(s.hora);
            d.dataset.hora = s.hora;
            if (s.disponible) d.onclick = () => seleccionarSlot(d, s.hora);
            c.appendChild(d);
        });
    }

    function seleccionarSlot(el, hora) {
        document.querySelectorAll('.slot.seleccionado').forEach(s => s.classList.remove('seleccionado'));
        el.classList.add('seleccionado');
        document.getElementById('hora_inicio').value = hora;
        document.getElementById('hora-display').value = formatHora(hora);
        actualizarPasos(4);
    }

    function resetHorarios() {
        const ph = document.getElementById('horarios-placeholder');
        ph.style.display = 'block';
        ph.textContent = 'Selecciona un funcionario y una fecha para ver los horarios disponibles';
        document.getElementById('horarios-grid-wrap').classList.remove('visible');
        document.getElementById('horarios-loading').classList.remove('visible');
        document.getElementById('hora_inicio').value = '';
        document.getElementById('hora-display').value = '';
        document.querySelectorAll('.slot.seleccionado').forEach(s => s.classList.remove('seleccionado'));
    }

    function formatHora(hora) {
        const [h, m] = hora.split(':');
        const hNum = parseInt(h);
        const ampm = hNum >= 12 ? 'PM' : 'AM';
        const h12  = hNum > 12 ? hNum - 12 : (hNum === 0 ? 12 : hNum);
        return `${h12}:${m} ${ampm}`;
    }

    function actualizarPasos(activo) {
        for (let i = 1; i <= 4; i++) {
            const paso  = document.getElementById('paso-' + i);
            const linea = document.getElementById('linea-' + i);
            paso.classList.remove('activo', 'completado');
            if (linea) linea.classList.remove('completado');
            if (i < activo)       { paso.classList.add('completado'); if (linea) linea.classList.add('completado'); }
            else if (i === activo) { paso.classList.add('activo'); }
        }
    }

    function actualizarContador() {
        const len = document.getElementById('motivo').value.length;
        document.getElementById('chars').textContent = len;
        document.getElementById('contador-motivo').style.color = len >= 10 ? '#22c55e' : '';
        if (len >= 10) actualizarPasos(4);
    }

    document.getElementById('formAgendar').addEventListener('submit', function(e) {
        if (!document.getElementById('hora_inicio').value) {
            e.preventDefault();
            mostrarAviso('Horario requerido', 'Por favor selecciona un horario disponible antes de continuar.', 'warn');
            return;
        }
        if (document.getElementById('motivo').value.trim().length < 10) {
            e.preventDefault();
            mostrarAviso('Motivo muy corto', 'El motivo de la visita debe tener al menos 10 caracteres.', 'warn');
            return;
        }
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agendando...';
    });

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['dependencia_id'])): ?>
    window.addEventListener('load', () => {
        cargarFuncionarios(<?= (int)$_POST['dependencia_id'] ?>);
        setTimeout(() => {
            document.getElementById('funcionario_id').value = '<?= (int)($_POST['funcionario_id'] ?? 0) ?>';
            actualizarContador();
        }, 700);
    });
    <?php endif; ?>

    function mostrarAviso(titulo, mensaje, tipo) {
        document.getElementById('aviso-titulo').textContent  = titulo;
        document.getElementById('aviso-mensaje').textContent = mensaje;
        var ico = document.getElementById('aviso-icono');
        if (tipo === 'warn') {
            ico.className = 'fas fa-exclamation-triangle';
            ico.style.color = '#f59e0b';
        } else {
            ico.className = 'fas fa-exclamation-circle';
            ico.style.color = '#ef4444';
        }
        document.getElementById('modal-aviso').style.display = 'flex';
    }
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('aviso-cerrar');
        var overlay = document.getElementById('modal-aviso');
        if (btn) btn.addEventListener('click', function() { overlay.style.display = 'none'; });
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.style.display = 'none'; });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') overlay.style.display = 'none'; });
    });
</script>

<!-- ══ Modal Aviso ══ -->
<div id="modal-aviso" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:16px;padding:28px 28px 22px;max-width:400px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;">
        <i id="aviso-icono" class="fas fa-exclamation-circle" style="font-size:2.2rem;margin-bottom:14px;display:block;"></i>
        <h3 id="aviso-titulo" style="font-size:1.05rem;font-weight:600;color:#1e293b;margin-bottom:10px;"></h3>
        <p id="aviso-mensaje" style="font-size:0.9rem;color:#475569;line-height:1.55;margin-bottom:22px;"></p>
        <button id="aviso-cerrar" style="background:#1a5c38;color:#fff;border:none;border-radius:10px;padding:10px 28px;font-size:0.9rem;font-weight:600;cursor:pointer;">Entendido</button>
    </div>
</div>

</body>
</html>