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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 48px;
            width: 100%;
            max-width: 440px;
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-wrap img {
            height: 60px;
            border-radius: 50%;
        }
        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a3c2e;
            text-align: center;
            margin-bottom: 6px;
        }
        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 28px;
        }
        .banner-temp {
            background: #fffbeb;
            border: 1.5px solid #f59e0b;
            border-radius: 9px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 0.88rem;
            color: #78350f;
        }
        .banner-temp i { color: #d97706; margin-top: 2px; flex-shrink: 0; }
        .alert {
            border-radius: 9px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 0.9rem;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color .2s;
            outline: none;
        }
        input[type="password"]:focus { border-color: #1a5c38; }
        .hint { font-size: 0.78rem; color: #9ca3af; margin-top: 5px; }
        .btn {
            width: 100%;
            background: #1a5c38;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background .2s;
            margin-top: 6px;
        }
        .btn:hover { background: #145030; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #6b7280;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .back-link:hover { color: #1a5c38; }
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
