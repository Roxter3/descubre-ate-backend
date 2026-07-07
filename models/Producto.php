<?php
require_once __DIR__ . '/Model.php';

class Producto extends Model {

    protected static $tabla = 'productos';

    protected static $camposEditables = [
        'title', 'price', 'description', 'image',
        'categoria_id', 'artesano_id',
        'tecnica', 'material', 'medidas',
    ];

    // Todos los productos con detalles de categoría y artesano
    public static function allConDetalles() {
        $pdo = Database::getConexion();
        $sql = "
            SELECT p.*,
                   c.nombre     AS categoria_nombre,
                   c.background AS categoria_background,
                   a.nombre     AS artesano_nombre,
                   a.apellidos  AS artesano_apellidos
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN artesanos  a ON p.artesano_id  = a.id
            ORDER BY p.id DESC
        ";
        return $pdo->query($sql)->fetchAll();
    }

    // Un producto con detalles + sus fotos de galería
    public static function findConDetalles($id) {
        $pdo  = Database::getConexion();
        $sql  = "
            SELECT p.*,
                   c.nombre     AS categoria_nombre,
                   c.background AS categoria_background,
                   a.nombre     AS artesano_nombre,
                   a.apellidos  AS artesano_apellidos
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN artesanos  a ON p.artesano_id  = a.id
            WHERE p.id = :id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $producto = $stmt->fetch();
        if (!$producto) return null;

        // Incluye las fotos de galería en la respuesta
        $stmtFotos = $pdo->prepare(
            "SELECT * FROM fotos_producto WHERE producto_id = :id ORDER BY orden ASC, id ASC"
        );
        $stmtFotos->execute(['id' => $id]);
        $producto['fotos'] = $stmtFotos->fetchAll();

        return $producto;
    }
}