<?php
// views/valoracion/formulario.php
// Variables disponibles: $valoracion, $datosVisita, $error, $token
$funcNombre = trim(($datosVisita['func_nombres'] ?? '') . ' ' . ($datosVisita['func_apellidos'] ?? ''));
$depNombre  = $datosVisita['dependencia'] ?? '';
$visitante  = trim(($datosVisita['nombres'] ?? '') . ' ' . ($datosVisita['apellidos'] ?? ''));
$fecha      = isset($datosVisita['fecha']) ? date('d/m/Y', strtotime($datosVisita['fecha'])) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Califica tu visita — Alcaldía de Monterrey</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --verde:       #1a5c38;
            --verde-medio: #2d7a4f;
            --verde-claro: #3d9e68;
            --dorado:      #c9a84c;
            --dorado-claro:#e8c97a;
            --blanco:      #ffffff;
            --texto:       #eef3ee;
            --texto-sub:   rgba(255, 255, 255, 0.6);
            --borde:       rgba(255, 255, 255, 0.14);
            --fondo:       rgba(0, 0, 0, 0.28);
            --rojo:        #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { min-height: 100%; }

        body {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;

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
            padding: 40px 36px;
            max-width: 520px;
            width: 100%;
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

        /* Header */
        .encabezado {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--borde);
        }
        .encabezado img {
            width: 48px; height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--dorado);
        }
        .encabezado-texto {
            font-size: 0.78rem;
            color: var(--texto-sub);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            line-height: 1.5;
        }
        .encabezado-texto strong {
            display: block;
            color: var(--dorado-claro);
            font-size: 0.88rem;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--blanco);
            margin-bottom: 6px;
        }
        h1 span { color: var(--dorado-claro); }

        .subtitulo {
            color: var(--texto-sub);
            font-size: 0.9rem;
            font-weight: 300;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Info de la visita */
        .info-visita {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-left: 4px solid var(--dorado);
            border-radius: 0 10px 10px 0;
            padding: 14px 16px;
            margin-bottom: 28px;
            font-size: 0.88rem;
            color: var(--texto-sub);
            line-height: 1.9;
        }
        .info-visita strong { color: var(--texto); }

        /* Error */
        .alerta-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-left: 4px solid var(--rojo);
            color: #fecaca;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }

        /* Etiqueta de sección */
        .seccion-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--texto);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }

        /* ── ESTRELLAS ── */
        .estrellas-wrap { margin-bottom: 28px; }

        .estrellas {
            display: flex;
            gap: 6px;
            /* Truco: el HTML va de 5→1 pero se muestra de 1→5 con flex-row-reverse */
            flex-direction: row-reverse;
            justify-content: flex-end;
        }

        .estrellas input[type="radio"] { display: none; }

        .estrellas label {
            font-size: 2.4rem;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: color 0.15s, transform 0.15s;
            line-height: 1;
            user-select: none;
        }

        /*
         * CSS-only trick:
         * En flex-row-reverse, "~" selecciona hermanos que vienen DESPUÉS en el DOM
         * pero que en la pantalla aparecen a la IZQUIERDA.
         * Así conseguimos que al hacer hover en s3 se iluminen s3, s2, s1.
         */
        .estrellas label:hover,
        .estrellas label:hover ~ label {
            color: var(--dorado-claro);
        }
        .estrellas input:checked ~ label,
        .estrellas input:checked + label {
            color: var(--dorado);
        }
        /* La propia estrella seleccionada */
        .estrellas input:checked + label {
            color: var(--dorado);
            transform: scale(1.15);
        }
        .estrellas label:hover {
            transform: scale(1.15);
        }

        .texto-estrella {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--texto-sub);
            min-height: 20px;
        }

        /* ── SOLUCIONADO ── */
        .opciones-solucionado {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
        }

        .opcion-label {
            flex: 1;
            padding: 16px 12px;
            border: 2px solid var(--borde);
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.2);
            cursor: pointer;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--texto-sub);
            transition: all 0.2s;
        }
        .opcion-label input { display: none; }
        .opcion-label .icono-opcion { font-size: 1.6rem; display: block; margin-bottom: 6px; }
        .opcion-label:hover {
            border-color: var(--dorado);
            background: rgba(201, 168, 76, 0.1);
            color: var(--dorado-claro);
        }
        .opcion-label.activo {
            border-color: var(--dorado);
            background: rgba(201, 168, 76, 0.14);
            color: var(--dorado-claro);
            font-weight: 600;
        }

        /* ── COMENTARIO ── */
        .campo-comentario { margin-bottom: 28px; }
        .campo-comentario textarea {
            width: 100%;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.28);
            border: 1.5px solid var(--borde);
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            color: var(--texto);
            resize: vertical;
            min-height: 90px;
            outline: none;
            transition: all 0.2s;
        }
        .campo-comentario textarea:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.18);
            background: rgba(0, 0, 0, 0.38);
        }
        .campo-comentario textarea::placeholder { color: rgba(255, 255, 255, 0.4); }
        .campo-hint { font-size: 0.78rem; color: var(--texto-sub); margin-top: 5px; }

        /* ── BOTÓN ── */
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
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
        }
        .btn-enviar:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
        }
        .btn-enviar:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* Footer */
        .footer-card {
            margin-top: 28px;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 20px; }
            .estrellas label { font-size: 2rem; }
            .opciones-solucionado { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="encabezado">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="encabezado-texto">
            Alcaldía Municipal
            <strong>Monterrey · Casanare</strong>
        </div>
    </div>

    <h1>Califica tu <span>visita</span></h1>
    <p class="subtitulo">Tu opinión es muy importante para mejorar nuestro servicio. Solo toma un minuto.</p>

    <?php if ($datosVisita): ?>
    <div class="info-visita">
        <?php if ($visitante): ?>
        <strong>Visitante:</strong> <?= htmlspecialchars($visitante) ?><br>
        <?php endif; ?>
        <strong>Funcionario:</strong> <?= htmlspecialchars($funcNombre) ?><br>
        <strong>Dependencia:</strong> <?= htmlspecialchars($depNombre) ?><br>
        <strong>Fecha:</strong> <?= $fecha ?><br>
        <strong>Motivo:</strong> <?= htmlspecialchars($datosVisita['motivo'] ?? '') ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alerta-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/valorar?token=<?= htmlspecialchars($token) ?>" id="formValoracion">
        <?= csrf_field() ?>
            <?= tab_id_field() ?>
        <!-- ESTRELLAS: HTML en orden 5→1 para que el truco CSS funcione -->
        <div class="seccion-label">¿Cómo calificarías la atención? <span style="color:var(--rojo)">*</span></div>
        <div class="estrellas-wrap">
            <div class="estrellas" id="estrellas">
                <input type="radio" name="estrellas" id="s5" value="5">
                <label for="s5">★</label>
                <input type="radio" name="estrellas" id="s4" value="4">
                <label for="s4">★</label>
                <input type="radio" name="estrellas" id="s3" value="3">
                <label for="s3">★</label>
                <input type="radio" name="estrellas" id="s2" value="2">
                <label for="s2">★</label>
                <input type="radio" name="estrellas" id="s1" value="1">
                <label for="s1">★</label>
            </div>
            <div class="texto-estrella" id="textoEstrella"></div>
        </div>

        <!-- SOLUCIONADO -->
        <div class="seccion-label">¿Tu motivo de visita fue solucionado? <span style="color:var(--rojo)">*</span></div>
        <div class="opciones-solucionado">
            <label class="opcion-label" id="lbl-si">
                <input type="radio" name="solucionado" value="si" required>
                <i class="fas fa-check-circle icono-opcion" style="color:#22c55e;"></i>
                Sí, fue solucionado
            </label>
            <label class="opcion-label" id="lbl-no">
                <input type="radio" name="solucionado" value="no">
                <i class="fas fa-times-circle icono-opcion" style="color:#ef4444;"></i>
                No, sin solución
            </label>
        </div>

        <!-- COMENTARIO -->
        <div class="seccion-label">Comentario adicional <span style="color:var(--texto-sub); font-weight:400; text-transform:none;">(opcional)</span></div>
        <div class="campo-comentario">
            <textarea name="comentario" placeholder="Cuéntanos más sobre tu experiencia..." maxlength="500"></textarea>
            <div class="campo-hint">Máximo 500 caracteres</div>
        </div>

        <button type="submit" class="btn-enviar" id="btnEnviar">
            ✓ &nbsp;Enviar valoración
        </button>
    </form>
</div>

<div class="footer-card">
    © <?= date('Y') ?> Sistema de Control de Visitas — Alcaldía Municipal de Monterrey, Casanare
</div>

<script>
    const textos = {
        1: '😞 Muy malo',
        2: '😐 Malo',
        3: '🙂 Regular',
        4: '😊 Bueno',
        5: '🤩 Excelente',
    };

    // Actualizar texto descriptivo al seleccionar estrella
    document.querySelectorAll('input[name="estrellas"]').forEach(inp => {
        inp.addEventListener('change', () => {
            document.getElementById('textoEstrella').textContent = textos[inp.value] || '';
        });
    });

    // Resaltar botón solucionado
    document.querySelectorAll('input[name="solucionado"]').forEach(inp => {
        inp.addEventListener('change', () => {
            document.querySelectorAll('.opcion-label').forEach(l => l.classList.remove('activo'));
            inp.closest('.opcion-label').classList.add('activo');
        });
    });

    // Validación antes de enviar
    document.getElementById('formValoracion').addEventListener('submit', function(e) {
        const estrellas  = document.querySelector('input[name="estrellas"]:checked');
        const solucionado = document.querySelector('input[name="solucionado"]:checked');

        if (!estrellas) {
            e.preventDefault();
            alert('Por favor selecciona una calificación con las estrellas.');
            return;
        }
        if (!solucionado) {
            e.preventDefault();
            alert('Por favor indica si tu motivo de visita fue solucionado.');
            return;
        }

        // Deshabilitar botón para evitar doble envío
        document.getElementById('btnEnviar').disabled = true;
        document.getElementById('btnEnviar').textContent = 'Enviando...';
    });
</script>

</body>
</html>