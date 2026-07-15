<?php
require_once __DIR__ . '/../config/database.php';

class Dependencia {

    /**
     * Retorna todas las dependencias activas ordenadas por nombre.
     */
    public static function getActivas(): array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->query("
                SELECT id_dependencia, nombre
                FROM dependencias
                WHERE activo = true
                ORDER BY nombre
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Dependencia::getActivas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna una dependencia por su ID.
     */
    public static function getPorId(int $id): ?array {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT id_dependencia, nombre, descripcion
                FROM dependencias
                WHERE id_dependencia = :id AND activo = true
            ");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            error_log('Dependencia::getPorId - ' . $e->getMessage());
            return null;
        }
    }
}