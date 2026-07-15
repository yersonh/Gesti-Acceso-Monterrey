<?php

declare(strict_types=1);

class PerfilController
{
    private function requireAuth(): void
    {
        $rolesPermitidos = ['Funcionario', 'Recepcionista', 'Administrador'];
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', $rolesPermitidos, true)) {
            redirect('/login');
        }
    }

    public function cambiarContrasena(): void
    {
        $this->requireAuth();

        $mensaje = $_SESSION['flash_mensaje'] ?? '';
        $error   = $_SESSION['flash_error']   ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            try {
                $nueva    = $_POST['nueva_password']    ?? '';
                $confirma = $_POST['confirmar_password'] ?? '';

                if (strlen($nueva) < 8) {
                    throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
                }
                if (!preg_match('/[A-Z]/', $nueva)) {
                    throw new InvalidArgumentException('La contraseña debe tener al menos una mayúscula.');
                }
                if (!preg_match('/[0-9]/', $nueva)) {
                    throw new InvalidArgumentException('La contraseña debe tener al menos un número.');
                }
                if ($nueva !== $confirma) {
                    throw new InvalidArgumentException('Las contraseñas no coinciden.');
                }

                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET password_hash                = :ph,
                        password_temporal            = false,
                        password_temporal_expires_at = NULL
                    WHERE id_usuario = :id
                ");
                $stmt->execute([
                    ':ph' => password_hash($nueva, PASSWORD_DEFAULT),
                    ':id' => $_SESSION['usuario_id'],
                ]);

                Auditoria::registrar('CAMBIAR_CONTRASENA', 'El usuario cambió su contraseña', 'usuarios', (int)$_SESSION['usuario_id']);

                $_SESSION['flash_mensaje'] = 'Contraseña actualizada correctamente.';

                $rol = $_SESSION['usuario_rol'] ?? '';
                if ($rol === 'Administrador') {
                    redirect('/admin/usuarios');
                } else {
                    redirect('/funcionario/dashboard');
                }

            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                error_log('PerfilController::cambiarContrasena - ' . $e->getMessage());
                $error = 'Error interno del servidor.';
            }
        }

        require_once BASE_PATH . '/views/perfil/cambiar_contrasena.php';
    }
}
