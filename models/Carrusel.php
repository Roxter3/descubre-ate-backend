<?php
require_once __DIR__ . '/Model.php';

class Carrusel extends Model {

    protected static $tabla = 'carrusel';

    protected static $camposEditables = [
        'url',
        'titulo',
        'activo',
        'actualizado_en',
    ];

    // Devuelve los 3 slots ordenados por posición
    public static function todos() {
        $pdo  = Database::getConexion();
        $stmt = $pdo->query(
            "SELECT * FROM carrusel ORDER BY posicion ASC"
        );
        return $stmt->fetchAll();
    }

    // Actualiza un slot por su posición (1, 2 o 3)
    public static function actualizarPosicion($posicion, $datos) {
        $pdo  = Database::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE carrusel
             SET url = :url,
                 titulo = :titulo,
                 actualizado_en = CURRENT_TIMESTAMP
             WHERE posicion = :posicion"
        );
        $stmt->execute([
            'url'      => $datos['url'],
            'titulo'   => $datos['titulo'] ?? null,
            'posicion' => $posicion,
        ]);
    }
}