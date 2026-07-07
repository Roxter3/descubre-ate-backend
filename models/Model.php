<?php
require_once __DIR__ . '/../config/database.php';

// ─────────────────────────────────────────────────────────
// CLASE BASE — Model
//
// Esta es la clase "padre" que Producto, Artesano y Categoria
// van a extender. Aquí viven los métodos genéricos que se
// repetirían en cada clase: buscar todos, buscar por id,
// eliminar. Así evitamos copiar el mismo código 3 veces.
//
// Cada clase hija solo necesita definir:
//   - $tabla        → el nombre de la tabla en la BD
//   - $camposEditables → qué columnas se pueden guardar
// ─────────────────────────────────────────────────────────

abstract class Model {

    protected static $tabla = '';
    protected static $camposEditables = [];

    // Devuelve TODOS los registros de la tabla
    public static function all() {
        $pdo = Database::getConexion();
        $tabla = static::$tabla;

        $stmt = $pdo->query("SELECT * FROM $tabla ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // Busca un registro por su id — devuelve null si no existe
    public static function find($id) {
        $pdo = Database::getConexion();
        $tabla = static::$tabla;

        $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    // Crea un nuevo registro — $datos es un array asociativo
    // Ej: Producto::create(['title' => 'Cerámica', 'price' => 150])
    public static function create($datos) {
        $pdo = Database::getConexion();
        $tabla = static::$tabla;

        // Solo toma los campos permitidos — evita que alguien
        // intente inyectar columnas no autorizadas
        $datosFiltrados = array_intersect_key_safe($datos, static::$camposEditables);

        $columnas    = implode(', ', array_keys($datosFiltrados));
        $marcadores  = ':' . implode(', :', array_keys($datosFiltrados));

        $sql = "INSERT INTO $tabla ($columnas) VALUES ($marcadores) RETURNING id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($datosFiltrados);

        $fila = $stmt->fetch();
        return $fila['id'];
    }

    // Actualiza un registro existente por id
    public static function update($id, $datos) {
        $pdo = Database::getConexion();
        $tabla = static::$tabla;

        $datosFiltrados = array_intersect_key_safe($datos, static::$camposEditables);

        // Construye "campo1 = :campo1, campo2 = :campo2..."
        $set = implode(', ', array_map(
            fn($campo) => "$campo = :$campo",
            array_keys($datosFiltrados)
        ));

        $datosFiltrados['id'] = $id;

        $sql = "UPDATE $tabla SET $set, actualizado_en = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($datosFiltrados);
    }

    // Elimina un registro por id
    public static function delete($id) {
        $pdo = Database::getConexion();
        $tabla = static::$tabla;

        $stmt = $pdo->prepare("DELETE FROM $tabla WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

// Función auxiliar — filtra $datos dejando solo las claves permitidas
// (similar a array_intersect_key pero más explícita)
function array_intersect_key_safe($datos, $permitidos) {
    return array_intersect_key($datos, array_flip($permitidos));
}