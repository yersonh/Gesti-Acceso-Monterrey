<?php
// $dependencias is set by AdminController::dependencias()
?>

<div class="page-header">
    <div>
        <h2>Dependencias</h2>
        <p>Unidades organizativas de la alcaldía (sincronizadas desde el Core Institucional)</p>
    </div>
</div>

<!-- Tabla -->
<div class="table-card">
    <?php if (empty($dependencias)): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h4>Sin dependencias registradas</h4>
        <p>Las dependencias se sincronizan automáticamente desde el Core Institucional.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>N° Funcionarios</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($dependencias as $d):
            $dActivo = ($d['activo'] === 't' || $d['activo'] === true || $d['activo'] === 1 || $d['activo'] === '1');
        ?>
        <tr>
            <td style="color:var(--texto-sub);font-size:0.82rem;"><?= (int)$d['id_dependencia'] ?></td>
            <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
            <td style="max-width:200px;font-size:0.82rem;color:var(--texto-sub);">
                <?= htmlspecialchars($d['descripcion'] ?? '—') ?>
            </td>
            <td style="text-align:center;">
                <span class="badge badge-rol"><?= (int)$d['num_funcionarios'] ?></span>
            </td>
            <td>
                <?php if ($dActivo): ?>
                    <span class="badge badge-activo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Activa</span>
                <?php else: ?>
                    <span class="badge badge-inactivo"><i class="fas fa-circle" style="font-size:0.5rem"></i> Inactiva</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
