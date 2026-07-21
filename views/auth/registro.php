<?php
// Vista — la lógica está en AuthController::registro()
// Variables disponibles: $error, $success
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Registro de Ciudadano</title>
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
            gap: 48px;
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

        /* Pasos visuales */
        .pasos {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 25px;
        }

        .paso {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .paso-num {
            width: 24px; height: 24px;
            border-radius: 50%;
            background: rgba(201, 168, 76, 0.2);
            border: 1px solid rgba(201, 168, 76, 0.4);
            color: var(--dorado-claro);
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .paso-texto {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            line-height: 1.4;
            font-weight: 300;
        }

        .paso-texto strong {
            display: block;
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            font-size: 0.82rem;
            margin-bottom: 2px;
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
            justify-content: flex-start;
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
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .form-encabezado-texto .bienvenida {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--verde-claro);
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-bottom: 8px;
        }

        .form-encabezado-texto h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--gris-texto);
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .form-encabezado-texto p {
            color: var(--gris-sub);
            font-size: 0.87rem;
            font-weight: 300;
        }

        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--dorado-claro);
            text-decoration: none;
            font-size: 0.83rem;
            font-weight: 500;
            padding: 8px 16px;
            border: 1.5px solid var(--gris-borde);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            transition: all 0.2s;
            white-space: nowrap;
            margin-top: 4px;
        }

        .btn-volver:hover {
            border-color: var(--dorado);
            background: rgba(201, 168, 76, 0.1);
        }

        /* Secciones del form */
        .seccion {
            margin-bottom: 28px;
        }

        .seccion-titulo {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--dorado-claro);
            text-transform: uppercase;
            letter-spacing: 0.14em;
            padding-bottom: 10px;
            border-bottom: 1.5px solid var(--gris-borde);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .seccion-titulo i {
            font-size: 0.85rem;
            color: var(--dorado);
        }

        /* Grid de campos */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }

        .grid-3-1 {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 16px 20px;
        }

        .campo-completo { grid-column: 1 / -1; }

        .campo { margin-bottom: 0; }

        .campo label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--gris-texto);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .campo label .requerido {
            color: #e53e3e;
            margin-left: 2px;
        }

        .campo-input { position: relative; }

        .campo-input i.icono-campo {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.85rem;
            transition: color 0.25s;
            pointer-events: none;
        }

        .campo-input input,
        .campo-input select {
            width: 100%;
            padding: 11px 13px 11px 38px;
            background: rgba(0, 0, 0, 0.28);
            border: 1.5px solid var(--gris-borde);
            border-radius: 9px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--gris-texto);
            transition: all 0.25s;
            outline: none;
            appearance: none;
        }

        .campo-input select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23e8c97a' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 13px center;
            padding-right: 36px;
        }

        .campo-input select option { background: #fff; color: #1a1a1a; }

        .campo-input input::placeholder { color: rgba(255, 255, 255, 0.4); font-weight: 300; }

        .campo-input input:focus,
        .campo-input select:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.18);
            background: rgba(0, 0, 0, 0.38);
        }

        .campo-input:focus-within i.icono-campo { color: var(--dorado-claro); }

        .btn-ver-pass {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            padding: 4px;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .btn-ver-pass:hover { color: var(--dorado-claro); }

        /* Indicador fortaleza contraseña */
        .fortaleza-wrap {
            margin-top: 7px;
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

        /* Checkbox términos */
        .terminos {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 24px;
            margin-top: 4px;
        }

        .terminos input[type="checkbox"] {
            width: 17px; height: 17px;
            accent-color: var(--verde-institucional);
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .terminos span {
            font-size: 0.82rem;
            color: var(--gris-sub);
            line-height: 1.55;
        }

        .terminos a {
            color: var(--dorado-claro);
            text-decoration: none;
            font-weight: 500;
        }

        .terminos a:hover { color: var(--dorado); }

        /* Botón registrar */
        .btn-registrar {
            width: 100%;
            padding: 13px;
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
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }

        .btn-registrar::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }

        .btn-registrar:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
        }

        .btn-registrar:active { transform: translateY(0); }

        .footer-form {
            margin-top: 28px;
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
            .pasos { display: none; }
            .panel-derecho { width: 100%; padding: 40px 28px; }
            .grid-2, .grid-3-1 { grid-template-columns: 1fr; }
            .campo-completo { grid-column: 1; }
        }
        @media (max-width: 480px) and (min-width: 601px) {
            .panel-izquierdo { padding: 28px 18px; min-height: auto; }
            .panel-derecho { padding: 32px 18px; }
            .form-encabezado { flex-direction: column; gap: 14px; }
        }

        /* ══ LAYOUT MÓVIL registro ══ */
        .mobile-brand-bar {
            display: none;
            width: 100%;
            flex-direction: column;
            align-items: center;
            padding: 28px 24px 22px;
            text-align: center;
            gap: 5px;
            flex-shrink: 0;
        }
        .mobile-brand-bar::before {
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            position: absolute;
            top: 0; left: 0;
            background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
        }
        .m-reg-logo {
            width: 76px; height: 76px; border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 0 6px rgba(201,168,76,0.15), 0 8px 28px rgba(0,0,0,0.4);
            overflow: hidden; margin-bottom: 10px; background: #fff;
        }
        .m-reg-logo img { width: 100%; height: 100%; object-fit: cover; }
        .m-reg-title {
            color: #fff; font-size: 0.7rem; font-weight: 500;
            letter-spacing: 0.14em; text-transform: uppercase; opacity: 0.75;
        }
        .m-reg-sub {
            color: var(--dorado-claro); font-family: 'Playfair Display', serif;
            font-size: 1.05rem; font-weight: 700;
        }
        .m-reg-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.35);
            color: var(--dorado-claro); font-size: 0.63rem; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 20px; margin-top: 8px;
        }

        @media (max-width: 600px) {
            body {
                flex-direction: column;
                align-items: center;
                position: relative;
            }
            body::before {
                content: '';
                display: block;
                width: 100%;
                height: 3px;
                position: absolute;
                top: 0; left: 0;
                background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
            }
            .panel-izquierdo { display: none !important; }
            .mobile-brand-bar { display: flex !important; }
            .panel-derecho {
                width: calc(100% - 32px);
                max-width: 480px;
                min-height: auto;
                padding: 24px 20px 36px;
                border-radius: 20px;
                margin: 0 16px 40px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            }
            .panel-derecho::before { display: none; }
            .form-encabezado { flex-direction: column; gap: 12px; }
            .grid-2, .grid-3-1 { grid-template-columns: 1fr; }
            .campo-completo { grid-column: 1; }
            .seccion-titulo { font-size: 0.68rem; }
        }
    </style>
</head>
<body>

<!-- Barra de marca móvil (visible solo ≤600px) -->
<div class="mobile-brand-bar">
    <div class="m-reg-logo">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
    </div>
    <div class="m-reg-title">Alcaldía Municipal</div>
    <div class="m-reg-sub">Monterrey · Casanare</div>
    <div class="m-reg-badge">Registro Ciudadano</div>
</div>

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
        <div class="etiqueta-sistema">Registro Ciudadano</div>
        <h1 class="titulo-hero">
            Crea tu <span>cuenta</span><br>
            ciudadana
        </h1>
        <div class="separador-dorado"></div>
        <p class="desc-hero">
            Regístrese para acceder al portal de citas y trámites de la Alcaldía de Monterrey. El proceso toma menos de 2 minutos.
        </p>
    </div>

    <div class="pasos">
        <div class="paso">
            <div class="paso-num">1</div>
            <div class="paso-texto">
                <strong>Datos personales</strong>
                Nombres, apellidos e identificación
            </div>
        </div>
        <div class="paso">
            <div class="paso-num">2</div>
            <div class="paso-texto">
                <strong>Información de contacto</strong>
                Teléfono y correo electrónico
            </div>
        </div>
        <div class="paso">
            <div class="paso-num">3</div>
            <div class="paso-texto">
                <strong>Credenciales de acceso</strong>
                Cree una contraseña segura
            </div>
        </div>
    </div>
</div>

<!-- ── Panel derecho (formulario) ── -->
<div class="panel-derecho">
    <div class="form-encabezado">
        <div class="form-encabezado-texto">
            <div class="bienvenida">Portal Ciudadano</div>
            <h2>Crear cuenta</h2>
            <p>Complete el formulario. Todos los campos marcados con <span style="color:#e53e3e">*</span> son obligatorios.</p>
        </div>
        <a href="/login" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
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

    <form method="POST" action="" id="formRegistro">
        <?= csrf_field() ?>
            <?= tab_id_field() ?>
        <!-- Sección 1: Datos personales -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="fas fa-id-card"></i>
                Datos personales
            </div>

            <div class="grid-2">
                <div class="campo">
                    <label>Nombres <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-user icono-campo"></i>
                        <input type="text" name="nombres" placeholder="Ej: Carlos Andrés" required
                               value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
                    </div>
                </div>

                <div class="campo">
                    <label>Apellidos <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-user icono-campo"></i>
                        <input type="text" name="apellidos" placeholder="Ej: Gómez Rodríguez" required
                               value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                    </div>
                </div>

                <div class="campo">
                    <label>Tipo de identificación <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-id-badge icono-campo"></i>
                        <select name="tipo_identificacion" id="tipo_identificacion" required onchange="actualizarMaxlengthIdentificacion()">
                            <option value="" disabled <?php echo !isset($_POST['tipo_identificacion']) ? 'selected' : ''; ?>>Seleccione...</option>
                            <option value="CC" <?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'CC') ? 'selected' : ''; ?>>CC — Cédula de Ciudadanía</option>
                            <option value="TI" <?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'TI') ? 'selected' : ''; ?>>TI — Tarjeta de Identidad</option>
                            <option value="CE" <?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'CE') ? 'selected' : ''; ?>>CE — Cédula de Extranjería</option>
                            <option value="PA" <?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'PA') ? 'selected' : ''; ?>>PA — Pasaporte</option>
                            <option value="RC" <?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'RC') ? 'selected' : ''; ?>>RC — Registro Civil</option>
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label>Número de identificación <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-hashtag icono-campo"></i>
                        <input type="text" name="numero_identificacion" id="numero_identificacion"
                               placeholder="Ej: 1234567890" required
                               maxlength="<?php echo (isset($_POST['tipo_identificacion']) && $_POST['tipo_identificacion'] === 'PA') ? 20 : 10; ?>"
                               value="<?php echo isset($_POST['numero_identificacion']) ? htmlspecialchars($_POST['numero_identificacion']) : ''; ?>">
                    </div>
                    <div id="validacion-identificacion" style="font-size: 0.8rem; margin-top: 5px; min-height: 20px;"></div>
                </div>
            </div>
        </div>

        <!-- Sección 2: Contacto -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="fas fa-address-book"></i>
                Información de contacto
            </div>

            <div class="grid-2">
                <div class="campo">
                    <label>Teléfono <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-phone icono-campo"></i>
                        <input type="tel" name="telefono" placeholder="Ej: 3001234567" required
                               maxlength="15"
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                    </div>
                </div>

                <div class="campo">
                    <label>Correo electrónico <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-envelope icono-campo"></i>
                        <input type="email" name="email" placeholder="correo@ejemplo.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 3: Información adicional (opcional) -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="fas fa-map-marker-alt"></i>
                Información adicional <span style="font-weight: 400; font-size: 0.85em; color: var(--gris-sub);">(opcional)</span>
            </div>

            <div class="grid-2">
                <div class="campo">
                    <label>WhatsApp</label>
                    <div class="campo-input">
                        <i class="fab fa-whatsapp icono-campo"></i>
                        <input type="tel" name="whatsapp" placeholder="Ej: 3001234567"
                               maxlength="20"
                               value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>">
                    </div>
                </div>

                <div class="campo">
                    <label>Proveniencia</label>
                    <div class="campo-input">
                        <i class="fas fa-city icono-campo"></i>
                        <input type="text" name="proveniencia" placeholder="Ej: Bogotá, Yopal, Villavicencio..."
                               maxlength="150"
                               value="<?php echo isset($_POST['proveniencia']) ? htmlspecialchars($_POST['proveniencia']) : ''; ?>">
                    </div>
                </div>

                <div class="campo campo-completo">
                    <label>Dirección</label>
                    <div class="campo-input">
                        <i class="fas fa-home icono-campo"></i>
                        <input type="text" name="direccion" placeholder="Ej: Calle 5 # 10-20, Barrio Centro"
                               maxlength="250"
                               value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 4: Contraseña -->
        <div class="seccion">
            <div class="seccion-titulo">
                <i class="fas fa-lock"></i>
                Credenciales de acceso
            </div>

            <div class="grid-2">
                <div class="campo">
                    <label>Contraseña <span class="requerido">*</span></label>
                    <div class="campo-input">
                        <i class="fas fa-lock icono-campo"></i>
                        <input type="password" name="password" id="password"
                               placeholder="Mínimo 8 caracteres" required minlength="8">
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
                    <div id="matchMsg" style="font-size:0.74rem; margin-top:6px; display:none;"></div>
                </div>
            </div>
        </div>

        <!-- Términos -->
        <label class="terminos">
            <input type="checkbox" name="terminos" required>
            <span>
                Acepto los <a href="#">términos y condiciones</a> y la
                <a href="#">política de privacidad</a> de la Alcaldía Municipal de Monterrey para el tratamiento de mis datos personales conforme a la Ley 1581 de 2012.
            </span>
        </label>

        <button type="submit" class="btn-registrar">
            <i class="fas fa-user-check"></i>
            Crear mi cuenta ciudadana
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

<script>
    // Ajusta el largo máximo permitido según el tipo de identificación
    // (PA/Pasaporte puede ser alfanumérico y más largo; los demás máx. 10)
    function actualizarMaxlengthIdentificacion() {
        const tipo  = document.getElementById('tipo_identificacion').value;
        const input = document.getElementById('numero_identificacion');
        input.maxLength = (tipo === 'PA') ? 20 : 10;
    }
    actualizarMaxlengthIdentificacion();

    // Toggle mostrar/ocultar contraseña
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const icon  = btn.querySelector('i');
        const es = input.type === 'password';
        input.type = es ? 'text' : 'password';
        icon.className = es ? 'fas fa-eye-slash' : 'fas fa-eye';
    }

    // Indicador de fortaleza
    const passInput = document.getElementById('password');
    passInput.addEventListener('input', function () {
        const val = this.value;
        const wrap = document.getElementById('fortalezaWrap');
        const txt  = document.getElementById('fortalezaTxt');
        const barras = [document.getElementById('b1'), document.getElementById('b2'),
                        document.getElementById('b3'), document.getElementById('b4')];

        if (!val) { wrap.classList.remove('visible'); return; }
        wrap.classList.add('visible');

        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colores = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
        const textos  = ['Muy débil', 'Débil', 'Moderada', 'Fuerte'];

        barras.forEach((b, i) => {
            b.style.background = i < score ? colores[score - 1] : 'var(--gris-borde)';
        });
        txt.textContent  = 'Fortaleza: ' + textos[score - 1];
        txt.style.color  = colores[score - 1];
    });

    // Validación de identificación en tiempo real
    const identificacionInput = document.getElementById('numero_identificacion');
    const tipoIdentificacionSelect = document.getElementById('tipo_identificacion');
    const validacionDiv = document.getElementById('validacion-identificacion');
    let timeoutId;

    function dispararValidacionIdentificacion() {
        const numero = identificacionInput.value.trim();
        const tipo   = tipoIdentificacionSelect.value;

        clearTimeout(timeoutId);

        if (numero.length === 0 || !tipo) {
            validacionDiv.innerHTML = '';
            validacionDiv.style.color = '';
            return;
        }

        validacionDiv.innerHTML = 'Verificando...';
        validacionDiv.style.color = '#6b6b6b';

        timeoutId = setTimeout(function() {
            const params = new URLSearchParams({
                numero_identificacion: numero,
                tipo_identificacion: tipo,
            });
            fetch('/ajax/verificar_identificacion?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            validacionDiv.innerHTML = '❌ Error al verificar';
                            validacionDiv.style.color = '#e53e3e';
                            return;
                        }

                        if (data.estado === 'con_cuenta') {
                            validacionDiv.innerHTML = data.mensaje;
                            validacionDiv.style.color = '#e53e3e';
                            document.querySelector('button[type="submit"]').disabled = true;
                        } else {
                            // sin_cuenta o nuevo — no mostrar nada, permitir registro
                            validacionDiv.innerHTML = '';
                            document.querySelector('button[type="submit"]').disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        validacionDiv.innerHTML = '❌ Error de conexión';
                        validacionDiv.style.color = '#e53e3e';
                    });
            }, 300);
    }

    if (identificacionInput) {
        identificacionInput.addEventListener('input', dispararValidacionIdentificacion);
        tipoIdentificacionSelect.addEventListener('change', dispararValidacionIdentificacion);
    }

    // Validación coincidencia contraseñas
    document.getElementById('password_confirm').addEventListener('input', function () {
        const msg  = document.getElementById('matchMsg');
        const pass = document.getElementById('password').value;
        msg.style.display = 'block';
        if (this.value === pass) {
            msg.textContent = '✓ Las contraseñas coinciden';
            msg.style.color = '#22c55e';
        } else {
            msg.textContent = '✗ Las contraseñas no coinciden';
            msg.style.color = '#ef4444';
        }
    });

    // Validación antes de enviar
    document.getElementById('formRegistro').addEventListener('submit', function (e) {
        const pass    = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;
        if (pass !== confirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden. Por favor verifique.');
        }
    });
</script>
</body>
</html>