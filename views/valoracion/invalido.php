<?php
// views/valoracion/invalido.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace inválido — Alcaldía de Monterrey</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --verde:        #1a5c38;
            --verde-claro:  #3d9e68;
            --dorado:       #c9a84c;
            --dorado-claro: #e8c97a;
            --rojo:         #ef4444;
            --blanco:       #ffffff;
            --texto:        #eef3ee;
            --texto-sub:    rgba(255, 255, 255, 0.6);
            --borde:        rgba(255, 255, 255, 0.14);
            --fondo:        rgba(0, 0, 0, 0.28);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;

            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.6) 0%, rgba(6, 14, 10, 0.78) 55%, rgba(6, 14, 10, 0.9) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .card {
            background: rgba(20, 34, 26, 0.45);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            padding: 48px 36px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(201, 168, 76, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
        }
        .icono {
            width: 80px; height: 80px;
            background: rgba(239, 68, 68, 0.12);
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.2rem;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            color: var(--blanco);
            margin-bottom: 12px;
        }
        .mensaje {
            color: var(--texto-sub);
            font-size: 0.95rem;
            font-weight: 300;
            line-height: 1.7;
        }
        .footer-card {
            margin-top: 36px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icono">⚠️</div>
    <h1>Enlace no válido</h1>
    <p class="mensaje">
        Este enlace de valoración ya fue utilizado, ha expirado o no existe.<br><br>
        Si tienes alguna duda, comunícate con la Alcaldía Municipal de Monterrey.
    </p>
</div>

<div class="footer-card">
    © <?= date('Y') ?> Sistema de Control de Visitas — Alcaldía Municipal de Monterrey, Casanare
</div>

</body>
</html>