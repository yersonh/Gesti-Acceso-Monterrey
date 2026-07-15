<?php
require_once __DIR__ . '/../config/database.php';

class PasswordResetModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Crear un nuevo token para restablecer contraseña
     */
    public function crearToken($usuario_id) {
        // Generar token seguro (64 caracteres hexadecimales)
        $token = bin2hex(random_bytes(32));
        
        // Expira en 24 horas
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Marcar tokens anteriores como usados (opcional, por seguridad)
        $this->marcarTokensComoUsados($usuario_id);
        
        // Insertar nuevo token
        $sql = "INSERT INTO password_resets (usuario_id, token, expires_at) 
                VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $token, $expires_at]);
        
        return $token;
    }
    
    /**
     * Verificar si un token es válido
     */
    public function verificarToken($token) {
        $sql = "SELECT pr.*, u.email
                FROM password_resets pr
                JOIN usuarios u ON pr.usuario_id = u.id_usuario
                WHERE pr.token = ? 
                AND pr.used = false 
                AND pr.expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Marcar token como usado
     */
    public function marcarComoUsado($token) {
        $sql = "UPDATE password_resets SET used = true, updated_at = NOW() 
                WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token]);
    }
    
    /**
     * Marcar todos los tokens de un usuario como usados
     */
    private function marcarTokensComoUsados($usuario_id) {
        $sql = "UPDATE password_resets SET used = true, updated_at = NOW() 
                WHERE usuario_id = ? AND used = false";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuario_id]);
    }
    
    /**
     * Limpiar tokens expirados
     */
    public function limpiarExpirados() {
        $sql = "DELETE FROM password_resets WHERE expires_at < NOW() OR used = true";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}