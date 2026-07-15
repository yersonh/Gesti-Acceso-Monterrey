<?php

class ValoracionController {

    public function formulario(): void {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            http_response_code(404);
            require_once BASE_PATH . '/views/valoracion/invalido.php';
            return;
        }

        $model      = new ValoracionModel();
        $valoracion = $model->getByToken($token);

        if (!$valoracion) {
            require_once BASE_PATH . '/views/valoracion/invalido.php';
            return;
        }

        $datosVisita = $model->getDatosVisita(
            (int)$valoracion['visita_id'],
            $valoracion['tipo_visita']
        );

        $error = '';
        $exito = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $estrellas   = (int)($_POST['estrellas']   ?? 0);
            $solucionado = ($_POST['solucionado'] ?? '') === 'si';
            $comentario  = trim($_POST['comentario'] ?? '');

            if ($estrellas < 1 || $estrellas > 5) {
                $error = 'Por favor selecciona una calificación de 1 a 5 estrellas.';
            } elseif (!isset($_POST['solucionado'])) {
                $error = 'Por favor indica si tu motivo fue solucionado.';
            } else {
                $guardado = $model->responder($token, $estrellas, $solucionado, $comentario);
                if ($guardado) {
                    // ── Auditoría ──────────────────────────────────
                    $nombreVisitante = trim(
                        ($datosVisita['nombres']   ?? '') . ' ' .
                        ($datosVisita['apellidos'] ?? '')
                    );
                    Auditoria::registrar(
                        'ENVIAR_VALORACION',
                        "Valoración enviada por {$nombreVisitante}: " .
                        "{$estrellas} estrellas, solucionado: " .
                        ($solucionado ? 'Sí' : 'No') .
                        (empty($comentario) ? '' : ", comentario: {$comentario}"),
                        'valoraciones',
                        (int)$valoracion['visita_id']
                    );

                    $exito = true;
                } else {
                    $error = 'No se pudo guardar tu valoración. El enlace puede haber expirado.';
                }
            }
        }

        if ($exito) {
            require_once BASE_PATH . '/views/valoracion/gracias.php';
        } else {
            require_once BASE_PATH . '/views/valoracion/formulario.php';
        }
    }
}