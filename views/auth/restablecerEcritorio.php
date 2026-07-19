<?php
// Vista — la lógica está en AuthController::restablecerEscritorio()
// Variables disponibles: $error, $success, $token_valido, $token_data
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Restablecer Contraseña</title>
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
            --crema: rgba(20, 34, 26, 0.45);
            --blanco: #ffffff;
            --gris-texto: #eef3ee;
            --gris-sub: rgba(255, 255, 255, 0.65);
            --gris-borde: rgba(255, 255, 255, 0.14);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            overflow: auto;
            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.6) 0%, rgba(6, 14, 10, 0.78) 55%, rgba(6, 14, 10, 0.9) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* ── Panel izquierdo ── */
        .panel-izquierdo {
            width: 38%;
            position: relative;
            background: rgba(20, 34, 26, 0.45);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 52px 48px 40px 48px;
            overflow: hidden;
            min-height: 100vh;
        }

        .panel-izquierdo::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(201, 168, 76, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(61, 158, 104, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

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
            margin-bottom: 10px;
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
            margin-bottom: 20px;
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
            margin-bottom: 15px;
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
            margin: 15px 0;
        }

        /* Mensaje de ayuda */
        .mensaje-ayuda {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            line-height: 1.6;
            font-weight: 300;
            max-width: 380px;
            margin-top: 15px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            border-left: 3px solid var(--dorado);
        }

        .mensaje-ayuda i {
            color: var(--dorado-claro);
            margin-right: 8px;
        }

        /* ── Panel derecho ── */
        .panel-derecho {
            width: 62%;
            background: var(--crema);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 60px;
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

        /* Estilos para mensajes de éxito/error */
        .mensaje-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-left: 3px solid #ef4444;
            color: #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .mensaje-exito {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.4);
            border-left: 3px solid #22c55e;
            color: #bbf7d0;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        /* Token inválido */
        .token-invalido {
            text-align: center;
            padding: 40px 20px;
        }

        .token-invalido i {
            font-size: 4rem;
            color: #f87171;
            margin-bottom: 20px;
        }

        .token-invalido h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--gris-texto);
            margin-bottom: 15px;
        }

        .token-invalido p {
            color: var(--gris-sub);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
        }

        /* Encabezado del formulario */
        .form-encabezado {
            margin-bottom: 32px;
        }

        .form-encabezado .bienvenida {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--verde-claro);
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-bottom: 8px;
        }

        .form-encabezado h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--gris-texto);
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .form-encabezado p {
            color: var(--gris-sub);
            font-size: 0.9rem;
            font-weight: 300;
            line-height: 1.6;
            max-width: 500px;
        }

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

        .campo label .requerido {
            color: #e53e3e;
            margin-left: 2px;
        }

        .campo-input {
            position: relative;
        }

        .campo-input i.icono-campo {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.95rem;
            transition: color 0.25s;
            pointer-events: none;
        }

        .campo-input input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            background: rgba(0, 0, 0, 0.28);
            border: 1.5px solid var(--gris-borde);
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--gris-texto);
            transition: all 0.25s;
            outline: none;
        }

        .campo-input input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-weight: 300;
        }

        .campo-input input:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.18);
            background: rgba(0, 0, 0, 0.38);
        }

        .campo-input:focus-within i.icono-campo {
            color: var(--dorado-claro);
        }

        .btn-ver-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            padding: 4px;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .btn-ver-pass:hover {
            color: var(--dorado-claro);
        }

        /* Indicador fortaleza contraseña */
        .fortaleza-wrap {
            margin-top: 8px;
            display: none;
        }

        .fortaleza-wrap.visible { display: block; }

        .fortaleza-barras {
            display: flex;
            gap: 4px;
            margin-bottom: 4px;
        }

        .fortaleza-barra {
            height: 3px;
            flex: 1;
            border-radius: 2px;
            background: var(--gris-borde);
            transition: background 0.3s;
        }

        .fortaleza-texto {
            font-size: 0.74rem;
            color: var(--gris-sub);
            font-weight: 500;
        }

        /* Requisitos contraseña */
        .requisitos {
            background: rgba(201, 168, 76, 0.08);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            border: 1px solid var(--gris-borde);
        }

        .requisitos p {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gris-texto);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .requisitos p i {
            color: var(--dorado);
            font-size: 0.75rem;
        }

        .requisitos ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .requisitos li {
            font-size: 0.78rem;
            color: var(--gris-sub);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requisitos li i {
            font-size: 0.7rem;
            width: 16px;
        }

        .requisitos li.valido i {
            color: #22c55e;
        }

        .requisitos li.invalido i {
            color: #f87171;
        }

        /* Botón enviar */
        .btn-enviar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }

        .btn-enviar::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }

        .btn-enviar:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
        }

        .btn-enviar:active { transform: translateY(0); }

        /* Enlaces */
        .enlaces {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 16px;
        }

        .enlaces a {
            color: var(--dorado-claro);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .enlaces a:hover {
            color: var(--dorado);
        }

        .enlaces a i {
            font-size: 0.75rem;
        }

        /* Footer */
        .footer-form {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--gris-borde);
            text-align: center;
        }

        .footer-form p {
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.7;
        }

        .footer-form strong { color: rgba(255, 255, 255, 0.75); font-weight: 600; }

        /* Personalización del scroll */
        body::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.06);
        }

        body::-webkit-scrollbar-thumb {
            background: var(--dorado);
            border-radius: 4px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: var(--dorado-claro);
        }

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

        .panel-derecho::-webkit-scrollbar {
            width: 6px;
        }

        .panel-derecho::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.06);
        }

        .panel-derecho::-webkit-scrollbar-thumb {
            background: var(--dorado);
            border-radius: 3px;
        }

        .panel-derecho::-webkit-scrollbar-thumb:hover {
            background: var(--dorado-claro);
        }

        /* Animación entrada */
        .panel-derecho > * {
            animation: subirEntrar 0.45s ease both;
        }
        .panel-derecho > *:nth-child(1) { animation-delay: 0.05s; }
        .panel-derecho > *:nth-child(2) { animation-delay: 0.12s; }
        .panel-derecho > *:nth-child(3) { animation-delay: 0.18s; }

        @keyframes subirEntrar {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsivo tablet ── */
        @media (max-width: 1024px) and (min-width: 601px) {
            .panel-derecho { padding: 44px 40px; }
        }
        @media (max-width: 860px) and (min-width: 601px) {
            body { flex-direction: column; overflow: auto; }
            .panel-izquierdo { width: 100%; min-height: auto; padding: 36px 28px; }
            .panel-derecho { width: 100%; padding: 40px 28px; }
            .enlaces { flex-direction: column; gap: 12px; align-items: center; }
        }
        @media (max-width: 480px) and (min-width: 601px) {
            .panel-izquierdo { padding: 28px 18px; min-height: auto; }
            .panel-derecho { padding: 32px 18px; }
        }

        /* ══ LAYOUT MÓVIL ══ */
        .mobile-brand-bar {
            display: none; width: 100%; flex-direction: column;
            align-items: center; padding: 28px 24px 22px;
            text-align: center; gap: 5px; flex-shrink: 0;
        }
        .m-logo-m { width: 74px; height: 74px; border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 0 6px rgba(201,168,76,0.15), 0 8px 28px rgba(0,0,0,0.4);
            overflow: hidden; margin-bottom: 10px; background: #fff; }
        .m-logo-m img { width: 100%; height: 100%; object-fit: cover; }
        .m-title-m { color: #fff; font-size: 0.7rem; font-weight: 500;
            letter-spacing: 0.14em; text-transform: uppercase; opacity: 0.75; }
        .m-sub-m { color: var(--dorado-claro); font-family: 'Playfair Display', serif;
            font-size: 1.05rem; font-weight: 700; }
        .m-badge-m { display: inline-flex; align-items: center; gap: 6px;
            background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.35);
            color: var(--dorado-claro); font-size: 0.63rem; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 20px; margin-top: 8px; }

        @media (max-width: 600px) {
            body {
                flex-direction: column;
                align-items: center;
            }
            body::before {
                content: '';
                display: block; width: 100%; height: 3px;
                position: absolute; top: 0; left: 0;
                background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
            }
            .panel-izquierdo { display: none !important; }
            .mobile-brand-bar { display: flex !important; }
            .panel-derecho {
                width: calc(100% - 32px); max-width: 460px;
                min-height: auto; padding: 24px 20px 36px;
                border-radius: 20px; margin: 0 16px 40px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            }
            .panel-derecho::before { display: none; }
            .enlaces { flex-direction: column; gap: 10px; align-items: center; }
        }
    </style>
</head>
<body>

<?php if (!$token_valido): ?>
<!-- ── Token Inválido ── -->
<div class="mobile-brand-bar">
    <div class="m-logo-m"><img src="/imagenes/logoalcaldia.jpg" alt="Logo"></div>
    <div class="m-title-m">Alcaldía Municipal</div>
    <div class="m-sub-m">Monterrey · Casanare</div>
    <div class="m-badge-m">Restablecer Contraseña</div>
</div>
<div class="panel-izquierdo">
    <div class="brand-top">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="brand-nombre">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </div>

    <div class="contenido-central">
        <div class="etiqueta-sistema">Error de Seguridad</div>
        <h1 class="titulo-hero">
            Enlace no<br>
            <span>válido</span>
        </h1>
        <div class="separador-dorado"></div>
        <p class="desc-hero">
            <?php echo $error ?: "El enlace para restablecer tu contraseña ha expirado o no es válido. Solicita uno nuevo para continuar."; ?>
        </p>
    </div>
</div>

<div class="panel-derecho">
    <div class="token-invalido">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Enlace expirado</h3>
        <p><?php echo $error ?: "Lo sentimos, el enlace para restablecer tu contraseña ya no es válido. Los enlaces tienen una validez de 24 horas por seguridad."; ?></p>
        <a href="/recuperar" class="btn-primary">
            <i class="fas fa-redo-alt"></i>
            Solicitar nuevo enlace
        </a>
    </div>
    <div class="footer-form">
        <p>
                    <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                    Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()">NexGovIA S.A.S.®</button><br>
                    ☎ (+57) 310 631 02 27
                </p>
    </div>
</div>

<?php else: ?>
<!-- ── Token Válido ── -->
<div class="mobile-brand-bar">
    <div class="m-logo-m"><img src="/imagenes/logoalcaldia.jpg" alt="Logo"></div>
    <div class="m-title-m">Alcaldía Municipal</div>
    <div class="m-sub-m">Monterrey · Casanare</div>
    <div class="m-badge-m">Nueva Contraseña</div>
</div>
<div class="panel-izquierdo">
    <div class="brand-top">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="brand-nombre">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </div>

    <div class="contenido-central">
        <div class="etiqueta-sistema">Restablecer Contraseña</div>
        <h1 class="titulo-hero">
            Crea una<br>
            <span>nueva</span><br>
            contraseña
        </h1>
        <div class="separador-dorado"></div>
        <p class="desc-hero">
            Elige una contraseña segura que no hayas utilizado anteriormente. Debe tener al menos 8 caracteres.
        </p>
        
        <div class="mensaje-ayuda">
            <i class="fas fa-lock"></i>
            Tu seguridad es importante. Usa una combinación de letras, números y símbolos.
        </div>
    </div>
</div>

<!-- ── Panel derecho (formulario) ── -->
<div class="panel-derecho">
    <div class="form-encabezado">
        <div class="bienvenida">Nueva Contraseña</div>
        <h2>Restablecer contraseña</h2>
        <p>Ingresa y confirma tu nueva contraseña para acceder al sistema.</p>
    </div>

    <?php if ($error): ?>
    <div class="mensaje-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="mensaje-exito">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="requisitos">
        <p><i class="fas fa-shield-alt"></i> Requisitos de seguridad:</p>
        <ul id="requisitos-lista">
            <li id="req-longitud" class="invalido"><i class="fas fa-circle"></i> Mínimo 8 caracteres</li>
            <li id="req-mayuscula" class="invalido"><i class="fas fa-circle"></i> Al menos una mayúscula</li>
            <li id="req-numero" class="invalido"><i class="fas fa-circle"></i> Al menos un número</li>
            <li id="req-especial" class="invalido"><i class="fas fa-circle"></i> Al menos un carácter especial</li>
        </ul>
    </div>

    <form method="POST" action="?token=<?php echo htmlspecialchars($_GET['token']); ?>" id="formRestablecer">
        <?= csrf_field() ?>
            <?= tab_id_field() ?>
        <div class="campo">
            <label>Nueva contraseña <span class="requerido">*</span></label>
            <div class="campo-input">
                <i class="fas fa-lock icono-campo"></i>
                <input type="password" name="password" id="password" 
                       placeholder="Ingrese su nueva contraseña" required minlength="8">
                <button type="button" class="btn-ver-pass" onclick="togglePass('password', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="fortaleza-wrap" id="fortalezaWrap">
                <div class="fortaleza-barras">
                    <div class="fortaleza-barra" id="b1"></div>
                    <div class="fortaleza-barra" id="b2"></div>
                    <div class="fortaleza-barra" id="b3"></div>
                    <div class="fortaleza-barra" id="b4"></div>
                </div>
                <span class="fortaleza-texto" id="fortalezaTxt"></span>
            </div>
        </div>

        <div class="campo">
            <label>Confirmar contraseña <span class="requerido">*</span></label>
            <div class="campo-input">
                <i class="fas fa-lock icono-campo"></i>
                <input type="password" name="password_confirm" id="password_confirm" 
                       placeholder="Repita su contraseña" required minlength="8">
                <button type="button" class="btn-ver-pass" onclick="togglePass('password_confirm', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div id="matchMsg" style="font-size:0.8rem; margin-top:6px; display:none;"></div>
        </div>

        <button type="submit" class="btn-enviar" id="btnSubmit">
            <i class="fas fa-save"></i>
            Guardar nueva contraseña
        </button>
    </form>

    <div class="footer-form">
        <p>
                    <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                    Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()">NexGovIA S.A.S.®</button><br>
                    ☎ (+57) 310 631 02 27
                </p>
    </div>
</div>
<?php endif; ?>

<script>
    // Toggle mostrar/ocultar contraseña
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const icon  = btn.querySelector('i');
        const es = input.type === 'password';
        input.type = es ? 'text' : 'password';
        icon.className = es ? 'fas fa-eye-slash' : 'fas fa-eye';
    }

    <?php if ($token_valido): ?>
    // Indicador de fortaleza y validación de requisitos
    const passInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const fortalezaWrap = document.getElementById('fortalezaWrap');
    const fortalezaTxt = document.getElementById('fortalezaTxt');
    const barras = ['b1', 'b2', 'b3', 'b4'].map(id => document.getElementById(id));
    const matchMsg = document.getElementById('matchMsg');
    const btnSubmit = document.getElementById('btnSubmit');

    // Elementos de requisitos
    const reqLongitud = document.getElementById('req-longitud');
    const reqMayuscula = document.getElementById('req-mayuscula');
    const reqNumero = document.getElementById('req-numero');
    const reqEspecial = document.getElementById('req-especial');

    function validarRequisitos(password) {
        // Validar longitud
        if (password.length >= 8) {
            reqLongitud.className = 'valido';
            reqLongitud.innerHTML = '<i class="fas fa-check-circle"></i> Mínimo 8 caracteres';
        } else {
            reqLongitud.className = 'invalido';
            reqLongitud.innerHTML = '<i class="fas fa-circle"></i> Mínimo 8 caracteres';
        }

        // Validar mayúscula
        if (/[A-Z]/.test(password)) {
            reqMayuscula.className = 'valido';
            reqMayuscula.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una mayúscula';
        } else {
            reqMayuscula.className = 'invalido';
            reqMayuscula.innerHTML = '<i class="fas fa-circle"></i> Al menos una mayúscula';
        }

        // Validar número
        if (/[0-9]/.test(password)) {
            reqNumero.className = 'valido';
            reqNumero.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un número';
        } else {
            reqNumero.className = 'invalido';
            reqNumero.innerHTML = '<i class="fas fa-circle"></i> Al menos un número';
        }

        // Validar carácter especial
        if (/[^A-Za-z0-9]/.test(password)) {
            reqEspecial.className = 'valido';
            reqEspecial.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un carácter especial';
        } else {
            reqEspecial.className = 'invalido';
            reqEspecial.innerHTML = '<i class="fas fa-circle"></i> Al menos un carácter especial';
        }
    }

    passInput.addEventListener('input', function() {
        const val = this.value;
        
        // Validar requisitos
        validarRequisitos(val);
        
        // Mostrar/ocultar fortaleza
        if (!val) { 
            fortalezaWrap.classList.remove('visible'); 
            return; 
        }
        fortalezaWrap.classList.add('visible');

        // Calcular fortaleza
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colores = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
        const textos = ['Muy débil', 'Débil', 'Moderada', 'Fuerte'];

        barras.forEach((b, i) => {
            b.style.background = i < score ? colores[score - 1] : 'var(--gris-borde)';
        });
        fortalezaTxt.textContent = 'Fortaleza: ' + textos[score - 1];
        fortalezaTxt.style.color = colores[score - 1];
    });

    // Validar coincidencia de contraseñas
    function validarCoincidencia() {
        const pass = passInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            matchMsg.style.display = 'none';
            return false;
        }
        
        matchMsg.style.display = 'block';
        if (pass === confirm) {
            matchMsg.innerHTML = '✓ Las contraseñas coinciden';
            matchMsg.style.color = '#22c55e';
            return true;
        } else {
            matchMsg.innerHTML = '✗ Las contraseñas no coinciden';
            matchMsg.style.color = '#ef4444';
            return false;
        }
    }

    confirmInput.addEventListener('input', validarCoincidencia);

    // Validar antes de enviar
    document.getElementById('formRestablecer').addEventListener('submit', function(e) {
        const pass = passInput.value;
        const confirm = confirmInput.value;
        
        // Validar que todos los requisitos se cumplan
        const requisitosValidos = 
            pass.length >= 8 &&
            /[A-Z]/.test(pass) &&
            /[0-9]/.test(pass) &&
            /[^A-Za-z0-9]/.test(pass);
        
        if (!requisitosValidos) {
            e.preventDefault();
            alert('La contraseña no cumple con todos los requisitos de seguridad.');
            return;
        }
        
        if (pass !== confirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden. Por favor verifique.');
        }
    });
    <?php endif; ?>
</script>
</body>
</html>