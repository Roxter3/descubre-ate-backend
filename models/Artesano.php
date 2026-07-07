<?php
require_once __DIR__ . '/Model.php';

class Artesano extends Model {

    protected static $tabla = 'artesanos';

    protected static $camposEditables = [
        'nombre', 'apellidos', 'celular', 'whatsapp', 'correo',
        'direccion', 'especialidad', 'descripcion', 'rna',
        'facebook', 'instagram', 'tiktok',
        'logo', 'foto_presentacion', 'foto_taller',
    ];

    // Productos del artesano
    public static function productos($artesanoId) {
        $pdo  = Database::getConexion();
        $stmt = $pdo->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre, c.background AS categoria_background
             FROM productos p
             JOIN categorias c ON p.categoria_id = c.id
             WHERE p.artesano_id = :id ORDER BY p.id DESC"
        );
        $stmt->execute(['id' => $artesanoId]);
        return $stmt->fetchAll();
    }

    // Fotos de galería del artesano
    public static function fotos($artesanoId) {
        $pdo  = Database::getConexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM fotos_artesano WHERE artesano_id = :id ORDER BY orden ASC, id ASC"
        );
        $stmt->execute(['id' => $artesanoId]);
        return $stmt->fetchAll();
    }

    // Artesano por id con sus fotos de galería incluidas
    public static function findConFotos($id) {
        $pdo  = Database::getConexion();
        $stmt = $pdo->prepare("SELECT * FROM artesanos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $artesano = $stmt->fetch();
        if (!$artesano) return null;

        $artesano['fotos_galeria'] = self::fotos($id);
        return $artesano;
    }
}