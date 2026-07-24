<?php

class ManualController
{
    private function requireAuth(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
        }
    }

    public function descargar(): void
    {
        $this->requireAuth();

        $archivo = match ($_SESSION['usuario_rol'] ?? '') {
            'Ciudadano'     => 'Manual_Usuario_SCV_Ciudadano.pdf',
            'Funcionario'   => 'Manual_Usuario_SCV_Funcionario.pdf',
            'Recepcionista' => 'Manual_Usuario_SCV_Recepcionista.pdf',
            'Administrador' => 'Manual_Usuario_SCV_Administrador.pdf',
            'Superadmin'    => 'Manual_Usuario_SCV_Superadmin.pdf',
            default         => null,
        };

        $ruta = $archivo ? BASE_PATH . '/docs/manuales-usuario/' . $archivo : null;

        if (!$ruta || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $archivo . '"');
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($ruta);
        exit;
    }
}
