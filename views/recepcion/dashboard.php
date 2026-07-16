<?php
// Vista — la lógica está en RecepcionController::dashboard()
// Variables: $registros, $alertas, $dependencias, $mensaje, $error
?>
<!DOCTYPE html>
<html lang="es">
<head>
<script>(function(){var K="tab_id",id=sessionStorage.getItem(K);if(!id){id=(crypto.randomUUID?crypto.randomUUID().replace(/-/g,"").substr(0,16):Math.random().toString(36).substr(2,16));sessionStorage.setItem(K,id);}window.TAB_ID=id;function fill(){document.querySelectorAll('input[name="tab_id"]').forEach(function(e){e.value=id;});}fill();document.addEventListener("DOMContentLoaded",function(){fill();document.querySelectorAll("form").forEach(function(f){f.addEventListener("submit",fill);});document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href");if(!h||h[0]==="#"||/^[a-z]+:/i.test(h))return;a.href=h+(h.indexOf("?")>-1?"&":"?")+"tab_id="+id;});});})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCV — Recepción</title>
    <link rel="icon" type="image/png" href="/imagenes/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-sub: #64748b;
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --verde: #16a34a;   --verde-bg: #dcfce7;
            --azul: #2563eb;    --azul-bg: #dbeafe;
            --gris: #64748b;    --gris-bg: #f1f5f9;
            --rojo: #dc2626;    --rojo-bg: #fee2e2;
            --ambar: #d97706;   --ambar-bg: #fef3c7;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .header-brand img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .header-brand-texto { color: var(--text); font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; line-height: 1.3; }
        .header-brand-texto strong { display: block; font-size: 0.86rem; color: var(--primary); }
        .header-usuario { display: flex; align-items: center; gap: 16px; }
        .header-titulo { font-weight: 700; font-size: 0.95rem; color: var(--text); display: flex; align-items: center; gap: 8px; }
        .header-titulo i { color: var(--primary); }
        .btn-logout {
            background: var(--gris-bg); border: 1px solid var(--border);
            color: var(--text-sub); padding: 7px 14px; border-radius: 8px;
            font-family: 'Inter', sans-serif; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.15s;
        }
        .btn-logout:hover { background: var(--rojo-bg); color: var(--rojo); }

        .main { max-width: 1400px; margin: 0 auto; padding: 24px 24px 60px; }

        .banner { padding: 12px 16px; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-size: 0.88rem; font-weight: 500; margin-bottom: 16px; }
        .banner-success { background: var(--verde-bg); color: #14532d; border-left: 4px solid var(--verde); }
        .banner-error { background: var(--rojo-bg); color: #7f1d1d; border-left: 4px solid var(--rojo); }

        .alertas-panel { margin-bottom: 20px; }
        .alerta-fila {
            background: var(--ambar-bg); border: 1px solid #fcd34d; border-radius: 10px;
            padding: 12px 16px; display: flex; align-items: center; gap: 12px;
            font-size: 0.85rem; color: #78350f; margin-bottom: 8px;
        }
        .alerta-fila i { color: var(--ambar); font-size: 1rem; flex-shrink: 0; }
        .alerta-fila strong { color: #78350f; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
            padding: 18px 20px; display: flex; align-items: center; gap: 14px;
        }
        .stat-icono { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .stat-card.total .stat-icono   { background: var(--primary-light); color: var(--primary); }
        .stat-card.curso .stat-icono   { background: var(--verde-bg); color: var(--verde); }
        .stat-card.final .stat-icono   { background: var(--gris-bg); color: var(--gris); }
        .stat-card.espon .stat-icono   { background: var(--azul-bg); color: var(--azul); }
        .stat-info h3 { font-size: 1.4rem; font-weight: 800; line-height: 1.1; }
        .stat-info p { color: var(--text-sub); font-size: 0.78rem; font-weight: 600; margin-top: 2px; }

        .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .buscador { position: relative; flex: 1; min-width: 240px; max-width: 380px; }
        .buscador i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-sub); font-size: 0.85rem; }
        .buscador input {
            width: 100%; padding: 10px 14px 10px 36px; border: 1.5px solid var(--border); border-radius: 9px;
            font-family: 'Inter', sans-serif; font-size: 0.87rem; background: var(--surface);
        }
        .buscador input:focus { outline: none; border-color: var(--primary); }

        .btn { padding: 10px 18px; border: none; border-radius: 9px; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: var(--surface); border: 1.5px solid var(--border); color: var(--text); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-sm { padding: 6px 12px; font-size: 0.78rem; }

        .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
            color: var(--text-sub); padding: 12px 16px; border-bottom: 1px solid var(--border); background: #fafbfe;
        }
        tbody td { padding: 12px 16px; font-size: 0.85rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafbff; }

        .persona-nombre { font-weight: 600; color: var(--text); }
        .persona-sub { font-size: 0.76rem; color: var(--text-sub); margin-top: 2px; }

        .badge-estado { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; white-space: nowrap; }
        .badge-estado::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-estado.en_curso    { background: var(--verde-bg); color: var(--verde); }
        .badge-estado.confirmada  { background: var(--azul-bg); color: var(--azul); }
        .badge-estado.finalizada  { background: var(--gris-bg); color: var(--gris); }
        .badge-estado.cancelada   { background: var(--rojo-bg); color: var(--rojo); }
        .badge-estado.no_asistio  { background: var(--ambar-bg); color: var(--ambar); }
        .badge-estado.pendiente,
        .badge-estado.propuesta_reprogramacion,
        .badge-estado.contrapropuesta_ciudadano { background: var(--primary-light); color: var(--primary); }

        .badge-tipo { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; }
        .badge-tipo.cita { background: var(--primary-light); color: var(--primary); }
        .badge-tipo.espontanea { background: var(--azul-bg); color: var(--azul); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-sub); }
        .empty-state i { font-size: 2.4rem; margin-bottom: 12px; opacity: 0.4; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(15,23,42,0.5); display: none;
            align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(3px);
        }
        .modal-overlay.open { display: flex; }
        .modal-card { background: var(--surface); border-radius: 16px; width: 92%; max-width: 640px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .modal-close { background: none; border: none; font-size: 1.1rem; color: var(--text-sub); cursor: pointer; }
        .modal-body { padding: 20px 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 8px;
            font-family: 'Inter', sans-serif; font-size: 0.87rem; background: var(--surface);
        }
        .form-group input:disabled, .form-group select:disabled { background: var(--gris-bg); color: var(--text-sub); }
        .form-group textarea { resize: vertical; min-height: 70px; }
        .grupo-verificar { display: flex; gap: 8px; align-items: flex-end; }
        .grupo-verificar .form-group { flex: 1; margin-bottom: 0; }
        #verif-estado { font-size: 0.78rem; margin-top: 6px; min-height: 16px; }

        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .header { padding: 0 14px; }
            .main { padding: 16px 12px 48px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            table { font-size: 0.78rem; }
            thead th, tbody td { padding: 9px 10px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/recepcion" class="header-brand">
            <img src="/imagenes/logoalcaldia.jpg" alt="Logo Alcaldía">
            <div class="header-brand-texto">
                Alcaldía Municipal<br>
                <strong>Monterrey · Casanare</strong>
            </div>
        </a>
        <div class="header-titulo"><i class="fas fa-desktop"></i> Recepción</div>
        <div class="header-usuario">
            <span style="font-size:0.85rem;font-weight:600;color:var(--text-sub);">
                <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Recepcionista') ?>
            </span>
            <a href="/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <main class="main">

        <?php if ($mensaje): ?>
        <div class="banner banner-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="banner banner-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="alertas-panel" id="alertas-panel">
            <?php foreach ($alertas as $a): ?>
            <div class="alerta-fila">
                <i class="fas fa-bell"></i>
                <span>
                    <strong><?= htmlspecialchars(($a['nombres_ciudadano'] ?? '') . ' ' . ($a['apellidos_ciudadano'] ?? '')) ?></strong>
                    tiene cita a las
                    <strong><?= !empty($a['hora_inicio']) ? date('h:i A', strtotime($a['hora_inicio'])) : '—' ?></strong>
                    con <?= htmlspecialchars(($a['nombres_funcionario'] ?? '') . ' ' . ($a['apellidos_funcionario'] ?? '')) ?>
                    (<?= htmlspecialchars($a['dependencia'] ?? '') ?>)
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="stats-grid" id="stats-grid">
            <div class="stat-card total">
                <div class="stat-icono"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-info"><h3 id="stat-total"><?= count($registros) ?></h3><p>Registros Hoy</p></div>
            </div>
            <div class="stat-card curso">
                <div class="stat-icono"><i class="fas fa-walking"></i></div>
                <div class="stat-info"><h3 id="stat-curso"><?= count(array_filter($registros, fn($r) => $r['estado'] === 'en_curso')) ?></h3><p>En Curso</p></div>
            </div>
            <div class="stat-card final">
                <div class="stat-icono"><i class="fas fa-check-double"></i></div>
                <div class="stat-info"><h3 id="stat-final"><?= count(array_filter($registros, fn($r) => $r['estado'] === 'finalizada')) ?></h3><p>Finalizadas</p></div>
            </div>
            <div class="stat-card espon">
                <div class="stat-icono"><i class="fas fa-user-plus"></i></div>
                <div class="stat-info"><h3 id="stat-espon"><?= count(array_filter($registros, fn($r) => $r['tipo_registro'] === 'espontanea')) ?></h3><p>Espontáneas</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="buscador">
                <i class="fas fa-search"></i>
                <input type="text" id="buscador-input" placeholder="Buscar por nombre o identificación…">
            </div>
            <button class="btn btn-primary" style="margin-left:auto;" onclick="abrirModalEspontanea()">
                <i class="fas fa-user-plus"></i> Visita Espontánea
            </button>
        </div>

        <div class="table-card">
            <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Visitante</th>
                        <th>Identificación</th>
                        <th>Funcionario</th>
                        <th>Dependencia</th>
                        <th>Horario</th>
                        <th>Estado</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="registros-tbody">
                    <?php
                    $etiquetasEstado = [
                        'en_curso' => 'En curso', 'confirmada' => 'Confirmada', 'finalizada' => 'Finalizada',
                        'cancelada' => 'Cancelada', 'no_asistio' => 'No asistió', 'pendiente' => 'Pendiente',
                        'propuesta_reprogramacion' => 'Propuesta enviada', 'contrapropuesta_ciudadano' => 'Contrapropuesta',
                    ];
                    $fmtHora = function ($valor) {
                        if (!$valor) return '—';
                        $ts = strtotime($valor);
                        return $ts ? date('h:i A', $ts) : $valor;
                    };
                    ?>
                    <?php foreach ($registros as $r): ?>
                    <?php
                        $nombre      = trim(($r['nombres_ciudadano'] ?? '') . ' ' . ($r['apellidos_ciudadano'] ?? ''));
                        $funcionario = trim(($r['nombres_funcionario'] ?? '') . ' ' . ($r['apellidos_funcionario'] ?? ''));
                        $horario = $r['tipo_registro'] === 'cita'
                            ? (!empty($r['fecha_cita']) ? date('d/m/Y', strtotime($r['fecha_cita'])) : '') . ' · ' . $fmtHora($r['hora_inicio'] ?? null)
                            : 'Espontánea · ' . $fmtHora($r['hora_ingreso'] ?? null);
                        $estado = $r['estado'];
                    ?>
                    <tr>
                        <td>
                            <div class="persona-nombre"><?= htmlspecialchars($nombre ?: '—') ?></div>
                            <div class="persona-sub">
                                <span class="badge-tipo <?= htmlspecialchars($r['tipo_registro']) ?>">
                                    <?= $r['tipo_registro'] === 'cita' ? 'Cita' : 'Espontánea' ?>
                                </span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars(trim(($r['tipo_identificacion'] ?? '') . ' ' . ($r['numero_identificacion'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars($funcionario ?: '—') ?></td>
                        <td><?= htmlspecialchars($r['dependencia'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($horario) ?></td>
                        <td><span class="badge-estado <?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($etiquetasEstado[$estado] ?? $estado) ?></span></td>
                        <td><?= htmlspecialchars($fmtHora($r['hora_ingreso'] ?? null)) ?></td>
                        <td><?= htmlspecialchars($fmtHora($r['hora_salida'] ?? null)) ?></td>
                        <td>
                            <?php if ($r['tipo_registro'] === 'cita' && $estado === 'confirmada'): ?>
                            <form method="POST" action="/recepcion" style="display:inline;">
                                <?= csrf_field() ?>
                                <?= tab_id_field() ?>
                                <input type="hidden" name="accion" value="registrar_ingreso">
                                <input type="hidden" name="id_cita" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingreso</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($estado === 'en_curso'): ?>
                            <form method="POST" action="/recepcion" style="display:inline;">
                                <?= csrf_field() ?>
                                <?= tab_id_field() ?>
                                <input type="hidden" name="accion" value="registrar_salida">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="tipo" value="<?= htmlspecialchars($r['tipo_registro']) ?>">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sign-out-alt"></i> Salida</button>
                            </form>
                            <?php endif; ?>
                            <?php if (!($r['tipo_registro'] === 'cita' && $estado === 'confirmada') && $estado !== 'en_curso'): ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if (empty($registros)): ?>
            <div class="empty-state" id="tabla-vacia">
                <i class="fas fa-inbox"></i>
                <p>No hay registros para hoy.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Visita Espontánea -->
    <div class="modal-overlay" id="modal-espontanea">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus" style="color:var(--primary);"></i> Visita Espontánea</h3>
                <button class="modal-close" onclick="cerrarModal('modal-espontanea')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="/recepcion" id="form-espontanea">
                <?= csrf_field() ?>
                <?= tab_id_field() ?>
                <input type="hidden" name="accion" value="visita_espontanea">
                <input type="hidden" name="ciudadano_id" id="esp-ciudadano_id" value="0">
                <div class="modal-body">

                    <div class="grupo-verificar">
                        <div class="form-group" style="max-width:110px;">
                            <label>Tipo ID *</label>
                            <select name="tipo_identificacion" id="esp-tipo_identificacion" required>
                                <option value="CC">CC</option>
                                <option value="TI">TI</option>
                                <option value="CE">CE</option>
                                <option value="PA">PA</option>
                                <option value="RC">RC</option>
                                <option value="NIT">NIT</option>
                                <option value="PEP">PEP</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de identificación *</label>
                            <input type="text" name="numero_identificacion" id="esp-numero_identificacion" required maxlength="20">
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="verificarCiudadano()">
                            <i class="fas fa-search"></i> Verificar
                        </button>
                    </div>
                    <div id="verif-estado"></div>

                    <div class="form-row" style="margin-top:14px;">
                        <div class="form-group">
                            <label>Nombres *</label>
                            <input type="text" name="nombres" id="esp-nombres" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos *</label>
                            <input type="text" name="apellidos" id="esp-apellidos" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="esp-telefono">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="esp-email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="text" name="whatsapp" id="esp-whatsapp">
                        </div>
                        <div class="form-group">
                            <label>Proveniencia</label>
                            <input type="text" name="proveniencia" id="esp-proveniencia" placeholder="Ej: Yopal, Villavicencio…">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" id="esp-direccion">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Dependencia *</label>
                            <select name="dependencia_id" id="esp-dependencia_id" required onchange="cargarFuncionarios()">
                                <option value="">Seleccione...</option>
                                <?php foreach ($dependencias as $dep): ?>
                                <option value="<?= (int)$dep['id_dependencia'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Funcionario *</label>
                            <select name="funcionario_id" id="esp-funcionario_id" required disabled>
                                <option value="">Seleccione una dependencia primero</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Motivo de la visita *</label>
                        <textarea name="motivo" id="esp-motivo" required placeholder="Describa el motivo de la visita"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Notas (opcional)</label>
                        <textarea name="notas" id="esp-notas" placeholder="Observaciones adicionales"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modal-espontanea')">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar Visita</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const TAB_ID = window.TAB_ID;

        function abrirModalEspontanea() {
            document.getElementById('modal-espontanea').classList.add('open');
        }
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        // ── Verificar ciudadano existente ────────────────────────────────
        function verificarCiudadano() {
            const numero = document.getElementById('esp-numero_identificacion').value.trim();
            const estadoDiv = document.getElementById('verif-estado');
            if (!numero) { estadoDiv.textContent = ''; return; }

            estadoDiv.textContent = 'Verificando…';
            estadoDiv.style.color = 'var(--text-sub)';

            fetch('/recepcion/verificar?numero=' + encodeURIComponent(numero) + '&tab_id=' + TAB_ID)
                .then(r => r.json())
                .then(data => {
                    const campos = ['nombres', 'apellidos', 'telefono', 'email', 'whatsapp', 'direccion', 'proveniencia'];
                    if (data.encontrado) {
                        const c = data.ciudadano;
                        document.getElementById('esp-ciudadano_id').value = c.id_ciudadano;
                        document.getElementById('esp-tipo_identificacion').value = c.tipo_identificacion;

                        campos.forEach(campo => {
                            const input = document.getElementById('esp-' + campo);
                            const valor = c[campo] || '';
                            input.value = valor;
                            // Bloqueado si ya tiene valor, editable si vacío (para completar datos faltantes)
                            input.disabled = valor !== '';
                        });

                        estadoDiv.innerHTML = '<i class="fas fa-check-circle" style="color:var(--verde);"></i> Ciudadano encontrado — datos cargados';
                        estadoDiv.style.color = 'var(--verde)';
                    } else {
                        document.getElementById('esp-ciudadano_id').value = 0;
                        campos.forEach(campo => {
                            const input = document.getElementById('esp-' + campo);
                            input.disabled = false;
                        });
                        estadoDiv.innerHTML = '<i class="fas fa-info-circle" style="color:var(--primary);"></i> No encontrado — se registrará como nuevo';
                        estadoDiv.style.color = 'var(--primary)';
                    }
                })
                .catch(() => {
                    estadoDiv.textContent = 'Error al verificar. Intenta de nuevo.';
                    estadoDiv.style.color = 'var(--rojo)';
                });
        }

        // ── Cargar funcionarios según dependencia ────────────────────────
        function cargarFuncionarios() {
            const depId = document.getElementById('esp-dependencia_id').value;
            const select = document.getElementById('esp-funcionario_id');

            if (!depId) {
                select.innerHTML = '<option value="">Seleccione una dependencia primero</option>';
                select.disabled = true;
                return;
            }

            select.disabled = true;
            select.innerHTML = '<option value="">Cargando…</option>';

            fetch('/agendar?action=funcionarios&dependencia_id=' + depId + '&tab_id=' + TAB_ID)
                .then(r => r.json())
                .then(funcionarios => {
                    if (!funcionarios.length) {
                        select.innerHTML = '<option value="">Sin funcionarios disponibles</option>';
                        return;
                    }
                    select.innerHTML = '<option value="">Seleccione...</option>' +
                        funcionarios.map(f => `<option value="${f.id_funcionario}">${f.nombres} ${f.apellidos}${f.cargo ? ' — ' + f.cargo : ''}</option>`).join('');
                    select.disabled = false;
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Error al cargar</option>';
                });
        }

        // ── Buscador con debounce ─────────────────────────────────────────
        let terminoActual = '';
        let debounceId;
        document.getElementById('buscador-input').addEventListener('input', function() {
            clearTimeout(debounceId);
            const valor = this.value.trim();
            debounceId = setTimeout(() => {
                terminoActual = valor;
                actualizarDatos();
            }, 300);
        });

        // ── Polling / actualización de tabla, stats y alertas ────────────
        function estadoBadge(estado) {
            const etiquetas = {
                en_curso: 'En curso', confirmada: 'Confirmada', finalizada: 'Finalizada',
                cancelada: 'Cancelada', no_asistio: 'No asistió', pendiente: 'Pendiente',
                propuesta_reprogramacion: 'Propuesta enviada', contrapropuesta_ciudadano: 'Contrapropuesta',
            };
            return `<span class="badge-estado ${estado}">${etiquetas[estado] || estado}</span>`;
        }

        function formatoHora(valor) {
            if (!valor) return '—';
            const d = new Date(valor.includes('T') ? valor : valor.replace(' ', 'T'));
            if (isNaN(d)) return valor;
            return d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
        }

        function renderFila(r) {
            const nombre = `${r.nombres_ciudadano || ''} ${r.apellidos_ciudadano || ''}`.trim();
            const funcionario = `${r.nombres_funcionario || ''} ${r.apellidos_funcionario || ''}`.trim();
            const horario = r.tipo_registro === 'cita'
                ? (r.fecha_cita ? new Date(r.fecha_cita).toLocaleDateString('es-CO') : '') + ' · ' + formatoHora(r.hora_inicio)
                : 'Espontánea · ' + formatoHora(r.hora_ingreso);

            let acciones = '';
            if (r.tipo_registro === 'cita' && r.estado === 'confirmada') {
                acciones += `<form method="POST" action="/recepcion" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="${window.CSRF_TOKEN}">
                    <input type="hidden" name="tab_id" value="${TAB_ID}">
                    <input type="hidden" name="accion" value="registrar_ingreso">
                    <input type="hidden" name="id_cita" value="${r.id}">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingreso</button>
                </form> `;
            }
            if (r.estado === 'en_curso') {
                acciones += `<form method="POST" action="/recepcion" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="${window.CSRF_TOKEN}">
                    <input type="hidden" name="tab_id" value="${TAB_ID}">
                    <input type="hidden" name="accion" value="registrar_salida">
                    <input type="hidden" name="id" value="${r.id}">
                    <input type="hidden" name="tipo" value="${r.tipo_registro}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sign-out-alt"></i> Salida</button>
                </form>`;
            }

            return `<tr>
                <td>
                    <div class="persona-nombre">${nombre || '—'}</div>
                    <div class="persona-sub"><span class="badge-tipo ${r.tipo_registro}">${r.tipo_registro === 'cita' ? 'Cita' : 'Espontánea'}</span></div>
                </td>
                <td>${r.tipo_identificacion || ''} ${r.numero_identificacion || ''}</td>
                <td>${funcionario || '—'}</td>
                <td>${r.dependencia || '—'}</td>
                <td>${horario}</td>
                <td>${estadoBadge(r.estado)}</td>
                <td>${formatoHora(r.hora_ingreso)}</td>
                <td>${formatoHora(r.hora_salida)}</td>
                <td>${acciones || '—'}</td>
            </tr>`;
        }

        function actualizarDatos() {
            const params = new URLSearchParams({ tab_id: TAB_ID });
            if (terminoActual) params.set('q', terminoActual);

            fetch('/ajax/recepcion_poll?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) return;

                    const tbody = document.getElementById('registros-tbody');
                    tbody.innerHTML = data.registros.map(renderFila).join('');

                    const vacio = document.getElementById('tabla-vacia');
                    if (data.registros.length === 0) {
                        if (!vacio) {
                            const div = document.createElement('div');
                            div.className = 'empty-state';
                            div.id = 'tabla-vacia';
                            div.innerHTML = '<i class="fas fa-inbox"></i><p>No hay registros para hoy.</p>';
                            tbody.closest('.table-card').appendChild(div);
                        }
                    } else if (vacio) {
                        vacio.remove();
                    }

                    document.getElementById('stat-total').textContent = data.stats.total;
                    document.getElementById('stat-curso').textContent = data.stats.en_curso;
                    document.getElementById('stat-final').textContent = data.stats.finalizadas;
                    document.getElementById('stat-espon').textContent = data.stats.espontaneas;

                    const panel = document.getElementById('alertas-panel');
                    panel.innerHTML = data.alertas.map(a => `
                        <div class="alerta-fila">
                            <i class="fas fa-bell"></i>
                            <span>
                                <strong>${(a.nombres_ciudadano || '') + ' ' + (a.apellidos_ciudadano || '')}</strong>
                                tiene cita a las
                                <strong>${formatoHora(a.hora_inicio)}</strong>
                                con ${(a.nombres_funcionario || '') + ' ' + (a.apellidos_funcionario || '')}
                                (${a.dependencia || ''})
                            </span>
                        </div>
                    `).join('');
                })
                .catch(err => console.error('Error de polling:', err));
        }

        window.CSRF_TOKEN = document.querySelector('#form-espontanea input[name="csrf_token"]').value;
        setInterval(actualizarDatos, 10000);
    </script>
</body>
</html>
