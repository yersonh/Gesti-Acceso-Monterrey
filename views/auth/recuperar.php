<?php
// Vista — la lógica está en AuthController::recuperar()
// Variables disponibles: $error, $success
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Recuperar Contraseña</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
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
            margin-bottom: 24px;
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
            font-family: 'Outfit', sans-serif;
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

        /* Información adicional */
        .info-recuperacion {
            background: rgba(201, 168, 76, 0.08);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 28px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px dashed var(--dorado);
        }

        .info-recuperacion i {
            color: var(--dorado);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-recuperacion span {
            color: var(--gris-sub);
            font-size: 0.85rem;
            line-height: 1.5;
        }

        /* Botón enviar */
        .btn-enviar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            border: none;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
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
        }
        @media (max-width: 480px) and (min-width: 601px) {
            .enlaces { flex-direction: column; gap: 12px; align-items: center; }
        }

        /* ── Ocultar layout desktop en móvil ── */
        @media (max-width: 600px) {
            .panel-izquierdo, .panel-derecho { display: none !important; }
            .mobile-rec { display: flex !important; }
        }

        /* ══ LAYOUT MÓVIL recuperar ══ */
        .mobile-rec {
            display: none;
            min-height: 100vh;
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0 0 40px;
            overflow-y: auto;
        }
        .mobile-rec::before {
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            flex-shrink: 0;
            background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
        }
        .mobile-rec .m-header {
            display: flex; flex-direction: column; align-items: center;
            padding: 28px 24px 20px; text-align: center; width: 100%;
        }
        .mobile-rec .m-logo-wrap {
            width: 78px; height: 78px; border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 0 6px rgba(201,168,76,0.15), 0 8px 28px rgba(0,0,0,0.4);
            overflow: hidden; margin-bottom: 14px; background: #fff;
        }
        .mobile-rec .m-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .mobile-rec .m-title {
            color: var(--blanco); font-size: 0.72rem; font-weight: 500;
            letter-spacing: 0.14em; text-transform: uppercase; opacity: 0.75; margin-bottom: 3px;
        }
        .mobile-rec .m-subtitle {
            color: var(--dorado-claro); font-family: 'Playfair Display', serif;
            font-size: 1.1rem; font-weight: 700;
        }
        .mobile-rec .m-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.35);
            color: var(--dorado-claro); font-size: 0.65rem; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 20px; margin-top: 10px;
        }
        .mobile-rec .m-card {
            width: calc(100% - 32px); max-width: 420px;
            background: var(--crema);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,168,76,0.08);
            padding: 28px 22px 24px; margin: 0 16px;
            animation: recCardEntrar 0.45s ease both 0.1s;
        }
        @keyframes recCardEntrar {
            from { opacity:0; transform:translateY(24px) scale(0.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        .mobile-rec .m-bienvenida {
            font-size: 0.7rem; font-weight: 700; color: var(--verde-claro);
            letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 6px;
        }
        .mobile-rec .m-card h2 {
            font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700;
            color: var(--gris-texto); line-height: 1.15; margin-bottom: 5px;
        }
        .mobile-rec .m-card > p {
            color: var(--gris-sub); font-size: 0.82rem; font-weight: 300;
            line-height: 1.55; margin-bottom: 20px;
        }
        .mobile-rec .m-footer { margin-top: 18px; text-align: center; }
        .mobile-rec .m-footer .enlaces {
            display: flex; flex-direction: column; gap: 10px;
            align-items: center; margin-bottom: 16px;
        }
        .mobile-rec .m-footer .enlaces a {
            color: var(--dorado-claro); text-decoration: none; font-size: 0.82rem;
            font-weight: 500; display: inline-flex; align-items: center; gap: 5px;
        }
        .mobile-rec .m-footer p {
            font-size: 0.68rem; color: rgba(255, 255, 255, 0.5); line-height: 1.65;
        }
        .mobile-rec .m-footer strong { color: rgba(255, 255, 255, 0.75); font-weight: 600; }
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
        <div class="etiqueta-sistema">Recuperación de Acceso</div>
        <h1 class="titulo-hero">
            ¿Olvidaste tu<br>
            <span>contraseña?</span>
        </h1>
        <div class="separador-dorado"></div>
        <p class="desc-hero">
            No te preocupes, te enviaremos las instrucciones para restablecer tu contraseña y recuperar el acceso a tu cuenta.
        </p>
        
        <div class="mensaje-ayuda">
            <i class="fas fa-shield-alt"></i>
            Tu seguridad es importante. Solo usaremos esta información para verificar tu identidad.
        </div>
    </div>
</div>

<!-- ── Panel derecho (formulario) ── -->
<div class="panel-derecho">
    <div class="form-encabezado">
        <div class="bienvenida">Recuperar Contraseña</div>
        <h2>Restablecer acceso</h2>
        <p>Ingresa el correo electrónico con el que te registraste. Recibirás un enlace para crear una nueva contraseña.</p>
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

    <div class="info-recuperacion">
        <i class="fas fa-info-circle"></i>
        <span>Te enviaremos un correo con un enlace válido por 24 horas. Revisa también tu carpeta de spam.</span>
    </div>

    <form method="POST" action="" id="formRecuperar">
        <?= csrf_field() ?>
            <?= tab_id_field() ?>
        <div class="campo">
            <label>Correo electrónico <span class="requerido">*</span></label>
            <div class="campo-input">
                <i class="fas fa-envelope icono-campo"></i>
                <input type="email" name="email" placeholder="correo@ejemplo.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>

        <button type="submit" class="btn-enviar">
            <i class="fas fa-paper-plane"></i>
            Enviar instrucciones
        </button>
    </form>

    <div class="enlaces">
        <a href="/login">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
        <a href="/registro">
            <i class="fas fa-user-plus"></i> Crear una cuenta
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

<!-- ══ LAYOUT MÓVIL recuperar (visible solo ≤600px) ══ -->
<div class="mobile-rec">
    <div class="m-header">
        <div class="m-logo-wrap">
            <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        </div>
        <div class="m-title">Alcaldía Municipal</div>
        <div class="m-subtitle">Monterrey · Casanare</div>
        <div class="m-badge">Recuperar Acceso</div>
    </div>

    <div class="m-card">
        <div class="m-bienvenida">Recuperar Contraseña</div>
        <h2>Restablecer acceso</h2>
        <p>Ingresa el correo con el que te registraste. Recibirás un enlace para crear una nueva contraseña.</p>

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

        <div class="info-recuperacion" style="margin-bottom:20px;">
            <i class="fas fa-info-circle"></i>
            <span>Te enviaremos un correo válido por 24 horas. Revisa también tu carpeta de spam.</span>
        </div>

        <form method="POST" action="" id="formRecuperarMobile">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <div class="campo" style="margin-bottom:22px;">
                <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--gris-texto);letter-spacing:0.04em;text-transform:uppercase;margin-bottom:8px;">
                    Correo electrónico <span style="color:#e53e3e">*</span>
                </label>
                <div class="campo-input">
                    <i class="fas fa-envelope icono-campo"></i>
                    <input type="email" name="email" placeholder="correo@ejemplo.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            <button type="submit" class="btn-enviar">
                <i class="fas fa-paper-plane"></i> Enviar instrucciones
            </button>
        </form>

        <div class="m-footer">
            <div class="enlaces">
                <a href="/login"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a>
                <a href="/registro"><i class="fas fa-user-plus"></i> Crear una cuenta</a>
            </div>
            <p>
                    <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                    Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()">NexGovIA S.A.S.®</button><br>
                    ☎ (+57) 310 631 02 27
            </p>
        </div>
    </div>
</div>

<script>
    // Validación básica del formulario
    ['formRecuperar', 'formRecuperarMobile'].forEach(function(id) {
        const f = document.getElementById(id);
        if (f) f.addEventListener('submit', function (e) {
            const email = this.querySelector('input[name="email"]').value.trim();
            if (!email) {
                e.preventDefault();
                alert('Por favor ingresa tu correo electrónico');
            }
        });
    });
</script>
</body>
</html>