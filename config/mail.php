<?php
// config/mail.php

class Mailer {
    private $apiKey;
    private $fromEmail;
    private $fromName;

    private static function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private static function baseUrl(string $path = ''): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/' . ltrim($path, '/');
    }

    public function __construct() {
        $this->apiKey = getenv('BREVO_API_KEY');
        $this->fromEmail = getenv('SMTP_FROM');
        $this->fromName = getenv('SMTP_FROM_NAME');
        
        if (!$this->apiKey || !$this->fromEmail || !$this->fromName) {
            error_log("Faltan variables de entorno para el correo");
        }
    }
    
    /**
     * Enviar correo de confirmación de registro
     */
    public function enviarConfirmacionRegistro($nombre, $email) {
        $subject = "Bienvenido al Sistema de Control de Visitas - Alcaldía de Monterrey";
        
        $htmlContent = $this->getTemplateBienvenida($nombre);
        
        return $this->sendEmail($email, $subject, $htmlContent);
    }
    
    /**
     * Enviar correo de recuperación de contraseña
     */
    public function enviarRecuperacion($nombre, $email, $token) {
        $baseUrl   = (((($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'sistema');
        $resetLink = $baseUrl . '/restablecer?token=' . $token;

        $subject = "Recupera tu acceso - Sistema de Control de Visitas";

        $htmlContent = $this->getTemplateRecuperacion($nombre, $resetLink);

        return $this->sendEmail($email, $subject, $htmlContent);
    }
    
    /**
     * Enviar correo de confirmación de solicitud de cita
     */
    public function enviarConfirmacionSolicitud($nombre, $email, $datosCita) {
        $subject = "Solicitud de cita recibida - Alcaldía de Monterrey";
        
        $htmlContent = $this->getTemplateConfirmacionSolicitud($nombre, $datosCita);
        
        return $this->sendEmail($email, $subject, $htmlContent);
    }
    
    /**
     * Enviar correo genérico
     */
    private function sendEmail($to, $subject, $htmlContent) {
        $url = 'https://api.brevo.com/v3/smtp/email';
        
        $data = [
            'sender' => [
                'name' => $this->fromName,
                'email' => $this->fromEmail
            ],
            'to' => [
                [
                    'email' => $to,
                    'name' => explode('@', $to)[0] // Nombre temporal
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $this->apiKey,
            'content-type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            error_log("✅ Correo enviado a: " . $to);
            return true;
        } else {
            error_log("❌ Error al enviar correo: " . $response);
            return false;
        }
    }
    
    /**
     * Template HTML para correo de bienvenida
     */
    private function getTemplateBienvenida($nombre) {
        $year     = date('Y');
        $nombre   = self::h($nombre);
        $loginUrl = self::h(self::baseUrl('login'));
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'DM Sans', Arial, sans-serif; background-color: #f8f4ed; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <!-- Cabecera con gradiente -->
        <div style="background: linear-gradient(135deg, #1a5c38 0%, #2d7a4f 100%); padding: 32px 24px; text-align: center;">
            <h1 style="color: #e8c97a; font-family: 'Playfair Display', serif; font-size: 28px; margin: 0; font-weight: 700;">
                ¡Bienvenido al Sistema de Control de Visitas!
            </h1>
        </div>
        
        <!-- Contenido -->
        <div style="padding: 32px 24px; color: #2c2c2c;">
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hola <strong style="color: #1a5c38;">{$nombre}</strong>,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Tu cuenta ha sido creada exitosamente en el portal ciudadano de la 
                <strong>Alcaldía Municipal de Monterrey, Casanare</strong>.
            </p>
            
            <div style="background-color: #f8f4ed; border-left: 4px solid #c9a84c; padding: 20px; margin-bottom: 24px; border-radius: 0 8px 8px 0;">
                <p style="font-size: 16px; margin: 0 0 10px 0; font-weight: 600; color: #1a5c38;">
                    ¿Qué puedes hacer ahora?
                </p>
                <ul style="margin: 0; padding-left: 20px; color: #6b6b6b;">
                    <li style="margin-bottom: 8px;">✓ Agendar citas con la alcaldía</li>
                    <li style="margin-bottom: 8px;">✓ Realizar trámites administrativos</li>
                    <li style="margin-bottom: 8px;">✓ Consultar el estado de tus solicitudes</li>
                    <li style="margin-bottom: 8px;">✓ Recibir notificaciones importantes</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 32px 0;">
                <a href="{$loginUrl}"
                   style="background-color: #1a5c38; color: #ffffff; padding: 14px 32px;
                          text-decoration: none; border-radius: 8px; font-weight: 600;
                          display: inline-block; font-size: 16px;">
                    Iniciar Sesión
                </a>
            </div>
            
            <p style="font-size: 14px; color: #6b6b6b; line-height: 1.6; margin-top: 24px; padding-top: 24px; border-top: 1px solid #d8d0c4;">
                Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos:<br>
                📧 nexgoviasolutions@gmail.com<br>
                📞 (+57) 310 6310227
            </p>
        </div>
        
        <!-- Pie de página -->
        <div style="background-color: #1a5c38; padding: 16px; text-align: center;">
            <p style="color: #e8e0d5; font-size: 12px; margin: 0;">
                © {$year} Sistema de Control de Visitas SCV — Alcaldía Municipal de Monterrey, Casanare<br>
                Desarrollado por NexGovIA S.A.S.®
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Template HTML para correo de recuperación de contraseña
     */
    private function getTemplateRecuperacion($nombre, $resetLink) {
        $year      = date('Y');
        $nombre    = self::h($nombre);
        $resetLink = self::h($resetLink);
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'DM Sans', Arial, sans-serif; background-color: #f8f4ed; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <!-- Cabecera -->
        <div style="background: linear-gradient(135deg, #1a5c38 0%, #2d7a4f 100%); padding: 32px 24px; text-align: center;">
            <h1 style="color: #e8c97a; font-family: 'Playfair Display', serif; font-size: 28px; margin: 0; font-weight: 700;">
                Recuperación de Contraseña
            </h1>
        </div>
        
        <!-- Contenido -->
        <div style="padding: 32px 24px; color: #2c2c2c;">
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hola <strong style="color: #1a5c38;">{$nombre}</strong>,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Recibimos una solicitud para restablecer la contraseña de tu cuenta en el 
                <strong>Sistema de Control de Visitas de la Alcaldía de Monterrey</strong>.
            </p>
            
            <div style="background-color: #f8f4ed; border-left: 4px solid #c9a84c; padding: 20px; margin-bottom: 24px; border-radius: 0 8px 8px 0;">
                <p style="font-size: 16px; margin: 0 0 10px 0; font-weight: 600; color: #1a5c38;">
                    Para crear una nueva contraseña, haz clic en el siguiente botón:
                </p>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="{$resetLink}" 
                       style="background-color: #1a5c38; color: #ffffff; padding: 14px 32px; 
                              text-decoration: none; border-radius: 8px; font-weight: 600; 
                              display: inline-block; font-size: 16px;">
                        Restablecer contraseña
                    </a>
                </div>
                <p style="font-size: 14px; color: #6b6b6b; margin: 10px 0 0 0;">
                    Si el botón no funciona, copia y pega este enlace en tu navegador:
                </p>
                <p style="font-size: 13px; color: #2c2c2c; word-break: break-all; background: #ffffff; padding: 10px; border-radius: 4px;">
                    {$resetLink}
                </p>
            </div>
            
            <div style="background-color: #fff5e6; border: 1px solid #c9a84c; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <p style="font-size: 14px; margin: 0; color: #6b6b6b;">
                    ⚠️ Este enlace es válido por <strong>24 horas</strong>. Si no solicitaste este cambio, puedes ignorar este correo.
                </p>
            </div>
            
            <p style="font-size: 14px; color: #6b6b6b; line-height: 1.6; margin-top: 24px; padding-top: 24px; border-top: 1px solid #d8d0c4;">
                ¿Necesitas ayuda? Contáctanos:<br>
                📧 nexgoviasolutions@gmail.com<br>
                📞 (+57) 310 6310227
            </p>
        </div>
        
        <!-- Pie de página -->
        <div style="background-color: #1a5c38; padding: 16px; text-align: center;">
            <p style="color: #e8e0d5; font-size: 12px; margin: 0;">
                © {$year} Sistema de Control de Visitas SCV — Alcaldía Municipal de Monterrey, Casanare<br>
                Desarrollado por NexGovIA S.A.S.®
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Template HTML para correo de confirmación de solicitud
     */
    private function getTemplateConfirmacionSolicitud($nombre, $datosCita) {
        $year            = date('Y');
        $nombre          = self::h($nombre);
        $fechaFormateada = date('d/m/Y', strtotime($datosCita['fecha']));
        $horaFormateada  = date('h:i A', strtotime($datosCita['hora']));
        $dependencia     = self::h($datosCita['dependencia']);
        $funcionario     = self::h($datosCita['funcionario']);
        $motivo          = self::h($datosCita['motivo']);
        $dashUrl         = self::h(self::baseUrl('dashboard'));
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'DM Sans', Arial, sans-serif; background-color: #f8f4ed; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <!-- Cabecera con gradiente -->
        <div style="background: linear-gradient(135deg, #1a5c38 0%, #2d7a4f 100%); padding: 32px 24px; text-align: center;">
            <h1 style="color: #e8c97a; font-family: 'Playfair Display', serif; font-size: 28px; margin: 0; font-weight: 700;">
                ¡Solicitud Recibida!
            </h1>
            <p style="color: #ffffff; margin-top: 10px; font-size: 16px; opacity: 0.9;">
                Alcaldía Municipal de Monterrey - Casanare
            </p>
        </div>
        
        <!-- Contenido -->
        <div style="padding: 32px 24px; color: #2c2c2c;">
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hola <strong style="color: #1a5c38;">{$nombre}</strong>,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hemos recibido tu solicitud de cita correctamente. El estado actual es 
                <strong style="color: #c9a84c;">PENDIENTE DE APROBACIÓN</strong>.
            </p>
            
            <!-- Detalles de la cita -->
            <div style="background-color: #f8f4ed; border-left: 4px solid #c9a84c; padding: 20px; margin-bottom: 24px; border-radius: 0 8px 8px 0;">
                <p style="font-size: 16px; margin: 0 0 15px 0; font-weight: 600; color: #1a5c38;">
                    Detalles de tu solicitud:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b6b6b; width: 120px;"><strong>Dependencia:</strong></td>
                        <td style="padding: 8px 0; color: #2c2c2c;">{$dependencia}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b6b6b;"><strong>Funcionario:</strong></td>
                        <td style="padding: 8px 0; color: #2c2c2c;">{$funcionario}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b6b6b;"><strong>Fecha:</strong></td>
                        <td style="padding: 8px 0; color: #2c2c2c;">{$fechaFormateada}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b6b6b;"><strong>Hora:</strong></td>
                        <td style="padding: 8px 0; color: #2c2c2c;">{$horaFormateada}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b6b6b;"><strong>Motivo:</strong></td>
                        <td style="padding: 8px 0; color: #2c2c2c;">{$motivo}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Información adicional -->
            <div style="background-color: #e8f0fe; border: 1px solid #1a5c38; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <p style="font-size: 14px; margin: 0 0 10px 0; color: #1a5c38; font-weight: 600;">
                    ⏱️ ¿Qué sigue?
                </p>
                <ul style="margin: 0; padding-left: 20px; color: #6b6b6b; font-size: 14px;">
                    <li style="margin-bottom: 6px;">✓ El funcionario revisará tu solicitud</li>
                    <li style="margin-bottom: 6px;">✓ Recibirás un correo de confirmación cuando sea aprobada</li>
                    <li style="margin-bottom: 6px;">✓ Puedes ver el estado en tu dashboard</li>
                </ul>
            </div>
            
            <!-- Botón para ver estado -->
            <div style="text-align: center; margin: 32px 0;">
                <a href="{$dashUrl}"
                   style="background-color: #1a5c38; color: #ffffff; padding: 14px 32px;
                          text-decoration: none; border-radius: 8px; font-weight: 600;
                          display: inline-block; font-size: 16px;">
                    Ver mis citas
                </a>
            </div>
            
            <p style="font-size: 14px; color: #6b6b6b; line-height: 1.6; margin-top: 24px; padding-top: 24px; border-top: 1px solid #d8d0c4;">
                ¿Tienes alguna duda? Contáctanos:<br>
                📧 nexgoviasolutions@gmail.com<br>
                📞 (+57) 310 6310227
            </p>
        </div>
        
        <!-- Pie de página -->
        <div style="background-color: #1a5c38; padding: 16px; text-align: center;">
            <p style="color: #e8e0d5; font-size: 12px; margin: 0;">
                © {$year} Sistema de Control de Visitas SCV — Alcaldía Municipal de Monterrey, Casanare<br>
                Desarrollado por NexGovIA S.A.S.®
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    /**
 * Notificar al funcionario que tiene una nueva solicitud de cita pendiente
 */
public function enviarNuevaSolicitudFuncionario(string $nombreFunc, string $emailFunc, array $datos): bool {
    $subject      = "Nueva solicitud de cita - Alcaldía de Monterrey";
    $year         = date('Y');
    $nombreFunc   = self::h($nombreFunc);
    $ciudadano    = self::h($datos['ciudadano']);
    $dependencia  = self::h($datos['dependencia']);
    $fecha        = self::h(date('d/m/Y', strtotime($datos['fecha'])));
    $hora         = self::h(date('h:i A', strtotime($datos['hora'])));
    $motivo       = self::h($datos['motivo']);
    $urlDashboard = self::h(self::baseUrl('funcionario/dashboard'));

    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
  <div style="background:linear-gradient(135deg,#1a5c38,#2d7a4f);padding:28px 24px;text-align:center;">
    <h1 style="color:#e8c97a;font-family:'Playfair Display',serif;font-size:24px;margin:0;">Nueva Solicitud de Cita</h1>
    <p style="color:rgba(255,255,255,0.85);margin-top:8px;font-size:14px;">Requiere tu revisión</p>
  </div>
  <div style="padding:28px 24px;">
    <p style="font-size:16px;margin-bottom:20px;">Hola <strong style="color:#1a5c38;">{$nombreFunc}</strong>,</p>
    <p style="font-size:15px;color:#334155;margin-bottom:20px;">
      El ciudadano <strong>{$ciudadano}</strong> ha solicitado una cita contigo en <strong>{$dependencia}</strong>.
    </p>
    <div style="background:#f8f4ed;border-left:4px solid #c9a84c;padding:20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
      <p style="margin:6px 0;"><strong>Fecha solicitada:</strong> {$fecha}</p>
      <p style="margin:6px 0;"><strong>Hora:</strong> {$hora}</p>
      <p style="margin:6px 0;"><strong>Dependencia:</strong> {$dependencia}</p>
      <p style="margin:10px 0 0;font-size:14px;color:#64748b;"><strong>Motivo:</strong> {$motivo}</p>
    </div>
    <p style="font-size:14px;color:#64748b;margin-bottom:20px;">
      Por favor ingresa a tu panel para aprobar, rechazar o reprogramar esta solicitud.
    </p>
    <div style="text-align:center;margin:24px 0;">
      <a href="{$urlDashboard}" style="background:#1a5c38;color:#fff;padding:13px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">
        Revisar solicitud
      </a>
    </div>
    <p style="font-size:13px;color:#94a3b8;text-align:center;">
      Esta notificación es automática — no responder a este correo.
    </p>
  </div>
  <div style="background:#1a5c38;padding:14px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;

    return $this->sendEmail($emailFunc, $subject, $html);
}

/**
 * Enviar correo de aprobación de cita
 */
public function enviarConfirmacionAprobacion($nombre, $email, $datosCita) {
    $subject = "Cita Confirmada - Alcaldía de Monterrey";
    
    $fechaFormateada    = date('d/m/Y', strtotime($datosCita['fecha']));
    $horaFormateada     = date('h:i A', strtotime($datosCita['hora']));
    $numeroConfirmacion = sprintf('CIT-%06d', (int)($datosCita['id_cita'] ?? 0));
    
    $htmlContent = $this->getTemplateAprobacion($nombre, $fechaFormateada, $horaFormateada, $datosCita['dependencia'], $numeroConfirmacion);
    
    return $this->sendEmail($email, $subject, $htmlContent);
}

/**
 * Enviar correo de rechazo de cita
 */
public function enviarNotificacionRechazo($nombre, $email, $motivoOriginal, $motivoRechazo) {
    $subject = "Cita No Confirmada - Alcaldía de Monterrey";
    
    $htmlContent = $this->getTemplateRechazo($nombre, $motivoOriginal, $motivoRechazo);
    
    return $this->sendEmail($email, $subject, $htmlContent);
}

/**
 * Template para aprobación
 */
private function getTemplateAprobacion($nombre, $fecha, $hora, $dependencia, $numeroConfirmacion) {
    $year              = date('Y');
    $nombre            = self::h($nombre);
    $dependencia       = self::h($dependencia);
    $numeroConfirmacion = self::h($numeroConfirmacion);
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'DM Sans', Arial, sans-serif; background-color: #f8f4ed; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #1a5c38 0%, #2d7a4f 100%); padding: 32px 24px; text-align: center;">
            <h1 style="color: #e8c97a; font-family: 'Playfair Display', serif; font-size: 28px; margin: 0;">
                ¡Cita Confirmada!
            </h1>
        </div>
        
        <div style="padding: 32px 24px;">
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hola <strong style="color: #1a5c38;">{$nombre}</strong>,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Tu cita ha sido <strong style="color: #1a5c38;">CONFIRMADA</strong> para la siguiente fecha:
            </p>
            
            <div style="background-color: #f8f4ed; border-left: 4px solid #1a5c38; padding: 20px; margin-bottom: 24px;">
                <p style="margin: 5px 0;"><strong>Fecha:</strong> {$fecha}</p>
                <p style="margin: 5px 0;"><strong>Hora:</strong> {$hora}</p>
                <p style="margin: 5px 0;"><strong>Dependencia:</strong> {$dependencia}</p>
                <p style="margin: 5px 0;"><strong>N° Confirmación:</strong> <span style="background: #1a5c38; color: white; padding: 3px 8px; border-radius: 4px;">{$numeroConfirmacion}</span></p>
            </div>
            
            <p style="font-size: 14px; color: #6b6b6b; margin-top: 24px; padding-top: 24px; border-top: 1px solid #d8d0c4;">
                📍 Presentarse 10 minutos antes en la oficina correspondiente.<br>
                📞 (+57) 313 333 62 27
            </p>
        </div>
        
        <div style="background-color: #1a5c38; padding: 16px; text-align: center;">
            <p style="color: #e8e0d5; font-size: 12px; margin: 0;">
                © {$year} Alcaldía Municipal de Monterrey, Casanare
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Template para rechazo
 */
private function getTemplateRechazo($nombre, $motivoOriginal, $motivoRechazo) {
    $year          = date('Y');
    $nombre        = self::h($nombre);
    $motivoOriginal = self::h($motivoOriginal);
    $motivoRechazo  = self::h($motivoRechazo);
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'DM Sans', Arial, sans-serif; background-color: #f8f4ed; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); padding: 32px 24px; text-align: center;">
            <h1 style="color: #fecaca; font-family: 'Playfair Display', serif; font-size: 28px; margin: 0;">
                Cita No Confirmada
            </h1>
        </div>
        
        <div style="padding: 32px 24px;">
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Hola <strong style="color: #1a5c38;">{$nombre}</strong>,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Lamentamos informarte que tu solicitud de cita no pudo ser confirmada por el siguiente motivo:
            </p>
            
            <div style="background-color: #fee; border-left: 4px solid #b91c1c; padding: 20px; margin-bottom: 24px;">
                <p style="margin: 0 0 10px 0;"><strong>Motivo:</strong> {$motivoRechazo}</p>
                <p style="margin: 0; color: #666;"><strong>Solicitud:</strong> {$motivoOriginal}</p>
            </div>
            
            <p style="font-size: 14px; color: #6b6b6b; margin-top: 24px; padding-top: 24px; border-top: 1px solid #d8d0c4;">
                Puedes agendar una nueva cita en nuestro portal.<br>
                📧 nexgoviasolutions@gmail.com
            </p>
        </div>
        
        <div style="background-color: #1a5c38; padding: 16px; text-align: center;">
            <p style="color: #e8e0d5; font-size: 12px; margin: 0;">
                © {$year} Alcaldía Municipal de Monterrey, Casanare
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}
/**
 * Enviar notificación de reprogramación de cita
 */
public function enviarNotificacionReprogramacion($nombre, $email, $datosReprogramacion) {
    $subject = "Cita Reprogramada - Alcaldía de Monterrey";
    
    $htmlContent = $this->getTemplateReprogramacion($nombre, $datosReprogramacion);
    
    return $this->sendEmail($email, $subject, $htmlContent);
}

/**
 * Template para reprogramación
 */
private function getTemplateReprogramacion($nombre, $datos) {
    $year           = date('Y');
    $nombre         = self::h($nombre);
    $funcionario    = self::h($datos['funcionario']);
    $fechaOriginal  = self::h($datos['fecha_original']);
    $horaOriginal   = self::h($datos['hora_original']);
    $nuevaFecha     = self::h($datos['nueva_fecha']);
    $nuevaHora      = self::h($datos['nueva_hora']);
    $dependencia    = self::h($datos['dependencia']);
    $motivo         = self::h($datos['motivo']);
    $urlAceptar     = self::h($datos['url_aceptar']);
    $urlRechazar    = self::h($datos['url_rechazar']);
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
  <div style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:32px 24px;text-align:center;">
    <h1 style="color:#fff;font-size:26px;margin:0;font-weight:700;">Propuesta de Reprogramación</h1>
    <p style="color:rgba(255,255,255,0.9);margin-top:8px;">Tu respuesta es necesaria</p>
  </div>
  <div style="padding:32px 24px;">
    <p style="font-size:16px;">Hola <strong style="color:#1a5c38;">{$nombre}</strong>,</p>
    <p style="font-size:15px;color:#334155;margin:12px 0 24px;">El funcionario <strong>{$funcionario}</strong> ha propuesto una nueva fecha para tu cita:</p>

    <div style="background:#f8f4ed;border-left:4px solid #f59e0b;padding:20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
      <p style="margin:6px 0;"><strong>Fecha anterior:</strong> <span style="text-decoration:line-through;color:#94a3b8;">{$fechaOriginal} · {$horaOriginal}</span></p>
      <p style="margin:6px 0;"><strong>Nueva propuesta:</strong> <span style="color:#f59e0b;font-weight:700;">{$nuevaFecha} · {$nuevaHora}</span></p>
      <p style="margin:6px 0;"><strong>Dependencia:</strong> {$dependencia}</p>
      <p style="margin:10px 0 0;font-size:14px;color:#64748b;"><strong>Motivo:</strong> {$motivo}</p>
    </div>

    <p style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:16px;text-align:center;">¿Qué deseas hacer?</p>

    <table style="width:100%;border-collapse:separate;border-spacing:8px;">
      <tr>
        <td style="text-align:center;">
          <a href="{$urlAceptar}" style="display:block;background:#1a5c38;color:#fff;padding:14px 20px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;">
            Aceptar fecha
          </a>
        </td>
        <td style="text-align:center;">
          <a href="{$urlRechazar}" style="display:block;background:#fff;border:2px solid #fecaca;color:#ef4444;padding:12px 20px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;">
            Rechazar / Proponer otra
        </a>
        </td>
      </tr>
    </table>

    <p style="font-size:12px;color:#94a3b8;text-align:center;margin-top:20px;">
      Este enlace es válido por 48 horas.
    </p>
  </div>
  <div style="background:#1a5c38;padding:16px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Sistema de Control de Visitas — Alcaldía de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;
}
/**
 * Notificar al funcionario que el ciudadano rechazó la reprogramación
 */
public function enviarRechazoReprogramacion(string $nombreFunc, string $emailFunc, array $datos): void {
    $year         = date('Y');
    $nombreFunc   = self::h($nombreFunc);
    $ciudadano    = self::h($datos['ciudadano']);
    $dependencia  = self::h($datos['dependencia']);
    $urlDashboard = self::h($datos['url_dashboard']);
    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#b91c1c,#991b1b);padding:28px 24px;text-align:center;">
    <h1 style="color:#fecaca;font-size:22px;margin:0;">Propuesta Rechazada</h1>
  </div>
  <div style="padding:28px 24px;">
    <p>Hola <strong>{$nombreFunc}</strong>,</p>
    <p style="margin:12px 0;">El ciudadano <strong>{$ciudadano}</strong> ha rechazado tu propuesta de reprogramación para la cita en <strong>{$dependencia}</strong>.</p>
    <p style="margin:16px 0;">Por favor ingresa al panel para gestionar una nueva alternativa:</p>
    <div style="text-align:center;margin:24px 0;">
      <a href="{$urlDashboard}" style="background:#1a5c38;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;">Ir al panel</a>
    </div>
  </div>
  <div style="background:#1a5c38;padding:14px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;
    $this->sendEmail($emailFunc, 'Ciudadano rechazó tu propuesta de reprogramación', $html);
}

/**
 * Enviar link de encuesta de satisfacción al visitante
 */
public function enviarLinkValoracion(string $nombre, string $email, string $link, string $dependencia, string $funcionario = '', string $resultado = ''): bool {
    $year        = date('Y');
    $nombre      = self::h($nombre);
    $dependencia = self::h($dependencia);
    $funcionario = self::h($funcionario);
    $link        = self::h($link);
    $resultado   = self::h($resultado);

    $lineaFuncionario = $funcionario
        ? "<br>Atendido por: <strong>{$funcionario}</strong>"
        : '';

    $bloqueResultado = $resultado !== ''
        ? '<div style="background:#f8f4ed;border-left:4px solid #1a5c38;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:24px;">'
          . '<p style="margin:0 0 6px;font-size:13px;color:#1a5c38;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Resultado de tu visita</p>'
          . '<p style="margin:0;font-size:14px;color:#334155;white-space:pre-line;">' . nl2br($resultado) . '</p>'
          . '</div>'
        : '';

    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
  <div style="background:linear-gradient(135deg,#1a5c38,#2d7a4f);padding:28px 24px;text-align:center;">
    <h1 style="color:#e8c97a;font-family:'Playfair Display',serif;font-size:24px;margin:0;">¿Cómo fue tu visita?</h1>
    <p style="color:rgba(255,255,255,0.85);margin-top:8px;font-size:14px;">Tu opinión nos ayuda a mejorar</p>
  </div>
  <div style="padding:28px 24px;">
    <p style="font-size:16px;margin-bottom:16px;">Hola <strong style="color:#1a5c38;">{$nombre}</strong>,</p>
    <p style="font-size:15px;color:#334155;margin-bottom:24px;">
      Tu visita a <strong>{$dependencia}</strong> — Alcaldía de Monterrey ha finalizado.{$lineaFuncionario}<br>
      Nos gustaría conocer tu experiencia. Solo toma un minuto.
    </p>
    {$bloqueResultado}
    <div style="background:#f8f4ed;border-left:4px solid #c9a84c;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
      <p style="margin:0;font-size:14px;color:#64748b;">⭐ Califica la atención recibida, indica si tu solicitud fue resuelta y deja un comentario opcional.</p>
    </div>
    <div style="text-align:center;margin:28px 0;">
      <a href="{$link}" style="background:#1a5c38;color:#fff;padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block;">
        Calificar mi visita
      </a>
    </div>
    <p style="font-size:12px;color:#94a3b8;text-align:center;margin-top:8px;">
      Este enlace es válido por 7 días y es de un solo uso.
    </p>
  </div>
  <div style="background:#1a5c38;padding:14px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Sistema de Control de Visitas — Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;

    return $this->sendEmail($email, '¿Cómo fue tu visita? — Alcaldía de Monterrey', $html);
}

/**
 * Notificar al funcionario que el ciudadano hizo una contrapropuesta
 */
public function enviarCredencialesTemporales(string $nombre, string $email, string $password, string $rol): bool {
    $year     = date('Y');
    $nombreH  = self::h($nombre);
    $emailH   = self::h($email);
    $passH    = self::h($password);
    $rolH     = self::h($rol);
    $baseUrl  = (((($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'sistema');
    $loginUrl = self::h($baseUrl . '/login');
    $logoUrl  = self::h($baseUrl . '/imagenes/favicon.png');
    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#1a5c38,#2d7a4f);padding:32px 24px;text-align:center;">
    <img src="{$logoUrl}" alt="Logo" style="width:64px;height:64px;border-radius:50%;border:3px solid #c9a84c;margin-bottom:14px;">
    <h1 style="color:#fff;font-size:22px;margin:0;">Tu cuenta ha sido creada</h1>
    <p style="color:rgba(255,255,255,0.8);font-size:14px;margin:8px 0 0;">Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
  <div style="padding:32px 28px;">
    <p style="font-size:16px;color:#1e293b;">Hola, <strong>{$nombreH}</strong>:</p>
    <p style="color:#64748b;font-size:14px;line-height:1.6;">
      El administrador del sistema te ha registrado con el rol de <strong>{$rolH}</strong>.
      A continuación encontrarás tus credenciales de acceso:
    </p>
    <div style="background:#f1f5f9;border-radius:10px;padding:20px 24px;margin:24px 0;border:1px solid #e2e8f0;">
      <p style="margin:0 0 10px;font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.08em;font-weight:700;">Tus credenciales</p>
      <p style="margin:6px 0;font-size:15px;color:#1e293b;"><strong>Usuario (correo):</strong> {$emailH}</p>
      <p style="margin:6px 0;font-size:15px;color:#1e293b;"><strong>Contraseña temporal:</strong>
        <span style="font-family:monospace;background:#fff;border:1px solid #cbd5e1;padding:3px 10px;border-radius:6px;font-size:16px;letter-spacing:.1em;">{$passH}</span>
      </p>
    </div>
    <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:0 8px 8px 0;margin-bottom:24px;">
      <p style="margin:0;font-size:14px;color:#92400e;">
        <strong>⚠ Importante:</strong> Esta contraseña es temporal y tienes <strong>24 horas</strong> para cambiarla
        por una de tu elección. Después de ese plazo recibirás recordatorios de cambio.
      </p>
    </div>
    <div style="text-align:center;margin:28px 0 8px;">
      <a href="{$loginUrl}" style="background:#1a5c38;color:#fff;padding:13px 32px;border-radius:9px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block;">
        Ingresar al sistema →
      </a>
    </div>
  </div>
  <div style="background:#1a5c38;padding:16px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;
    return $this->sendEmail($email, 'Tus credenciales de acceso — Alcaldía de Monterrey', $html);
}

/**
 * Notificar al funcionario que un visitante espontáneo lo está esperando
 */
public function enviarNotificacionVisitaEspontanea(string $nombreFunc, string $emailFunc, array $datos): bool {
    $subject      = "Visita espontánea en recepción - Alcaldía de Monterrey";
    $year         = date('Y');
    $nombreFunc   = self::h($nombreFunc);
    $visitante    = self::h($datos['visitante']);
    $dependencia  = self::h($datos['dependencia']);
    $motivo       = self::h($datos['motivo']);
    $urlDashboard = self::h(self::baseUrl('funcionario/dashboard'));

    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
  <div style="background:linear-gradient(135deg,#1a5c38,#2d7a4f);padding:28px 24px;text-align:center;">
    <h1 style="color:#e8c97a;font-family:'Playfair Display',serif;font-size:24px;margin:0;">Visita Espontánea</h1>
    <p style="color:rgba(255,255,255,0.85);margin-top:8px;font-size:14px;">Un visitante te está esperando en recepción</p>
  </div>
  <div style="padding:28px 24px;">
    <p style="font-size:16px;margin-bottom:20px;">Hola <strong style="color:#1a5c38;">{$nombreFunc}</strong>,</p>
    <p style="font-size:15px;color:#334155;margin-bottom:20px;">
      <strong>{$visitante}</strong> se ha registrado en recepción como visita espontánea para <strong>{$dependencia}</strong>.
    </p>
    <div style="background:#f8f4ed;border-left:4px solid #c9a84c;padding:20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
      <p style="margin:6px 0;"><strong>Dependencia:</strong> {$dependencia}</p>
      <p style="margin:10px 0 0;font-size:14px;color:#64748b;"><strong>Motivo:</strong> {$motivo}</p>
    </div>
    <p style="font-size:14px;color:#64748b;margin-bottom:20px;">
      Por favor dirígete a recepción o ingresa a tu panel para gestionar la visita.
    </p>
    <div style="text-align:center;margin:24px 0;">
      <a href="{$urlDashboard}" style="background:#1a5c38;color:#fff;padding:13px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">
        Ir al panel
      </a>
    </div>
    <p style="font-size:13px;color:#94a3b8;text-align:center;">
      Esta notificación es automática — no responder a este correo.
    </p>
  </div>
  <div style="background:#1a5c38;padding:14px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;

    return $this->sendEmail($emailFunc, $subject, $html);
}

public function enviarContrapropuestaCiudadano(string $nombreFunc, string $emailFunc, array $datos): void {
    $year          = date('Y');
    $nombreFunc    = self::h($nombreFunc);
    $ciudadano     = self::h($datos['ciudadano']);
    $dependencia   = self::h($datos['dependencia']);
    $fechaOriginal = self::h($datos['fecha_original']);
    $horaOriginal  = self::h($datos['hora_original']);
    $nuevaFecha    = self::h($datos['nueva_fecha']);
    $nuevaHora     = self::h($datos['nueva_hora']);
    $urlDashboard  = self::h($datos['url_dashboard']);
    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f8f4ed;margin:0;padding:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
  <div style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:28px 24px;text-align:center;">
    <h1 style="color:#fff;font-size:22px;margin:0;">Nueva Propuesta del Ciudadano</h1>
  </div>
  <div style="padding:28px 24px;">
    <p>Hola <strong>{$nombreFunc}</strong>,</p>
    <p style="margin:12px 0;">El ciudadano <strong>{$ciudadano}</strong> propone una nueva fecha para su cita en <strong>{$dependencia}</strong>:</p>
    <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:16px;border-radius:0 8px 8px 0;margin:20px 0;">
      <p style="margin:4px 0;"><strong>Anterior:</strong> <span style="text-decoration:line-through;color:#94a3b8;">{$fechaOriginal} · {$horaOriginal}</span></p>
      <p style="margin:4px 0;"><strong>Propuesta:</strong> <span style="color:#f59e0b;font-weight:700;">{$nuevaFecha} · {$nuevaHora}</span></p>
    </div>
    <p>Ingresa al panel para aceptar o proponer otra alternativa:</p>
    <div style="text-align:center;margin:24px 0;">
      <a href="{$urlDashboard}" style="background:#1a5c38;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;">Ir al panel</a>
    </div>
  </div>
  <div style="background:#1a5c38;padding:14px;text-align:center;">
    <p style="color:#e8e0d5;font-size:12px;margin:0;">© {$year} Alcaldía Municipal de Monterrey, Casanare</p>
  </div>
</div>
</body></html>
HTML;
    $this->sendEmail($emailFunc, 'El ciudadano propone una nueva fecha de cita', $html);
}
}