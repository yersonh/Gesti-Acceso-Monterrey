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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde-institucional: #1a5c38;
            --verde-medio: #2d7a4f;
            --verde-claro: #3d9e68;
            --dorado: #c9a84c;
            --dorado-claro: #e8c97a;
            --blanco: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;

            background-image:
                linear-gradient(180deg, rgba(6, 14, 10, 0.55) 0%, rgba(6, 14, 10, 0.72) 55%, rgba(6, 14, 10, 0.85) 100%),
                url('/imagenes/Fondo-ciudad.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* ── Tarjeta de vidrio (glassmorphism) ── */
        .glass-card {
            width: 100%;
            max-width: 460px;
            background: rgba(20, 34, 26, 0.45);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 22px;
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(201, 168, 76, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            padding: 40px 40px 32px;
            position: relative;
            overflow: hidden;
            animation: cardEntrar 0.55s ease both;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--verde-institucional), var(--dorado), var(--verde-claro));
        }

        @keyframes cardEntrar {
            from { opacity: 0; transform: translateY(22px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Encabezado ── */
        .card-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 26px;
        }

        .logo-wrap {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            border: 3px solid var(--dorado);
            box-shadow: 0 0 0 6px rgba(201, 168, 76, 0.14), 0 8px 26px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            background: #fff;
            margin-bottom: 16px;
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-header .marca-titulo {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--blanco);
            letter-spacing: 0.02em;
        }

        .card-header .marca-subtitulo {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--dorado-claro);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .card-header .marca-desc {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 8px;
            font-weight: 300;
        }

        .separador-titulo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 22px 0 24px;
        }

        .separador-titulo::before,
        .separador-titulo::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.18), transparent);
        }

        .separador-titulo span {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.55);
            white-space: nowrap;
        }

        /* Error */
        .error-box {
            display: <?php echo $error ? 'flex' : 'none'; ?>;
            align-items: flex-start;
            gap: 10px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-left: 3px solid #ef4444;
            color: #fecaca;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 0.85rem;
        }

        .error-box i { margin-top: 2px; flex-shrink: 0; }

        /* Grupos de campo */
        .campo {
            margin-bottom: 20px;
        }

        .campo label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 0.06em;
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
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.9rem;
            transition: color 0.25s;
            pointer-events: none;
        }

        .campo-input input {
            width: 100%;
            padding: 13px 14px 13px 40px;
            background: rgba(0, 0, 0, 0.28);
            border: 1.5px solid rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            font-size: 0.92rem;
            font-family: 'Outfit', sans-serif;
            color: var(--blanco);
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

        .campo-input input:focus + .icono-campo,
        .campo-input:focus-within i.icono-campo {
            color: var(--dorado-claro);
        }

        .campo-input input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-ver {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            padding: 4px;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .btn-ver:hover { color: var(--dorado-claro); }

        /* Opciones bajo el campo contraseña */
        .opciones-password {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -6px;
            margin-bottom: 26px;
        }

        .checkbox-recordar {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-recordar input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--dorado);
            cursor: pointer;
        }

        .checkbox-recordar span {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.65);
            font-weight: 400;
        }

        .link-olvido {
            font-size: 0.8rem;
            color: var(--dorado-claro);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link-olvido:hover { color: var(--dorado); text-decoration: underline; }

        .registro-link {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .registro-link:hover {
            color: var(--dorado-claro);
        }

        .registro-link i {
            font-size: 0.75rem;
        }

        /* Botón principal — dorado, como en la referencia */
        .btn-ingresar {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            border: none;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }

        .btn-ingresar:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
        }

        .btn-ingresar:active { transform: translateY(0); }

        .btn-ingresar:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Enlace registro */
        .zona-registro {
            text-align: center;
            margin: 20px 0 4px;
        }

        /* Logo NexGovIA */
        .nexgov-logo {
            margin-top: 22px;
            text-align: center;
        }

        .nexgov-logo img {
            max-width: 190px;
            height: auto;
            opacity: 0.75;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .nexgov-logo img:hover {
            opacity: 1;
            transform: scale(1.02);
        }

        /* Franja inferior */
        .footer-form {
            margin-top: 18px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .footer-form p {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.7;
            text-align: center;
        }

        .footer-form strong {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 600;
        }

        .footer-form a,
        .btn-nexgov-trigger {
            color: var(--dorado-claro);
        }

        /* ── Responsivo móvil ── */
        @media (max-width: 480px) {
            .glass-card {
                padding: 30px 24px 26px;
                border-radius: 18px;
            }

            .logo-wrap {
                width: 70px;
                height: 70px;
            }

            .card-header .marca-titulo { font-size: 1.15rem; }
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <div class="card-header">
            <div class="logo-wrap">
                <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            </div>
            <div class="marca-titulo">Alcaldía de Monterrey</div>
            <div class="marca-subtitulo">Casanare</div>
            <div class="marca-desc">Sistema de Control de Visitas</div>
        </div>

        <div class="separador-titulo"><span>Iniciar sesión</span></div>

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

            <button type="submit" class="btn-ingresar" id="btnIngresar" <?= ($bloqueado ?? false) ? 'disabled' : '' ?>>
                <?php if ($bloqueado ?? false): ?>
                    <i class="fas fa-lock"></i>
                    Bloqueado — espera <span id="countdown"><?= $minutos ?>:00</span>
                <?php else: ?>
                    <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
                <?php endif; ?>
            </button>
        </form>

        <div class="zona-registro">
            <a href="/registro" class="registro-link">
                <i class="fas fa-user-plus"></i>
                ¿No tiene cuenta? Regístrese aquí
            </a>
        </div>

        <div class="footer-form">
            <p>
                <strong>© 2026 Sistema de control de visitas SCV</strong> — Monterrey, Casanare<br>
                Desarrollado por <button class="btn-nexgov-trigger" onclick="abrirModalNexGov()" style="background:none;border:none;cursor:pointer;font:inherit;padding:0;">NexGovIA S.A.S.®</button> · ☎ (+57) 310 631 02 27 · soportesgp@gmail.com
            </p>
        </div>
    </div>

    <?php require_once BASE_PATH . '/views/components/modal_nexgovia.php'; ?>

    <script>
        // Toggle contraseña
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon  = this.querySelector('i');
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            icon.className = esPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        <?php if ($bloqueado ?? false): ?>
        // Cuenta regresiva
        (function(){
            var secs = <?= ($minutos ?? 0) * 60 ?>;
            var el   = document.getElementById('countdown');
            if (secs <= 0 || !el) return;
            var t = setInterval(function(){
                secs--;
                if (secs <= 0) { clearInterval(t); window.location.reload(); return; }
                var m = Math.floor(secs / 60), s = secs % 60;
                el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            }, 1000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>
