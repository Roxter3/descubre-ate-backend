<?php
require_once __DIR__ . '/Model.php';

class FotoProducto extends Model {

    protected static $tabla = 'fotos_producto';

    protected static $camposEditables = [
        'producto_id',
        'url',
        'orden',
    ];

    // Devuelve todas las fotos de un producto ordenadas
    public static function porProducto($productoId) {
        $pdo = Database::getConexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM fotos_producto WHERE producto_id = :id ORDER BY orden ASC, id ASC"
        );
        $stmt->execute(['id' => $productoId]);
        return $stmt->fetchAll();
    }
}