<?php
require_once __DIR__ . '/Model.php';

class FotoArtesano extends Model {

    protected static $tabla = 'fotos_artesano';

    protected static $camposEditables = [
        'artesano_id',
        'url',
        'orden',
    ];

    // Devuelve todas las fotos de un artesano ordenadas
    public static function porArtesano($artesanoId) {
        $pdo = Database::getConexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM fotos_artesano WHERE artesano_id = :id ORDER BY orden ASC, id ASC"
        );
        $stmt->execute(['id' => $artesanoId]);
        return $stmt->fetchAll();
    }
}