<?php
// La sesión ya fue parcialmente cerrada por AuthController::logout()
// mediante auth_clear(), que solo elimina el slot de esta pestaña.
// NO llamar session_destroy() aquí — destruiría las sesiones de otras pestañas.
$redirectUrl = '/login?tab_id=' . htmlspecialchars(auth_tab_id());
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Cerrando sesión</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.6) 0%, rgba(6, 14, 10, 0.78) 55%, rgba(6, 14, 10, 0.9) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease;
        }

        .card {
            background: rgba(20, 34, 26, 0.45);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border-radius: 22px;
            padding: 48px 32px;
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(201, 168, 76, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.14);
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

        /* Logo o ícono */
        .logo-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--verde), var(--verde-medio));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 0 6px rgba(201, 168, 76, 0.14);
            position: relative;
            animation: pulse 2s infinite;
        }

        .logo-wrapper i {
            font-size: 48px;
            color: white;
        }

        .logo-wrapper::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: ripple 2s linear infinite;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #eef3ee;
            margin-bottom: 12px;
        }

        h1 span {
            color: var(--dorado-claro);
            position: relative;
            display: inline-block;
        }

        h1 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(201, 168, 76, 0.14);
            border-radius: 4px;
            z-index: -1;
        }

        p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 32px;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Spinner */
        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 24px;
            border: 4px solid rgba(255, 255, 255, 0.12);
            border-top-color: var(--dorado);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Barra de progreso */
        .progress-container {
            width: 100%;
            height: 6px;
            background: rgba(0, 0, 0, 0.28);
            border-radius: 3px;
            margin: 24px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--dorado-claro), var(--dorado));
            border-radius: 3px;
            animation: progress 3s ease forwards;
        }

        /* Mensaje de redirección */
        .redirect-message {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.55);
            margin-top: 16px;
        }

        .redirect-message a {
            color: var(--dorado-claro);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .redirect-message a:hover {
            color: var(--dorado);
            text-decoration: underline;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes progress {
            to {
                width: 100%;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.3;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card {
                padding: 40px 24px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .logo-wrapper {
                width: 100px;
                height: 100px;
            }

            .logo-wrapper i {
                font-size: 40px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 32px 20px;
            }

            h1 {
                font-size: 1.6rem;
            }

            p {
                font-size: 0.9rem;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Logo con animación -->
            <div class="logo-wrapper">
                <i class="fas fa-check-circle"></i>
            </div>

            <!-- Título -->
            <h1>¡Hasta <span>pronto!</span></h1>
            
            <!-- Mensaje descriptivo -->
            <p>
                Has cerrado sesión exitosamente. Serás redirigido a la página de inicio en unos segundos.
            </p>

            <!-- Spinner de carga -->
            <div class="spinner"></div>

            <!-- Barra de progreso -->
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>

            <!-- Mensaje de redirección con enlace directo -->
            <div class="redirect-message">
                <i class="fas fa-arrow-right"></i>
                Si no eres redirigido automáticamente, 
                <a href="<?= htmlspecialchars($redirectUrl) ?>">haz clic aquí</a>
            </div>
        </div>
    </div>

    <!-- Script para redirección automática -->
    <script>
        setTimeout(function() {
            window.location.href = '<?= htmlspecialchars($redirectUrl) ?>';
        }, 3000); // 3 segundos
    </script>
</body>
</html>