<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;var t=document.createElement("meta");t.name="csrf-token";document.head&&document.head.appendChild(t);function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Cambiar Contraseña</title>
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
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
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
            border-radius: 22px;
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(201, 168, 76, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            padding: 40px 48px;
            width: 100%;
            max-width: 440px;
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
        .logo-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-wrap img {
            height: 60px;
            border-radius: 50%;
            border: 2px solid var(--dorado);
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--blanco);
            text-align: center;
            margin-bottom: 6px;
        }
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-bottom: 28px;
        }
        .banner-temp {
            background: rgba(201, 168, 76, 0.12);
            border: 1.5px solid rgba(201, 168, 76, 0.4);
            border-radius: 9px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 0.88rem;
            color: var(--dorado-claro);
        }
        .banner-temp i { color: var(--dorado); margin-top: 2px; flex-shrink: 0; }
        .alert {
            border-radius: 9px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 0.9rem;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .alert-error   { background: rgba(239, 68, 68, 0.12); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4); }
        .alert-success { background: rgba(52, 211, 153, 0.14); color: #6ee7b7; border: 1px solid rgba(110, 231, 183, 0.4); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 6px; }
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            background: rgba(0, 0, 0, 0.28);
            border: 1.5px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--blanco);
            transition: all .2s;
            outline: none;
        }
        input[type="password"]:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.18);
            background: rgba(0, 0, 0, 0.38);
        }
        .hint { font-size: 0.78rem; color: rgba(255, 255, 255, 0.5); margin-top: 5px; }
        .btn {
            width: 100%;
            background: linear-gradient(135deg, var(--dorado-claro), var(--dorado));
            color: #2c2107;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all .2s;
            margin-top: 6px;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4); }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-decoration: none;
        }
        .back-link:hover { color: var(--dorado-claro); }
    </style>
</head>
<body>
<div class="card">
    <div class="logo-wrap">
        <img src="/imagenes/favicon.png" alt="Logo">
    </div>
    <h1>Cambiar contraseña</h1>
    <p class="subtitle">Alcaldía Municipal de Monterrey, Casanare</p>

    <div class="banner-temp">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Tu cuenta usa una contraseña temporal. Crea una nueva contraseña para asegurar tu cuenta.</span>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/cambiar-contrasena">
        <?= csrf_field() ?>
        <?= tab_id_field() ?>

        <div class="form-group">
            <label for="nueva_password">Nueva contraseña</label>
            <input type="password" id="nueva_password" name="nueva_password" required autofocus>
            <p class="hint">Mínimo 8 caracteres, una mayúscula y un número.</p>
        </div>

        <div class="form-group">
            <label for="confirmar_password">Confirmar contraseña</label>
            <input type="password" id="confirmar_password" name="confirmar_password" required>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-key"></i> Actualizar contraseña
        </button>
    </form>

    <a href="/funcionario/dashboard" class="back-link">
        <i class="fas fa-arrow-left"></i> Volver al panel
    </a>
</div>
</body>
</html>
