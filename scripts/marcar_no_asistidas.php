<?php
/**
 * marcar_no_asistidas.php — Marca como 'no_asistio' las citas confirmadas
 * de hoy cuya hora de inicio ya pasó por más de 30 minutos sin registrar ingreso.
 *
 * Uso: php scripts/marcar_no_asistidas.php
 *
 * Pensado para correr por cron cada pocos minutos.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Auditoria.php';

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        UPDATE citas SET estado = 'no_asistio', updated_at = NOW()
        WHERE estado = 'confirmada' AND fecha = CURRENT_DATE
          AND (fecha + hora_inicio) < (NOW() - INTERVAL '30 minutes')
    ");
    $stmt->execute();

    $marcadas = $stmt->rowCount();

    if ($marcadas > 0) {
        Auditoria::registrar(
            'MARCAR_NO_ASISTIDAS',
            "Sistema marcó automáticamente {$marcadas} cita(s) como no_asistio",
            'citas'
        );
    }

    echo "Marcadas: {$marcadas}\n";

} catch (Exception $e) {
    error_log('marcar_no_asistidas.php ERROR: ' . $e->getMessage());
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
