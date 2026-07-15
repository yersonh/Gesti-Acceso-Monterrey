<?php
// Vista — la lógica está en AuthController::login()
// Variables disponibles: $error
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP — Acceso al Sistema</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde-institucional: #1a5c38;
            --verde-medio: #2d7a4f;
            --verde-claro: #3d9e68;
            --dorado: #c9a84c;
            --dorado-claro: #e8c97a;
            --crema: #f8f4ed;
            --blanco: #ffffff;
            --gris-texto: #2c2c2c;
            --gris-sub: #6b6b6b;
            --gris-borde: #d8d0c4;
            --sombra: rgba(26, 92, 56, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            background-color: #0e2a1a;
            display: flex;
            overflow: auto;
        }

        /* ── Panel izquierdo decorativo ── */
        .panel-izquierdo {
            width: 55%;
            position: relative;
            background:
                linear-gradient(160deg, rgba(10, 40, 22, 0.92) 0%, rgba(26, 92, 56, 0.85) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 56px;
            overflow: auto;
            min-height: 100vh;
        }

        /* Patrón geométrico sutil */
        .panel-izquierdo::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(201, 168, 76, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(61, 158, 104, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Líneas decorativas */
        .panel-izquierdo::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom,
                transparent 0%,
                rgba(201, 168, 76, 0.4) 30%,
                rgba(201, 168, 76, 0.4) 70%,
                transparent 100%);
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.05);
            padding: 12px 20px;
            border-radius: 60px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(201, 168, 76, 0.3);
            max-width: fit-content;
        }

        .brand-top img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--dorado);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            filter: brightness(1.1);
            transition: all 0.3s ease;
        }
        .brand-top img:hover {
            transform: scale(1.05);
            border-color: var(--dorado-claro);
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.4);
        }
        .brand-nombre {
            color: var(--blanco);
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.9;
            line-height: 1.5;
        }

        .brand-nombre strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            opacity: 1;
            letter-spacing: 0.06em;
            color: var(--dorado-claro);
        }

        .contenido-central {
            position: relative;
            z-index: 1;
        }

        .etiqueta-sistema {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201, 168, 76, 0.15);
            border: 1px solid rgba(201, 168, 76, 0.35);
            color: var(--dorado-claro);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 28px;
        }

        .etiqueta-sistema::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--dorado);
            border-radius: 50%;
            animation: pulso 2s infinite;
        }

        @keyframes pulso {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .titulo-hero {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 3.5vw, 3.2rem);
            font-weight: 700;
            color: var(--blanco);
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .titulo-hero span {
            color: var(--dorado-claro);
        }

        .desc-hero {
            color: rgba(255,255,255,0.65);
            font-size: 0.95rem;
            line-height: 1.7;
            font-weight: 300;
            max-width: 420px;
        }

        .separador-dorado {
            width: 48px;
            height: 2px;
            background: linear-gradient(to right, var(--dorado), transparent);
            margin: 24px 0;
        }

        .stats-row {
            display: flex;
            gap: 36px;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            color: var(--blanco);
        }

        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dorado-claro);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 4px;
            font-weight: 500;
        }

        /* ── Panel derecho — formulario ── */
        .panel-derecho {
            width: 45%;
            background: var(--crema);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 60px 56px;
            position: relative;
            overflow-y: auto;
            min-height: 100vh;
        }

        .panel-derecho::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
        }

        .form-encabezado {
            margin-bottom: 40px;
        }
        /* Personalización del scroll para toda la página */
        body::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        body::-webkit-scrollbar-track {
            background: var(--crema);
        }

        body::-webkit-scrollbar-thumb {
            background: var(--verde-medio);
            border-radius: 4px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: var(--verde-institucional);
        }

        /* Personalización del scroll para panel izquierdo */
        .panel-izquierdo::-webkit-scrollbar {
            width: 6px;
        }

        .panel-izquierdo::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .panel-izquierdo::-webkit-scrollbar-thumb {
            background: var(--dorado);
            border-radius: 3px;
        }

        /* Personalización del scroll para panel derecho */
        .panel-derecho::-webkit-scrollbar {
            width: 6px;
        }

        .panel-derecho::-webkit-scrollbar-track {
            background: #e8e0d5;
        }

        .panel-derecho::-webkit-scrollbar-thumb {
            background: var(--verde-medio);
            border-radius: 3px;
        }

        .panel-derecho::-webkit-scrollbar-thumb:hover {
            background: var(--verde-institucional);
        }

        .form-encabezado .bienvenida {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--verde-claro);
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-bottom: 10px;
        }

        .form-encabezado h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gris-texto);
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .form-encabezado p {
            color: var(--gris-sub);
            font-size: 0.88rem;
            font-weight: 300;
            line-height: 1.6;
        }

        /* Error */
        .error-box {
            display: <?php echo $error ? 'flex' : 'none'; ?>;
            align-items: flex-start;
            gap: 10px;
            background: #fff5f5;
            border: 1px solid #fca5a5;
            border-left: 3px solid #ef4444;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .error-box i { margin-top: 2px; flex-shrink: 0; }

        /* Grupos de campo */
        .campo {
            margin-bottom: 22px;
        }

        .campo label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gris-texto);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .campo-input {
            position: relative;
        }

        .campo-input i.icono-campo {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gris-borde);
            font-size: 0.9rem;
            transition: color 0.25s;
            pointer-events: none;
        }

        .campo-input input {
            width: 100%;
            padding: 13px 14px 13px 40px;
            background: var(--blanco);
            border: 1.5px solid var(--gris-borde);
            border-radius: 10px;
            font-size: 0.92rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--gris-texto);
            transition: all 0.25s;
            outline: none;
        }

        .campo-input input::placeholder {
            color: #b8b0a6;
            font-weight: 300;
        }

        .campo-input input:focus {
            border-color: var(--verde-medio);
            box-shadow: 0 0 0 3px rgba(45, 122, 79, 0.1);
        }

        .campo-input input:focus + .icono-campo,
        .campo-input:focus-within i.icono-campo {
            color: var(--verde-medio);
        }

        .btn-ver {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b8b0a6;
            cursor: pointer;
            padding: 4px;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .btn-ver:hover { color: var(--verde-medio); }

        /* Opciones bajo el campo contraseña */
        .opciones-password {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -10px;
            margin-bottom: 28px;
        }

        .checkbox-recordar {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-recordar input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--verde-institucional);
            cursor: pointer;
        }

        .checkbox-recordar span {
            font-size: 0.82rem;
            color: var(--gris-sub);
            font-weight: 400;
        }

        .link-olvido {
            font-size: 0.82rem;
            color: var(--verde-medio);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link-olvido:hover { color: var(--verde-institucional); }

        /* NUEVO: Estilo para enlace de registro (igual que olvidó contraseña) */
        .registro-link {
            font-size: 0.82rem;
            color: var(--verde-medio);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .registro-link:hover { 
            color: var(--verde-institucional); 
        }

        .registro-link i {
            font-size: 0.75rem;
        }

        /* Botón principal */
        .btn-ingresar {
            width: 100%;
            padding: 14px;
            background: var(--verde-institucional);
            color: var(--blanco);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .btn-ingresar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }

        .btn-ingresar:hover {
            background: var(--verde-medio);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(26, 92, 56, 0.3);
        }

        .btn-ingresar:active { transform: translateY(0); }

        /* Franja inferior */
        .footer-form {
            margin-top: 18px;
            padding-top: 24px;
            border-top: 1px solid var(--gris-borde);
        }

        .footer-form p {
            font-size: 0.75rem;
            color: #a09890;
            line-height: 1.7;
            text-align: center;
        }

        .footer-form strong {
            color: var(--gris-sub);
            font-weight: 600;
        }

        /* Animación de entrada */
        .panel-derecho > * {
            animation: subirEntrar 0.5s ease both;
        }

        .panel-derecho > *:nth-child(1) { animation-delay: 0.05s; }
        .panel-derecho > *:nth-child(2) { animation-delay: 0.12s; }
        .panel-derecho > *:nth-child(3) { animation-delay: 0.18s; }

        @keyframes subirEntrar {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsivo desktop (tablet ancha) ── */
        @media (max-width: 900px) and (min-width: 601px) {
            body { flex-direction: column; overflow: auto; }

            .panel-izquierdo {
                width: 100%;
                min-height: auto;
                padding: 36px 32px;
            }

            .titulo-hero { font-size: 1.9rem; }
            .stats-row { display: none; }

            .panel-derecho {
                width: 100%;
                padding: 44px 32px;
                min-height: auto;
            }
        }

        /* ── Ocultar layout desktop en móvil ── */
        @media (max-width: 600px) {
            .panel-izquierdo,
            .panel-derecho { display: none !important; }
            .mobile-login  { display: flex !important; }
        }

        /* ══════════════════════════════════════════
           LAYOUT MÓVIL — oculto en desktop
        ══════════════════════════════════════════ */
        .mobile-login {
            display: none;
            min-height: 100vh;
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background:
                radial-gradient(ellipse at 20% 0%, rgba(61,158,104,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 100%, rgba(201,168,76,0.12) 0%, transparent 55%),
                linear-gradient(175deg, #0a2014 0%, #1a5c38 45%, #0e3522 100%);
            padding: 0 0 40px;
            overflow-y: auto;
        }

        /* Franja dorada superior */
        .mobile-login::before {
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            flex-shrink: 0;
            background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
        }

        /* ── Cabecera móvil ── */
        .m-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 28px 24px 20px;
            text-align: center;
            width: 100%;
        }

        .m-logo-wrap {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 0 6px rgba(201,168,76,0.15), 0 8px 28px rgba(0,0,0,0.4);
            overflow: hidden;
            margin-bottom: 14px;
            background: #fff;
        }

        .m-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .m-title {
            color: var(--blanco);
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            opacity: 0.75;
            margin-bottom: 3px;
        }

        .m-subtitle {
            color: var(--dorado-claro);
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .m-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.35);
            color: var(--dorado-claro);
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-top: 10px;
        }

        .m-badge::before {
            content: '';
            width: 5px; height: 5px;
            background: var(--dorado);
            border-radius: 50%;
            animation: pulso 2s infinite;
        }

        /* ── Tarjeta del formulario ── */
        .m-card {
            width: calc(100% - 32px);
            max-width: 420px;
            background: var(--crema);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45), 0 0 0 1px rgba(201,168,76,0.12);
            padding: 28px 22px 24px;
            margin: 0 16px;
        }

        .m-card-header {
            margin-bottom: 24px;
        }

        .m-card-header .m-bienvenida {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--verde-claro);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .m-card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--gris-texto);
            line-height: 1.15;
            margin-bottom: 5px;
        }

        .m-card-header p {
            color: var(--gris-sub);
            font-size: 0.82rem;
            font-weight: 300;
            line-height: 1.55;
        }

        /* Reutiliza los mismos estilos de campo, error-box, etc. */
        .m-card .error-box   { display: <?php echo $error ? 'flex' : 'none'; ?>; margin-bottom: 18px; }
        .m-card .campo       { margin-bottom: 18px; }
        .m-card .btn-ingresar { margin-top: 4px; padding: 15px; font-size: 1rem; border-radius: 12px; }
        .m-card .opciones-password { margin-bottom: 22px; }

        .m-footer {
            margin-top: 16px;
            text-align: center;
        }

        .m-footer .m-registro-link {
            font-size: 0.82rem;
            color: var(--verde-claro);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 16px;
        }

        .m-footer p {
            font-size: 0.68rem;
            color: #a09890;
            line-height: 1.65;
        }

        .m-footer strong { color: var(--gris-sub); font-weight: 600; }

        /* Entrada animada de la tarjeta */
        @keyframes cardEntrar {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .m-card { animation: cardEntrar 0.45s ease both 0.1s; }
        .m-header { animation: cardEntrar 0.4s ease both 0.0s; }


        /* Logo NexGovIA - Desktop */
        .nexgov-logo {
            margin-top: 20px;
            text-align: center;
        }

        .nexgov-logo img {
            max-width: 240px;
            height: auto;
            opacity: 0.8;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .nexgov-logo img:hover {
            opacity: 1;
            transform: scale(1.02);
        }

        /* Logo NexGovIA - Móvil */
        .m-nexgov-logo {
            margin: 15px 0 6px 0;
            text-align: center;
        }

        .m-nexgov-logo img {
            max-width: 190px;
            width: 100%;
            height: auto;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        @media (max-width: 480px) {
            .m-nexgov-logo img {
                max-width: 165px;
            }
        }
    </style>
</head>
<body>

    <!-- ── Panel izquierdo ── -->
    <div class="panel-izquierdo">
        <div class="brand-top">
            <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            <div class="brand-nombre">
                Alcaldía Municipal<br>
                <strong>Monterrey · Casanare</strong>
            </div>
        </div>

        <div class="contenido-central">
            <div class="etiqueta-sistema">Sistema Activo 2026</div>
            <h1 class="titulo-hero">
                Plataforma de<br>
                <span>Control de Visitas</span><br>
                Municipal
            </h1>
            <div class="separador-dorado"></div>
            <p class="desc-hero">
                Acceso para funcionarios y ciudadanos del municipio de Monterrey. Gestione sus trámites y citas de manera segura y eficiente.
            </p>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-num">SCV</div>
                <div class="stat-label">Sistema de Control de Visitas</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">2026</div>
                <div class="stat-label">Versión Enterprise</div>
            </div>
        </div>
    </div>

    <!-- ── Panel derecho (formulario) ── -->
    <div class="panel-derecho">
        <div class="form-encabezado">
            <div class="bienvenida">Bienvenido</div>
            <h2>Inicie sesión</h2>
            <p>Ingrese sus credenciales para acceder al sistema de gestión municipal.</p>
        </div>

        <?php if ($error): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="formLogin">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <div class="campo-input">
                    <i class="fas fa-envelope icono-campo"></i>
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="correo@ejemplo.com"
                           required
                           autocomplete="username"
                           <?= ($bloqueado ?? false) ? 'disabled' : '' ?>
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <div class="campo-input">
                    <i class="fas fa-lock icono-campo"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Ingrese su contraseña"
                           required
                           autocomplete="current-password"
                           <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                    <button type="button" class="btn-ver" id="togglePassword" <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="opciones-password">
                <label class="checkbox-recordar">
                    <input type="checkbox" name="recordar" <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                    <span>Recordarme</span>
                </label>
                <a href="/recuperar" class="link-olvido">¿Olvidó su contraseña?</a>
            </div>

            <button type="submit" class="btn-ingresar" id="btnIngresar" <?= ($bloqueado ?? false) ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                <i class="fas fa-lock"></i>
                <?php if ($bloqueado ?? false): ?>
                    Bloqueado — espera <span id="countdown"><?= $minutos ?>:00</span>
                <?php else: ?>
                    <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
                <?php endif; ?>
            </button>
        </form>


        <!-- Enlace de registro -->
        <div style="text-align: center; margin: 15px 0 5px 0;">
            <a href="/registro" class="registro-link">
                <i class="fas fa-user-plus"></i>
                ¿No tiene cuenta? Regístrese aquí
            </a>
        </div>

        <div class="nexgov-logo">
            <img src="/imagenes/NexGovIA.png" alt="NexGovIA">
        </div>

        <div class="footer-form">
            <p>
                <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()">NexGovIA S.A.S.®</button> · ☎ (+57) 310 631 02 27 · soportesgp@gmail.com
            </p>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         LAYOUT MÓVIL (visible solo en ≤600px)
    ══════════════════════════════════════════ -->
    <div class="mobile-login">

        <div class="m-header">
            <div class="m-logo-wrap">
                <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            </div>
            <div class="m-title">Alcaldía Municipal</div>
            <div class="m-subtitle">Monterrey · Casanare</div>
            <div class="m-badge">Sistema Activo 2026</div>
        </div>

        <div class="m-card">
            <div class="m-card-header">
                <div class="m-bienvenida">Bienvenido</div>
                <h2>Inicie sesión</h2>
                <p>Ingrese sus credenciales para acceder al sistema.</p>
            </div>

            <?php if ($error): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="formLoginMobile">
                <?= csrf_field() ?>
                <?= tab_id_field() ?>

                <div class="campo">
                    <label for="email_m">Correo electrónico</label>
                    <div class="campo-input">
                        <i class="fas fa-envelope icono-campo"></i>
                        <input type="email"
                               id="email_m"
                               name="email"
                               placeholder="correo@ejemplo.com"
                               required
                               autocomplete="username"
                               <?= ($bloqueado ?? false) ? 'disabled' : '' ?>
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="password_m">Contraseña</label>
                    <div class="campo-input">
                        <i class="fas fa-lock icono-campo"></i>
                        <input type="password"
                               id="password_m"
                               name="password"
                               placeholder="Ingrese su contraseña"
                               required
                               autocomplete="current-password"
                               <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                        <button type="button" class="btn-ver" id="togglePasswordMobile" <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="opciones-password">
                    <label class="checkbox-recordar">
                        <input type="checkbox" name="recordar" <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                        <span>Recordarme</span>
                    </label>
                    <a href="/recuperar" class="link-olvido">¿Olvidó su contraseña?</a>
                </div>

                <button type="submit" class="btn-ingresar" <?= ($bloqueado ?? false) ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                    <?php if ($bloqueado ?? false): ?>
                        <i class="fas fa-lock"></i>
                        Bloqueado — espera <span id="countdown_m"><?= $minutos ?>:00</span>
                    <?php else: ?>
                        <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
                    <?php endif; ?>
                </button>
            </form>

            <div class="m-footer">
                <a href="/registro" class="m-registro-link">
                    <i class="fas fa-user-plus"></i>
                    ¿No tiene cuenta? Regístrese aquí
                </a>

                <div class="m-nexgov-logo">
                    <img src="/imagenes/NexGovIA.png" alt="NexGovIA">
                </div>

                <p>
                    <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                    Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()">NexGovIA S.A.S.®</button><br>
                    ☎ (+57) 310 631 02 27
                </p>
            </div>
        </div>

    </div>

    <?php require_once BASE_PATH . '/views/components/modal_nexgovia.php'; ?>

    <script>
        // Toggle contraseña — desktop
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon  = this.querySelector('i');
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            icon.className = esPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        // Toggle contraseña — móvil
        document.getElementById('togglePasswordMobile').addEventListener('click', function () {
            const input = document.getElementById('password_m');
            const icon  = this.querySelector('i');
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            icon.className = esPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        <?php if ($bloqueado ?? false): ?>
        // Cuenta regresiva — ambos layouts
        (function(){
            var secs = <?= ($minutos ?? 0) * 60 ?>;
            var els  = [document.getElementById('countdown'), document.getElementById('countdown_m')];
            if (secs <= 0) return;
            var t = setInterval(function(){
                secs--;
                if (secs <= 0) { clearInterval(t); window.location.reload(); return; }
                var m = Math.floor(secs / 60), s = secs % 60;
                var txt = m + ':' + (s < 10 ? '0' : '') + s;
                els.forEach(function(el){ if (el) el.textContent = txt; });
            }, 1000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>