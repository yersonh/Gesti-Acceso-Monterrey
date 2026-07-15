<?php
// Variables disponibles: $cita, $resultado, $token
// $resultado: 'aceptada' | 'rechazada' | 'contrapropuesta_enviada' | 'expirado' | (vacío = mostrar formulario)

function fh($h) {
    if (!$h) return '';
    [$hr, $m] = explode(':', $h);
    $hr = (int)$hr;
    return ($hr > 12 ? $hr - 12 : ($hr ?: 12)) . ':' . $m . ' ' . ($hr >= 12 ? 'PM' : 'AM');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
<script>
window.configSistema = <?= json_encode([
    'diasHabiles' => $diasHabiles   ?? [],
    '_festivos'   => $festivosReprog ?? [],
]) ?>;
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder propuesta de cita — Alcaldía de Monterrey</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f8f9fc; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
        .card { background: white; border-radius: 20px; padding: 40px; max-width: 520px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.08); }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo img { width: 56px; height: 56px; border-radius: 50%; border: 2px solid #c9a84c; }
        .logo p { font-size: 0.78rem; color: #64748b; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
        h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #1e293b; margin-bottom: 8px; text-align: center; }
        .subtitulo { color: #64748b; font-size: 0.9rem; text-align: center; margin-bottom: 28px; }
        .info-box { background: #f8fafc; border-left: 4px solid #c9a84c; border-radius: 0 8px 8px 0; padding: 16px 20px; margin-bottom: 28px; font-size: 0.9rem; }
        .info-row { display: flex; gap: 8px; margin-bottom: 6px; color: #334155; }
        .info-row:last-child { margin-bottom: 0; }
        .info-row i { color: #1a5c38; width: 16px; margin-top: 2px; }
        .tachado { text-decoration: line-through; color: #94a3b8; }
        .nuevo { color: #f59e0b; font-weight: 600; }
        .acciones { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
        .btn { padding: 14px 20px; border: none; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: all 0.2s; }
        .btn-verde { background: #1a5c38; color: white; }
        .btn-verde:hover { background: #2d7a4f; }
        .btn-rojo { background: white; border: 1.5px solid #fecaca; color: #ef4444; }
        .btn-rojo:hover { background: #fef2f2; }
        .btn-amarillo { background: white; border: 1.5px solid #fde68a; color: #92400e; }
        .btn-amarillo:hover { background: #fffbeb; }
        .divider { text-align: center; color: #94a3b8; font-size: 0.82rem; margin: 4px 0 12px; }
        .contrapropuesta { display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-top: 4px; }
        .contrapropuesta.visible { display: block; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input[type="date"], select { width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; margin-bottom: 14px; outline: none; }
        input[type="date"]:focus, select:focus { border-color: #f59e0b; }
        .resultado-icon { font-size: 3rem; text-align: center; margin-bottom: 16px; }
        .alerta { padding: 16px 20px; border-radius: 10px; font-size: 0.9rem; text-align: center; }
        .alerta-ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alerta-info { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .alerta-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .footer { text-align: center; margin-top: 24px; font-size: 0.78rem; color: #94a3b8; }

        @media (max-width: 480px) {
            body { padding: 12px 12px 28px; align-items: flex-start; justify-content: flex-start; }
            .card { padding: 24px 16px; border-radius: 14px; }
            h2 { font-size: 1.25rem; }
            .subtitulo { font-size: 0.85rem; margin-bottom: 20px; }
            .btn { padding: 12px 14px; font-size: 0.88rem; }
            .info-box { padding: 12px 14px; font-size: 0.85rem; }
            .acciones { gap: 8px; }
            .contrapropuesta { padding: 16px; }
            input[type="date"], select { padding: 9px 10px; font-size: 0.85rem; }
        }
        /* ── Modals ── */
        .modal-overlay { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(4px); }
        .modal-overlay.open { display:flex; }
        .modal-card { background:#fff;border-radius:20px;padding:32px 28px;max-width:400px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:mSlide .25s ease; }
        @keyframes mSlide { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
        .modal-icon { font-size:2.4rem;margin-bottom:14px; }
        .modal-title { font-family:'Playfair Display',serif;font-size:1.2rem;color:#1e293b;margin-bottom:10px; }
        .modal-msg { font-size:.88rem;color:#64748b;line-height:1.6;margin-bottom:24px; }
        .btn-entendido { padding:12px 32px;background:#1a5c38;color:#fff;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.95rem;cursor:pointer;transition:background .2s; }
        .btn-entendido:hover { background:#2d7a4f; }
        .btn-cancelar-modal { padding:12px 24px;background:#fff;color:#64748b;border:1.5px solid #e2e8f0;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:500;font-size:.9rem;cursor:pointer;transition:all .2s; }
        .btn-cancelar-modal:hover { border-color:#94a3b8; }
        .btn-confirmar-peligro { padding:12px 24px;background:#ef4444;color:#fff;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:background .2s; }
        .btn-confirmar-peligro:hover { background:#dc2626; }
        .modal-acciones { display:flex;gap:10px;justify-content:center;flex-wrap:wrap; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo">
        <p>Alcaldía Municipal · Monterrey, Casanare</p>
    </div>

    <?php if (isset($resultado) && $resultado === 'aceptada'): ?>
        <div class="resultado-icon"></div>
        <h2>¡Cita confirmada!</h2>
        <p class="subtitulo">Has aceptado la nueva fecha propuesta por el funcionario.</p>
        <div class="alerta alerta-ok">
            <i class="fas fa-check-circle"></i> Tu cita ha sido actualizada exitosamente. Recibirás un recordatorio próximamente.
        </div>

    <?php elseif (isset($resultado) && $resultado === 'rechazada'): ?>
        <div class="resultado-icon"></div>
        <h2>Propuesta rechazada</h2>
        <p class="subtitulo">El funcionario ha sido notificado para buscar una nueva alternativa.</p>
        <div class="alerta alerta-info">
            <i class="fas fa-info-circle"></i> Tu cita original permanece en estado pendiente hasta que se acuerde una nueva fecha.
        </div>

    <?php elseif (isset($resultado) && $resultado === 'contrapropuesta_enviada'): ?>
        <div class="resultado-icon"></div>
        <h2>Propuesta enviada</h2>
        <p class="subtitulo">El funcionario revisará tu propuesta de horario.</p>
        <div class="alerta alerta-info">
            <i class="fas fa-clock"></i> Recibirás una respuesta por correo cuando el funcionario confirme o proponga otra alternativa.
        </div>

    <?php elseif (isset($resultado) && $resultado === 'expirado'): ?>
        <div class="resultado-icon"></div>
        <h2>Enlace expirado</h2>
        <p class="subtitulo">Este enlace ya no es válido (venció a las 48 horas).</p>
        <div class="alerta alerta-error">
            <i class="fas fa-exclamation-circle"></i> Inicia sesión en tu cuenta para ver el estado de tu cita o contacta a la alcaldía.
        </div>

    <?php elseif (isset($resultado) && $resultado === 'error_fecha'): ?>
        <div class="resultado-icon"></div>
        <h2>Fecha u hora inválida</h2>
        <p class="subtitulo">El formato de la fecha o la hora no es correcto.</p>
        <div class="alerta alerta-error">
            <i class="fas fa-exclamation-circle"></i> Por favor usa el selector del formulario para elegir la fecha y la hora.
        </div>
        <a href="javascript:history.back()" style="display:inline-block;margin-top:16px;padding:10px 20px;background:#1a5c38;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            ← Volver e intentar de nuevo
        </a>

    <?php elseif (isset($resultado) && $resultado === 'error_fecha_pasado'): ?>
        <div class="resultado-icon"></div>
        <h2>Fecha en el pasado</h2>
        <p class="subtitulo">No puedes proponer una fecha que ya pasó.</p>
        <div class="alerta alerta-error">
            <i class="fas fa-exclamation-circle"></i> Selecciona una fecha a partir de hoy.
        </div>
        <a href="javascript:history.back()" style="display:inline-block;margin-top:16px;padding:10px 20px;background:#1a5c38;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            ← Volver e intentar de nuevo
        </a>

    <?php else: ?>
        <!-- Formulario de decisión -->
        <h2>Propuesta de reprogramación</h2>
        <p class="subtitulo">El funcionario propone cambiar tu cita a:</p>

        <div class="info-box">
            <div class="info-row">
                <i class="fas fa-calendar-times"></i>
                <span>Fecha anterior: <span class="tachado"><?= date('d/m/Y', strtotime($cita['fecha'])) ?> · <?= fh(substr($cita['hora_inicio'], 0, 5)) ?></span></span>
            </div>
            <div class="info-row">
                <i class="fas fa-calendar-check"></i>
                <span>Nueva propuesta: <span class="nuevo"><?= date('d/m/Y', strtotime($cita['fecha_propuesta'])) ?> · <?= fh(substr($cita['hora_propuesta'], 0, 5)) ?></span></span>
            </div>
            <div class="info-row">
                <i class="fas fa-building"></i>
                <span><?= htmlspecialchars($cita['dependencia_nombre'] ?? '') ?></span>
            </div>
            <?php if (!empty($cita['motivo_reprogramacion'])): ?>
            <div class="info-row">
                <i class="fas fa-comment"></i>
                <span><em><?= htmlspecialchars($cita['motivo_reprogramacion']) ?></em></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="acciones">
            <a href="/cita/responder?token=<?= htmlspecialchars($token) ?>&accion=aceptar" class="btn btn-verde">
                <i class="fas fa-check"></i> Aceptar esta fecha
            </a>

            <div class="divider">— o —</div>

            <button type="button" class="btn btn-amarillo" onclick="toggleContrapropuesta()">
                <i class="fas fa-calendar-alt"></i> Proponer otra fecha
            </button>

            <button type="button" class="btn btn-rojo" onclick="confirmarRechazar()">
                <i class="fas fa-times"></i> Rechazar propuesta
            </button>
        </div>

        <!-- Formulario contrapropuesta -->
        <div class="contrapropuesta" id="formContrapropuesta">
            <form method="POST" action="/cita/responder?token=<?= htmlspecialchars($token) ?>">
                <?= csrf_field() ?>
            <?= tab_id_field() ?>
                <label><i class="fas fa-calendar"></i> Nueva fecha que propones</label>
                <input type="date" name="contrapropuesta_fecha" required min="<?= date('Y-m-d') ?>">

                <label><i class="fas fa-clock"></i> Nueva hora que propones</label>
                <select name="contrapropuesta_hora" required>
                    <option value="">Selecciona una hora...</option>
                    <?php
                    $slots = ['07:00','07:15','07:30','07:45','08:00','08:15','08:30','08:45',
                              '09:00','09:15','09:30','09:45','10:00','10:15','10:30','10:45',
                              '11:00','11:15','11:30','11:45','14:00','14:15','14:30','14:45',
                              '15:00','15:15','15:30','15:45','16:00','16:15','16:30','16:45'];
                    foreach ($slots as $s):
                        [$h, $m] = explode(':', $s);
                        $hi = (int)$h;
                        $label = ($hi > 12 ? $hi - 12 : ($hi ?: 12)) . ':' . $m . ' ' . ($hi >= 12 ? 'PM' : 'AM');
                    ?>
                    <option value="<?= $s ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-amarillo">
                    <i class="fas fa-paper-plane"></i> Enviar mi propuesta
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<p class="footer">© <?= date('Y') ?> Alcaldía Municipal de Monterrey, Casanare</p>

<!-- Modal aviso (festivo / día no hábil / etc.) -->
<div class="modal-overlay" id="modal-aviso">
    <div class="modal-card">
        <div class="modal-icon" id="aviso-icon"></div>
        <div class="modal-title" id="aviso-titulo"></div>
        <div class="modal-msg" id="aviso-msg"></div>
        <div class="modal-acciones">
            <button type="button" class="btn-entendido" onclick="cerrarAviso()">Entendido</button>
        </div>
    </div>
</div>

<!-- Modal confirmar rechazo -->
<div class="modal-overlay" id="modal-rechazar">
    <div class="modal-card">
        <div class="modal-icon"><i class="fas fa-times-circle" style="color:#ef4444;"></i></div>
        <div class="modal-title">¿Rechazar propuesta?</div>
        <div class="modal-msg">El funcionario será notificado y buscará una nueva alternativa. Tu cita permanece pendiente.</div>
        <div class="modal-acciones">
            <button type="button" class="btn-cancelar-modal" onclick="document.getElementById('modal-rechazar').classList.remove('open')">Cancelar</button>
            <a href="/cita/responder?token=<?= htmlspecialchars($token ?? '') ?>&accion=rechazar" class="btn-confirmar-peligro">
                <i class="fas fa-times"></i> Rechazar
            </a>
        </div>
    </div>
</div>

<script>
function toggleContrapropuesta() {
    document.getElementById('formContrapropuesta').classList.toggle('visible');
}
function cerrarAviso() {
    document.getElementById('modal-aviso').classList.remove('open');
}
function mostrarAviso(titulo, mensaje, tipo) {
    var iconos = {
        error:   '<i class="fas fa-calendar-times" style="color:#ef4444;font-size:2.4rem;"></i>',
        warning: '<i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:2.4rem;"></i>',
        info:    '<i class="fas fa-info-circle" style="color:#1a5c38;font-size:2.4rem;"></i>'
    };
    document.getElementById('aviso-icon').innerHTML  = iconos[tipo] || iconos.info;
    document.getElementById('aviso-titulo').textContent = titulo;
    document.getElementById('aviso-msg').textContent    = mensaje;
    document.getElementById('modal-aviso').classList.add('open');
}
function confirmarRechazar() {
    document.getElementById('modal-rechazar').classList.add('open');
}

// ── Validación de fecha en contrapropuesta ──
(function() {
    var conf = window.configSistema || { diasHabiles: [], _festivos: [] };

    function esFestivo(fechaStr) {
        var festivos = conf._festivos || [];
        var mmdd = fechaStr.substring(5);
        for (var i = 0; i < festivos.length; i++) {
            var f = festivos[i];
            var esRec = f.recurrente === 't' || f.recurrente === true || f.recurrente === 1 || f.recurrente === '1';
            if (esRec ? f.clave === mmdd : f.clave === fechaStr) return f.descripcion;
        }
        return null;
    }

    var dateInput = document.querySelector('input[name="contrapropuesta_fecha"]');
    if (!dateInput) return;

    dateInput.addEventListener('change', function() {
        var fecha = this.value;
        if (!fecha) return;

        var festivoNombre = esFestivo(fecha);
        if (festivoNombre) {
            this.value = '';
            mostrarAviso('Día festivo', 'Ese día es festivo (' + festivoNombre + ') y la alcaldía no tiene atención. Por favor elige otra fecha.', 'error');
            return;
        }

        var diasHabiles = conf.diasHabiles || [];
        var d = new Date(fecha + 'T00:00:00');
        var dayJS = d.getDay();
        if (diasHabiles.length && !diasHabiles.includes(dayJS)) {
            this.value = '';
            mostrarAviso('Día no hábil', 'La alcaldía no atiende ese día. Por favor elige una fecha entre los días hábiles.', 'warning');
        }
    });
})();
</script>
</body>
</html>