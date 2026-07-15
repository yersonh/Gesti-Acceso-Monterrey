<?php
// Vista — la lógica está en CitaController::dashboard()
// Variables: $ciudadanoId, $ciudadanoNombre, $ciudadanoIniciales, $primerNombre
// $filtroEstado, $mensajeOk, $mensajeCancel, $errorDb, $stats, $citas

function badgeEstado(string $estado): array {
    return ([
        'pendiente'  => ['label' => 'Pendiente',  'class' => 'badge-pendiente',  'icon' => 'fa-clock'],
        'confirmada' => ['label' => 'Confirmada', 'class' => 'badge-confirmada', 'icon' => 'fa-check-circle'],
        'cancelada'  => ['label' => 'Cancelada',  'class' => 'badge-cancelada',  'icon' => 'fa-times-circle'],
        'finalizada' => ['label' => 'Finalizada', 'class' => 'badge-completada', 'icon' => 'fa-flag-checkered'],
        'no_asistio' => ['label' => 'No asistió', 'class' => 'badge-noasistio',  'icon' => 'fa-user-times'],
        'propuesta_reprogramacion' => ['label' => 'Propuesta de reprogramación', 'class' => 'badge-propuesta-reprogramacion', 'icon' => 'fa-calendar-plus'],
        'contrapropuesta_ciudadano' => ['label' => 'Contrapropuesta del ciudadano', 'class' => 'badge-contrapropuesta-ciudadano', 'icon' => 'fa-calendar-plus'],
    ][$estado] ?? ['label' => $estado, 'class' => '', 'icon' => 'fa-circle']);
}

function formatFecha(string $fecha): string {
    $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    [$y,$m,$d] = explode('-', $fecha);
    return "$d {$meses[(int)$m]} $y";
}

function formatHora(string $hora): string {
    [$h,$m] = explode(':', $hora);
    $h = (int)$h;
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $h12  = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
    return "$h12:$m $ampm";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
<script>
window.configSistema = <?= json_encode([
    'diasHabiles' => $diasHabilesDash ?? [],
    '_festivos'   => $festivosDash    ?? [],
]) ?>;
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGV — Mi Dashboard</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde:        #1a5c38;
            --verde-medio:  #2d7a4f;
            --verde-claro:  #3d9e68;
            --dorado:       #c9a84c;
            --dorado-claro: #e8c97a;
            --crema:        #f8f4ed;
            --blanco:       #ffffff;
            --texto:        #1e293b;
            --texto-sub:    #64748b;
            --borde:        #e2e8f0;
            --fondo:        #f8f9fc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--fondo);
            color: var(--texto);
            min-height: 100vh;
        }

        /* ── Header ── */
        .header {
            background: var(--verde);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--verde), var(--dorado), var(--verde-claro));
        }
        .header-brand {
            display: flex; align-items: center; gap: 14px; text-decoration: none;
        }
        .header-brand img {
            width: 40px; height: 40px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--dorado);
        }
        .header-brand-texto {
            color: var(--blanco); font-size: 0.8rem; font-weight: 500;
            letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; line-height: 1.4;
        }
        .header-brand-texto strong {
            display: block; font-size: 0.92rem; color: var(--dorado-claro); letter-spacing: 0.04em;
        }
        .header-usuario { display: flex; align-items: center; gap: 14px; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(201,168,76,0.25); border: 2px solid var(--dorado);
            color: var(--dorado-claro); font-size: 0.85rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; letter-spacing: 0.05em;
        }
        .header-nombre { color: var(--blanco); font-size: 0.88rem; font-weight: 500; opacity: 0.9; }
        .btn-logout {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco); padding: 7px 14px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.35); }

        /* ── Main ── */
        .main { max-width: 1100px; margin: 0 auto; padding: 36px 24px 60px; }

        /* ── Bienvenida ── */
        .bienvenida { margin-bottom: 32px; }
        .bienvenida h1 {
            font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 700;
            color: var(--texto); margin-bottom: 4px;
        }
        .bienvenida h1 span { color: var(--verde-medio); }
        .bienvenida p { color: var(--texto-sub); font-size: 0.9rem; font-weight: 300; }

        /* ── Stats ── */
        .stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 16px; margin-bottom: 28px;
        }
        .stat-card {
            background: var(--blanco); border: 1px solid var(--borde);
            border-radius: 14px; padding: 20px 20px 16px;
            display: flex; flex-direction: column; gap: 6px; transition: box-shadow 0.2s;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
        .stat-label {
            font-size: 0.75rem; font-weight: 600; color: var(--texto-sub);
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .stat-valor { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card.total     .stat-valor { color: var(--texto); }
        .stat-card.pendiente  .stat-valor { color: #f59e0b; }
        .stat-card.confirmada .stat-valor { color: var(--verde-medio); }
        .stat-card.completada .stat-valor { color: #64748b; }
        .stat-icono { font-size: 1.1rem; margin-bottom: 4px; }
        .stat-card.total     .stat-icono { color: #94a3b8; }
        .stat-card.pendiente  .stat-icono { color: #f59e0b; }
        .stat-card.confirmada .stat-icono { color: var(--verde-medio); }
        .stat-card.completada .stat-icono { color: #94a3b8; }
        .stat-card.propuesta_reprogramacion .stat-icono { color: #f59e0b; }

        /* ── Botón agendar ── */
        .btn-agendar {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: var(--verde); color: var(--blanco); text-decoration: none;
            padding: 16px 28px; border-radius: 12px; font-size: 1rem; font-weight: 600;
            letter-spacing: 0.04em; margin-bottom: 32px; transition: all 0.25s;
            box-shadow: 0 4px 14px rgba(26,92,56,0.25); position: relative; overflow: hidden;
        }
        .btn-agendar::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }
        .btn-agendar:hover {
            background: var(--verde-medio); transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(26,92,56,0.3);
        }
        .btn-agendar i { font-size: 1.1rem; }

        /* ── Alertas ── */
        .alerta {
            padding: 14px 18px; border-radius: 10px; margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px; font-size: 0.9rem;
            font-weight: 500; animation: subirEntrar 0.4s ease both;
        }
        .alerta-ok     { background: #e8f5e9; border-left: 4px solid #2e7d32; color: #1e4620; }
        .alerta-cancel { background: #fff3e0; border-left: 4px solid #e65100; color: #7c3a00; }
        .alerta-error  { background: #fee;    border-left: 4px solid #e53e3e; color: #c53030; }

        /* ── Sección citas ── */
        .seccion-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
        }
        .seccion-header h2 {
            font-family: 'Playfair Display', serif; font-size: 1.3rem;
            font-weight: 700; color: var(--texto);
        }
        .filtros { display: flex; gap: 8px; flex-wrap: wrap; }
        .filtro-btn {
            padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--borde);
            background: var(--blanco); color: var(--texto-sub); font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem; font-weight: 500; cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .filtro-btn:hover { border-color: var(--verde-medio); color: var(--verde-medio); }
        .filtro-btn.activo { background: var(--verde); border-color: var(--verde); color: var(--blanco); }

        /* ── Cards citas ── */
        .citas-lista { display: flex; flex-direction: column; gap: 14px; }
        .cita-card {
            background: var(--blanco); border: 1px solid var(--borde); border-radius: 14px;
            padding: 20px 24px; display: grid; grid-template-columns: 1fr auto;
            gap: 16px; align-items: start; transition: box-shadow 0.2s, transform 0.2s;
            animation: subirEntrar 0.4s ease both;
        }
        .cita-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.07); transform: translateY(-1px); }
        .cita-card.estado-confirmada { border-left: 4px solid var(--verde-medio); }
        .cita-card.estado-pendiente  { border-left: 4px solid #f59e0b; }
        .cita-card.estado-cancelada  { border-left: 4px solid #ef4444; opacity: 0.75; }
        .cita-card.estado-finalizada { border-left: 4px solid #94a3b8; }
        .cita-card.estado-no_asistio { border-left: 4px solid #f97316; opacity: 0.75; }
        .cita-card.estado-propuesta_reprogramacion { border-left: 4px solid #f59e0b; opacity: 0.75; }

        .cita-dependencia {
            font-size: 0.72rem; font-weight: 700; color: var(--verde-medio);
            text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;
        }
        .cita-funcionario { font-size: 1rem; font-weight: 600; color: var(--texto); margin-bottom: 6px; }
        .cita-meta { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-bottom: 8px; }
        .cita-meta-item { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--texto-sub); }
        .cita-meta-item i { font-size: 0.8rem; color: #94a3b8; }
        .cita-motivo { font-size: 0.85rem; color: var(--texto-sub); font-weight: 300; line-height: 1.5; margin-top: 4px; }
        .cita-motivo strong { color: var(--texto); font-weight: 500; }
        .cita-nota {
            margin-top: 10px; padding: 10px 12px; background: #f0fdf4; border-radius: 8px;
            border-left: 3px solid var(--verde-claro); font-size: 0.82rem; color: #166534; line-height: 1.5;
        }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px;
            border-radius: 20px; font-size: 0.78rem; font-weight: 600; white-space: nowrap; margin-bottom: 10px;
        }
        .badge-pendiente  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .badge-confirmada { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-cancelada  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-completada { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .badge-noasistio  { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
        .badge-propuesta-reprogramacion { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-contrapropuesta-ciudadano { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .cita-meta-tachado { text-decoration: line-through; opacity: 0.45; }
        .cita-propuesta-nueva {
            display: flex; align-items: center; gap: 6px;
            margin-top: 8px; padding: 8px 12px;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
            font-size: 0.82rem; color: #92400e; font-weight: 600;
        }
        .cita-propuesta-nueva i { color: #f59e0b; flex-shrink: 0; }

        .btn-cancelar {
            background: none; border: 1.5px solid #fecaca; color: #ef4444;
            padding: 7px 14px; border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex;
            align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap;
        }
        .btn-cancelar:hover { background: #fef2f2; border-color: #ef4444; }

        /* ── Paginación ── */
        .paginacion {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }
        .paginacion-info {
            font-size: 0.82rem;
            color: var(--texto-sub);
            font-weight: 400;
        }
        .paginacion-controles {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .pag-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1.5px solid var(--borde);
            background: var(--blanco);
            color: var(--texto-sub);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.18s;
            text-decoration: none;
        }
        .pag-btn:hover:not(:disabled):not(.activo) {
            border-color: var(--verde-medio);
            color: var(--verde-medio);
            background: #f0fdf4;
        }
        .pag-btn.activo {
            background: var(--verde);
            border-color: var(--verde);
            color: var(--blanco);
            cursor: default;
        }
        .pag-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .pag-puntos {
            color: var(--texto-sub);
            padding: 0 4px;
            font-size: 0.85rem;
            line-height: 36px;
        }
        @media (max-width: 480px) {
            .paginacion { justify-content: center; flex-direction: column; align-items: center; gap: 10px; }
            .pag-btn { min-width: 34px; height: 34px; font-size: 0.8rem; }
        }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 60px 20px; background: var(--blanco);
            border: 1px solid var(--borde); border-radius: 14px;
        }
        .empty-state i { font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 16px; }
        .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--texto); margin-bottom: 8px; }
        .empty-state p { color: var(--texto-sub); font-size: 0.9rem; font-weight: 300; margin-bottom: 24px; }
        .empty-state a {
            display: inline-flex; align-items: center; gap: 8px; background: var(--verde);
            color: var(--blanco); text-decoration: none; padding: 11px 22px;
            border-radius: 10px; font-size: 0.9rem; font-weight: 600; transition: background 0.2s;
        }
        .empty-state a:hover { background: var(--verde-medio); }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45);
            z-index: 200; align-items: center; justify-content: center; backdrop-filter: blur(3px);
        }
        .modal-overlay.activo { display: flex; }
        .modal {
            background: var(--blanco); border-radius: 16px; padding: 32px;
            width: 100%; max-width: 440px; margin: 20px; animation: subirEntrar 0.3s ease both;
        }
        .modal h3 { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--texto); margin-bottom: 8px; }
        .modal p { color: var(--texto-sub); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5; }
        .modal textarea {
            width: 100%; padding: 12px 14px; border: 1.5px solid var(--borde); border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: var(--texto);
            resize: vertical; min-height: 90px; outline: none; margin-bottom: 20px; transition: border-color 0.2s;
        }
        .modal textarea:focus { border-color: var(--verde-medio); }
        .modal textarea::placeholder { color: #c0b8ae; }
        .modal-acciones { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-modal-cancelar {
            padding: 10px 20px; border: 1.5px solid var(--borde); background: var(--blanco);
            color: var(--texto-sub); border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-modal-cancelar:hover { border-color: var(--texto-sub); color: var(--texto); }
        .btn-modal-confirmar {
            padding: 10px 20px; background: #ef4444; border: none; color: var(--blanco);
            border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-modal-confirmar:hover { background: #dc2626; }

        /* Modal aviso (festivo / día no hábil) */
        .modal-aviso-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px); }
        .modal-aviso-overlay.activo { display:flex; }
        .modal-aviso-card { background:#fff;border-radius:18px;padding:30px 26px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:subirEntrar .25s ease both; }
        .modal-aviso-icon { font-size:2.3rem;margin-bottom:12px; }
        .modal-aviso-title { font-family:'Playfair Display',serif;font-size:1.15rem;color:var(--texto);margin-bottom:8px; }
        .modal-aviso-msg { font-size:.87rem;color:var(--texto-sub);line-height:1.6;margin-bottom:20px; }
        .btn-aviso-ok { padding:10px 28px;background:var(--verde);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:background .2s; }
        .btn-aviso-ok:hover { background:var(--verde-medio); }

        /* Botones inline de respuesta a propuesta */
        .cita-responder-acciones { display:flex;flex-wrap:wrap;gap:8px;margin-top:10px; }
        .btn-resp { display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.8rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all .2s; }
        .btn-resp-verde { background:var(--verde);color:#fff; }
        .btn-resp-verde:hover { background:var(--verde-medio); }
        .btn-resp-amber { background:#f59e0b;color:#fff; }
        .btn-resp-amber:hover { background:#d97706; }
        /* Botones del modal responder */
        .btn-resp-modal { display:block;width:100%;padding:12px 16px;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.9rem;font-weight:600;text-align:center;text-decoration:none;margin-bottom:8px;cursor:pointer;border:none;transition:all .2s; }
        .btn-resp-modal.verde { background:var(--verde);color:#fff; }
        .btn-resp-modal.verde:hover { background:var(--verde-medio); }
        .btn-resp-modal.amber { background:#fffbeb;border:1.5px solid #fde68a;color:#92400e; }
        .btn-resp-modal.amber:hover { background:#fef3c7; }
        .btn-resp-modal.rojo { background:#fff;border:1.5px solid #fecaca;color:#ef4444; }
        .btn-resp-modal.rojo:hover { background:#fef2f2; }

        @keyframes subirEntrar {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        body::-webkit-scrollbar { width: 8px; }
        body::-webkit-scrollbar-track { background: var(--fondo); }
        body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        body::-webkit-scrollbar-thumb:hover { background: var(--verde-medio); }

        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .main { padding: 24px 16px 48px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .cita-card { grid-template-columns: 1fr; }
            .header-nombre { display: none; }
            .seccion-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .filtros {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            .filtros::-webkit-scrollbar { display: none; }
            .filtro-btn { flex-shrink: 0; }
        }
        @media (max-width: 480px) {
            .header-brand-texto { font-size: 0.6rem; letter-spacing: 0.05em; line-height: 1.3; }
            .header-brand-texto strong { font-size: 0.68rem; letter-spacing: 0.02em; }
            .bienvenida h1 { font-size: 1.35rem; }
            .bienvenida p { font-size: 0.82rem; }
            .stats-grid { gap: 10px; }
            .stat-card { padding: 14px 14px 12px; }
            .stat-valor { font-size: 1.7rem; }
            .btn-agendar { padding: 14px 18px; font-size: 0.92rem; }
            .filtros {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            .filtros::-webkit-scrollbar { display: none; }
            .filtro-btn { flex-shrink: 0; font-size: 0.75rem; padding: 5px 12px; }
            .cita-card { padding: 16px 16px; }
            .cita-funcionario { font-size: 0.95rem; }
            .cita-meta { gap: 10px; }
            .modal { padding: 24px 18px; margin: 12px; }
            .modal-acciones { flex-direction: column; }
            .btn-modal-cancelar, .btn-modal-confirmar { width: 100%; justify-content: center; }
            .btn-cancelar { padding: 6px 10px; font-size: 0.75rem; }
        }
    </style>
</head>
<body>

<header class="header">
    <a href="/dashboard" class="header-brand">
        <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
        <div class="header-brand-texto">
            Alcaldía Municipal<br>
            <strong>Monterrey · Casanare</strong>
        </div>
    </a>
    <div class="header-usuario">
        <div class="avatar"><?= htmlspecialchars($ciudadanoIniciales) ?></div>
        <span class="header-nombre"><?= htmlspecialchars($ciudadanoNombre) ?></span>
        <a href="/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</header>

<main class="main">

    <div class="bienvenida">
        <h1>Hola, <span><?= htmlspecialchars($primerNombre) ?></span></h1>
        <p>Gestiona tus citas con la Alcaldía Municipal de Monterrey</p>
    </div>

    <?php if ($mensajeOk): ?>
    <div class="alerta alerta-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensajeOk) ?></div>
    <?php endif; ?>

    <?php if ($mensajeCancel): ?>
    <div class="alerta alerta-cancel"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($mensajeCancel) ?></div>
    <?php endif; ?>

    <?php if ($errorDb): ?>
    <div class="alerta alerta-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorDb) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icono"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-label">Total citas</div>
            <div class="stat-valor"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card pendiente">
            <div class="stat-icono"><i class="fas fa-clock"></i></div>
            <div class="stat-label">Pendientes</div>
            <div class="stat-valor"><?= $stats['pendiente'] ?></div>
        </div>
        <div class="stat-card confirmada">
            <div class="stat-icono"><i class="fas fa-check-circle"></i></div>
            <div class="stat-label">Confirmadas</div>
            <div class="stat-valor"><?= $stats['confirmada'] ?></div>
        </div>
        <div class="stat-card completada">
            <div class="stat-icono"><i class="fas fa-flag-checkered"></i></div>
            <div class="stat-label">Finalizadas</div>
            <div class="stat-valor"><?= $stats['finalizada'] ?></div>
        </div>
    </div>

    <a href="/agendar" class="btn-agendar">
        <i class="fas fa-plus-circle"></i> Agendar nueva cita
    </a>

    <div class="seccion-header">
        <h2>Mis Citas</h2>
        <div class="filtros">
            <?php
            $filtros = ['todas' => 'Todas', 'pendiente' => 'Pendientes', 'confirmada' => 'Confirmadas',
                        'finalizada' => 'Finalizadas', 'cancelada' => 'Canceladas'];
            foreach ($filtros as $val => $label):
                $url = $val === 'todas' ? '/dashboard' : "/dashboard?estado=$val";
                $activo = $filtroEstado === $val ? 'activo' : '';
            ?>
            <a href="<?= $url ?>" class="filtro-btn <?= $activo ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="citas-lista">
        <?php if (empty($citas)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3><?= $filtroEstado === 'todas' ? 'Aún no tienes citas' : "No tienes citas {$filtroEstado}s" ?></h3>
            <p><?= $filtroEstado === 'todas'
                ? 'Agenda tu primera cita con la Alcaldía de forma rápida y sencilla.'
                : 'Prueba cambiando el filtro o agenda una nueva cita.' ?></p>
            <a href="/agendar"><i class="fas fa-plus"></i> Agendar cita</a>
        </div>
        <?php else: ?>
            <?php foreach ($citas as $i => $cita):
                $badge = badgeEstado($cita['estado']); ?>
            <div class="cita-card estado-<?= $cita['estado'] ?>"
                 style="animation-delay: <?= $i * 0.06 ?>s">
                <div class="cita-info">
                    <div class="cita-dependencia"><?= htmlspecialchars($cita['dependencia']) ?></div>
                    <div class="cita-funcionario"><?= htmlspecialchars($cita['func_nombres'] . ' ' . $cita['func_apellidos']) ?></div>
                    <div class="cita-meta">
                        <span class="cita-meta-item <?= $cita['estado'] === 'propuesta_reprogramacion' ? 'cita-meta-tachado' : '' ?>">
                            <i class="fas fa-calendar"></i>
                            <?= formatFecha($cita['fecha']) ?>
                        </span>
                        <span class="cita-meta-item <?= $cita['estado'] === 'propuesta_reprogramacion' ? 'cita-meta-tachado' : '' ?>">
                            <i class="fas fa-clock"></i>
                            <?= formatHora(substr($cita['hora_inicio'], 0, 5)) ?> – <?= formatHora(substr($cita['hora_fin'], 0, 5)) ?>
                        </span>
                    </div>
                    <?php if ($cita['estado'] === 'propuesta_reprogramacion' && !empty($cita['fecha_propuesta'])): ?>
                    <div class="cita-propuesta-nueva">
                        <i class="fas fa-calendar-alt"></i>
                        Nueva propuesta: <strong><?= formatFecha($cita['fecha_propuesta']) ?> · <?= formatHora(substr($cita['hora_propuesta'], 0, 5)) ?></strong>
                    </div>
                    <?php if (!empty($cita['token_respuesta'])): ?>
                    <div class="cita-responder-acciones">
                        <a href="/cita/responder?token=<?= htmlspecialchars($cita['token_respuesta']) ?>&accion=aceptar&from_dashboard=1"
                           class="btn-resp btn-resp-verde">
                            <i class="fas fa-check"></i> Aceptar
                        </a>
                        <button type="button" class="btn-resp btn-resp-amber btn-abrir-responder"
                                data-token="<?= htmlspecialchars($cita['token_respuesta']) ?>"
                                data-fecha-propuesta="<?= htmlspecialchars($cita['fecha_propuesta']) ?>"
                                data-hora-propuesta="<?= htmlspecialchars(substr($cita['hora_propuesta'], 0, 5)) ?>">
                            <i class="fas fa-calendar-alt"></i> Proponer otra / Rechazar
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="cita-motivo"><strong>Motivo:</strong> <?= htmlspecialchars($cita['motivo']) ?></div>
                    <?php if (!empty($cita['nota_gestion'])): ?>
                    <div class="cita-nota">
                        <i class="fas fa-comment-alt"></i>
                        <strong>Nota:</strong> <?= htmlspecialchars($cita['nota_gestion']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="cita-acciones">
                    <span class="badge <?= $badge['class'] ?>">
                        <i class="fas <?= $badge['icon'] ?>"></i> <?= $badge['label'] ?>
                    </span>
                    <?php if ($cita['estado'] === 'pendiente'): ?>
                    <button class="btn-cancelar" onclick="abrirModalCancelar(<?= $cita['id_cita'] ?>)">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <div class="paginacion" id="paginacion"
         data-pagina="<?= $page ?>"
         data-total-paginas="<?= $totalPaginas ?>"
         data-total="<?= $totalCitas ?>"
         data-per-page="<?= $perPage ?>">
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacion-info">
            Mostrando <?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $totalCitas) ?> de <?= $totalCitas ?> citas
        </div>
        <div class="paginacion-controles">
            <button class="pag-btn" id="pag-anterior" <?= $page <= 1 ? 'disabled' : '' ?>>
                <i class="fas fa-chevron-left"></i>
            </button>
            <?php
            $rango = 2;
            $inicio = max(1, $page - $rango);
            $fin    = min($totalPaginas, $page + $rango);
            if ($inicio > 1): ?><button class="pag-btn" data-p="1">1</button><?php
                if ($inicio > 2): ?><span class="pag-puntos">…</span><?php endif;
            endif;
            for ($p = $inicio; $p <= $fin; $p++): ?>
            <button class="pag-btn <?= $p === $page ? 'activo' : '' ?>" data-p="<?= $p ?>"><?= $p ?></button>
            <?php endfor;
            if ($fin < $totalPaginas):
                if ($fin < $totalPaginas - 1): ?><span class="pag-puntos">…</span><?php endif;
                ?><button class="pag-btn" data-p="<?= $totalPaginas ?>"><?= $totalPaginas ?></button><?php
            endif; ?>
            <button class="pag-btn" id="pag-siguiente" <?= $page >= $totalPaginas ? 'disabled' : '' ?>>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- Modal aviso (festivo / día no hábil) -->
<div class="modal-aviso-overlay" id="modalAviso">
    <div class="modal-aviso-card">
        <div class="modal-aviso-icon" id="aviso-icon-dash"></div>
        <div class="modal-aviso-title" id="aviso-titulo-dash"></div>
        <div class="modal-aviso-msg" id="aviso-msg-dash"></div>
        <button type="button" class="btn-aviso-ok" onclick="document.getElementById('modalAviso').classList.remove('activo')">Entendido</button>
    </div>
</div>

<!-- Modal responder propuesta de reprogramación -->
<div class="modal-overlay" id="modalResponderPropuesta">
    <div class="modal" style="max-width:480px;">
        <h3><i class="fas fa-calendar-alt" style="color:#f59e0b;margin-right:8px;"></i> Propuesta de reprogramación</h3>
        <div id="resp-info-box" style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;font-size:0.88rem;color:#334155;"></div>

        <!-- Opciones -->
        <div id="resp-opciones">
            <a id="btn-resp-aceptar" href="#" class="btn-resp-modal verde">
                <i class="fas fa-check"></i> Aceptar esta fecha
            </a>
            <button type="button" class="btn-resp-modal amber" onclick="mostrarFormCP()">
                <i class="fas fa-calendar-plus"></i> Proponer otra fecha
            </button>
            <button type="button" class="btn-resp-modal rojo" onclick="mostrarConfirmarRechazo()">
                <i class="fas fa-times"></i> Rechazar propuesta
            </button>
        </div>

        <!-- Confirmación de rechazo (oculta) -->
        <div id="resp-confirmar-rechazo" style="display:none;">
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:0.88rem;color:#991b1b;">
                <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>
                El funcionario será notificado para buscar una nueva alternativa. Tu cita vuelve a estado pendiente.
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn-modal-cancelar" onclick="ocultarSubpanel()">← Volver</button>
                <a id="btn-resp-rechazar" href="#" class="btn-modal-confirmar" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                    <i class="fas fa-times"></i> Confirmar rechazo
                </a>
            </div>
        </div>

        <!-- Form contrapropuesta (oculto) -->
        <form id="form-cp-dashboard" method="POST" style="display:none;" action="#">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="from_dashboard" value="1">
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.85rem;font-weight:600;color:#334155;margin-bottom:6px;"><i class="fas fa-calendar"></i> Nueva fecha que propones</label>
                <input type="date" name="contrapropuesta_fecha" id="cp-fecha" required min="<?= date('Y-m-d') ?>"
                       style="width:100%;padding:10px 12px;border:1.5px solid var(--borde);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:0.85rem;font-weight:600;color:#334155;margin-bottom:6px;"><i class="fas fa-clock"></i> Nueva hora que propones</label>
                <select name="contrapropuesta_hora" required
                        style="width:100%;padding:10px 12px;border:1.5px solid var(--borde);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;">
                    <option value="">Selecciona una hora...</option>
                    <?php
                    $cpSlots = ['07:00','07:15','07:30','07:45','08:00','08:15','08:30','08:45',
                                '09:00','09:15','09:30','09:45','10:00','10:15','10:30','10:45',
                                '11:00','11:15','11:30','11:45','14:00','14:15','14:30','14:45',
                                '15:00','15:15','15:30','15:45','16:00','16:15','16:30','16:45'];
                    foreach ($cpSlots as $s):
                        [$h, $m] = explode(':', $s);
                        $hi = (int)$h;
                        $lbl = ($hi > 12 ? $hi - 12 : ($hi ?: 12)) . ':' . $m . ' ' . ($hi >= 12 ? 'PM' : 'AM');
                    ?>
                    <option value="<?= $s ?>"><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn-modal-cancelar" onclick="ocultarSubpanel()">← Volver</button>
                <button type="submit" style="padding:10px 20px;background:#f59e0b;border:none;color:#fff;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.88rem;font-weight:600;cursor:pointer;">
                    <i class="fas fa-paper-plane"></i> Enviar propuesta
                </button>
            </div>
        </form>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--borde);">
            <button type="button" class="btn-modal-cancelar" onclick="cerrarResponderModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal cancelar -->
<div class="modal-overlay" id="modalCancelar">
    <div class="modal">
        <h3>Cancelar cita</h3>
        <p>¿Estás seguro de que deseas cancelar esta cita? Esta acción no se puede deshacer.</p>
        <form method="POST" action="">
            <?= csrf_field() ?>
            <?= tab_id_field() ?>
            <input type="hidden" name="cancelar_id" id="cancelar_id_input">
            <textarea name="motivo_cancelacion" placeholder="Motivo de cancelación (opcional)..."></textarea>
            <div class="modal-acciones">
                <button type="button" class="btn-modal-cancelar" onclick="cerrarModal()">No, mantener</button>
                <button type="submit" class="btn-modal-confirmar">
                    <i class="fas fa-times"></i> Sí, cancelar cita
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalCancelar(id) {
        document.getElementById('cancelar_id_input').value = id;
        document.getElementById('modalCancelar').classList.add('activo');
    }

    function cerrarModal() {
        document.getElementById('modalCancelar').classList.remove('activo');
    }

    // ── Aviso festivo / día no hábil ──
    function mostrarAviso(titulo, mensaje, tipo) {
        var iconos = {
            error:   '<i class="fas fa-calendar-times" style="color:#ef4444;font-size:2.3rem;"></i>',
            warning: '<i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:2.3rem;"></i>',
            info:    '<i class="fas fa-info-circle" style="color:var(--verde);font-size:2.3rem;"></i>'
        };
        document.getElementById('aviso-icon-dash').innerHTML    = iconos[tipo] || iconos.info;
        document.getElementById('aviso-titulo-dash').textContent = titulo;
        document.getElementById('aviso-msg-dash').textContent    = mensaje;
        document.getElementById('modalAviso').classList.add('activo');
    }

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

        document.addEventListener('change', function(e) {
            var input = e.target;
            if (input.id !== 'cp-fecha') return;
            var fecha = input.value;
            if (!fecha) return;

            var festivoNombre = esFestivo(fecha);
            if (festivoNombre) {
                input.value = '';
                mostrarAviso('Día festivo', 'Ese día es festivo (' + festivoNombre + ') y la alcaldía no tiene atención. Por favor elige otra fecha.', 'error');
                return;
            }
            var diasHabiles = conf.diasHabiles || [];
            if (diasHabiles.length) {
                var d = new Date(fecha + 'T00:00:00');
                if (!diasHabiles.includes(d.getDay())) {
                    input.value = '';
                    mostrarAviso('Día no hábil', 'La alcaldía no atiende ese día. Por favor elige una fecha entre los días hábiles.', 'warning');
                }
            }
        });
    })();

    // ── Modal responder propuesta ──
    function abrirResponderPropuesta(token, fechaP, horaP) {
        var urlBase = '/cita/responder?token=' + encodeURIComponent(token) + '&from_dashboard=1';
        document.getElementById('btn-resp-aceptar').href  = urlBase + '&accion=aceptar';
        document.getElementById('btn-resp-rechazar').href = urlBase + '&accion=rechazar';
        document.getElementById('form-cp-dashboard').action = urlBase;

        // Info box
        var d = new Date(fechaP + 'T00:00:00');
        var meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        var fechaFmt = d.getDate() + ' ' + meses[d.getMonth()] + ' ' + d.getFullYear();
        var hArr = horaP.split(':');
        var hh = parseInt(hArr[0]), mm = hArr[1];
        var ampm = hh >= 12 ? 'PM' : 'AM';
        var h12  = hh > 12 ? hh - 12 : (hh === 0 ? 12 : hh);
        document.getElementById('resp-info-box').innerHTML =
            '<strong>Propuesta del funcionario:</strong>&nbsp; ' +
            '<i class="fas fa-calendar" style="color:var(--verde);margin-right:3px;"></i>' + fechaFmt +
            '&nbsp;&nbsp;<i class="fas fa-clock" style="color:var(--verde);margin-right:3px;"></i>' + h12 + ':' + mm + ' ' + ampm;

        // Reset subpaneles
        ocultarSubpanel();
        document.getElementById('modalResponderPropuesta').classList.add('activo');
    }

    function cerrarResponderModal() {
        document.getElementById('modalResponderPropuesta').classList.remove('activo');
    }

    function mostrarFormCP() {
        document.getElementById('resp-opciones').style.display = 'none';
        document.getElementById('resp-confirmar-rechazo').style.display = 'none';
        document.getElementById('form-cp-dashboard').style.display = 'block';
    }

    function mostrarConfirmarRechazo() {
        document.getElementById('resp-opciones').style.display = 'none';
        document.getElementById('form-cp-dashboard').style.display = 'none';
        document.getElementById('resp-confirmar-rechazo').style.display = 'block';
    }

    function ocultarSubpanel() {
        document.getElementById('resp-opciones').style.display = 'block';
        document.getElementById('resp-confirmar-rechazo').style.display = 'none';
        document.getElementById('form-cp-dashboard').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filtros        = document.querySelectorAll('.filtro-btn');
        const citasContainer = document.querySelector('.citas-lista');
        const pagDiv         = document.getElementById('paginacion');
        const statsCards     = document.querySelectorAll('.stat-valor');

        // Estado global de paginación
        let estadoActual  = new URLSearchParams(window.location.search).get('estado') || 'todas';
        let paginaActual  = parseInt(pagDiv.dataset.pagina) || 1;

        document.getElementById('modalResponderPropuesta').addEventListener('click', function(e) {
            if (e.target === this) cerrarResponderModal();
        });
        document.getElementById('modalCancelar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });

        function actualizarStats(stats) {
            if (!stats) return;
            statsCards[0].textContent = stats.total    || 0;
            statsCards[1].textContent = stats.pendiente || 0;
            statsCards[2].textContent = stats.confirmada || 0;
            statsCards[3].textContent = stats.finalizada || 0;
        }

        function renderPaginacion(p) {
            if (!p || p.totalPaginas <= 1) { pagDiv.innerHTML = ''; return; }
            const { paginaActual: pa, totalPaginas: tp, total, perPage } = p;
            const desde = (pa - 1) * perPage + 1;
            const hasta = Math.min(pa * perPage, total);

            let btns = '';
            const rango = 2;
            const inicio = Math.max(1, pa - rango);
            const fin    = Math.min(tp, pa + rango);

            if (inicio > 1) {
                btns += `<button class="pag-btn" data-p="1">1</button>`;
                if (inicio > 2) btns += `<span class="pag-puntos">…</span>`;
            }
            for (let i = inicio; i <= fin; i++) {
                btns += `<button class="pag-btn ${i === pa ? 'activo' : ''}" data-p="${i}">${i}</button>`;
            }
            if (fin < tp) {
                if (fin < tp - 1) btns += `<span class="pag-puntos">…</span>`;
                btns += `<button class="pag-btn" data-p="${tp}">${tp}</button>`;
            }

            pagDiv.innerHTML = `
                <div class="paginacion-info">Mostrando ${desde}–${hasta} de ${total} citas</div>
                <div class="paginacion-controles">
                    <button class="pag-btn" id="pag-anterior" ${pa <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    ${btns}
                    <button class="pag-btn" id="pag-siguiente" ${pa >= tp ? 'disabled' : ''}>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>`;

            // Eventos de los botones de página
            pagDiv.querySelectorAll('[data-p]').forEach(btn => {
                btn.addEventListener('click', () => cargarCitas(estadoActual, parseInt(btn.dataset.p)));
            });
            const btnAnterior = pagDiv.querySelector('#pag-anterior');
            const btnSiguiente = pagDiv.querySelector('#pag-siguiente');
            if (btnAnterior) btnAnterior.addEventListener('click', () => { if (paginaActual > 1) cargarCitas(estadoActual, paginaActual - 1); });
            if (btnSiguiente) btnSiguiente.addEventListener('click', () => { if (paginaActual < tp) cargarCitas(estadoActual, paginaActual + 1); });
        }

        function cargarCitas(estado, pagina = 1) {
            estadoActual = estado;
            paginaActual = pagina;
            citasContainer.innerHTML = '<div style="text-align:center;padding:40px"><i class="fas fa-spinner fa-pulse fa-2x" style="color:var(--verde)"></i><p style="margin-top:10px;color:var(--texto-sub)">Cargando citas...</p></div>';

            fetch(`/ajax/get_citas?estado=${estado}&pagina=${pagina}&tab_id=${window.TAB_ID}`)
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(data => {
                    if (data.citasHtml) citasContainer.innerHTML = data.citasHtml;
                    if (data.stats) actualizarStats(data.stats);
                    renderPaginacion(data.paginacion);
                    // Scroll suave al tope de las citas al cambiar de página
                    if (pagina > 1) citasContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(() => {
                    citasContainer.innerHTML = '<div class="alerta alerta-error"><i class="fas fa-exclamation-circle"></i> Error al cargar las citas. Intente de nuevo.</div>';
                });
        }

        // Filtros: al cambiar filtro siempre vuelve a página 1
        filtros.forEach(filtro => {
            filtro.addEventListener('click', function(e) {
                e.preventDefault();
                filtros.forEach(f => f.classList.remove('activo'));
                this.classList.add('activo');
                const href = this.getAttribute('href');
                const estado = href.includes('estado=') ? href.split('estado=')[1] : 'todas';
                cargarCitas(estado, 1);
                history.pushState({}, '', href);
            });
        });

        // Paginación inicial (botones del render PHP)
        pagDiv.querySelectorAll('[data-p]').forEach(btn => {
            btn.addEventListener('click', () => cargarCitas(estadoActual, parseInt(btn.dataset.p)));
        });
        const btnA = pagDiv.querySelector('#pag-anterior');
        const btnS = pagDiv.querySelector('#pag-siguiente');
        if (btnA) btnA.addEventListener('click', () => { if (paginaActual > 1) cargarCitas(estadoActual, paginaActual - 1); });
        if (btnS) btnS.addEventListener('click', () => { if (paginaActual < parseInt(pagDiv.dataset.totalPaginas)) cargarCitas(estadoActual, paginaActual + 1); });

        // Delegación de clics — btn-cancelar y btn-abrir-responder (funciona con contenido AJAX)
        document.addEventListener('click', function(e) {
            const btnCancel = e.target.closest('.btn-cancelar');
            if (btnCancel) {
                e.preventDefault();
                const citaId = btnCancel.getAttribute('data-cita-id') || btnCancel.getAttribute('onclick')?.match(/\d+/)?.[0];
                if (citaId) abrirModalCancelar(citaId);
                return;
            }
            const btnResp = e.target.closest('.btn-abrir-responder');
            if (btnResp) {
                e.preventDefault();
                abrirResponderPropuesta(
                    btnResp.dataset.token,
                    btnResp.dataset.fechaPropuesta,
                    btnResp.dataset.horaPropuesta
                );
            }
        });

        // Marcar filtro activo al cargar según URL
        const estadoInicial = new URLSearchParams(window.location.search).get('estado') || 'todas';
        if (estadoInicial !== 'todas') {
            filtros.forEach(f => {
                const href = f.getAttribute('href');
                if (href.includes(`estado=${estadoInicial}`)) f.classList.add('activo');
                else f.classList.remove('activo');
            });
        }
    });
</script>

</body>
</html>