<?php
require_once __DIR__ . '/env.php';

// ─────────────────────────────────────────────────────────
// CONEXIÓN A LA BASE DE DATOS — PDO
//
// Esta clase es el ÚNICO lugar donde se construye la conexión.
// Usa el patrón "Singleton" — significa que sin importar
// cuántas veces se llame Database::getConexion(), siempre
// devuelve la MISMA conexión ya abierta, en vez de abrir una
// nueva cada vez (esto es más eficiente).
//
// Si el día de mañana el servidor real usa MySQL en lugar de
// Postgres, SOLO se cambia el valor DB_DRIVER en el archivo
// .env — esta clase no necesita modificarse.
// ─────────────────────────────────────────────────────────

class Database {

    private static $conexion = null;

    public static function getConexion() {

        // Si ya existe una conexión abierta, la reutiliza
        if (self::$conexion !== null) {
            return self::$conexion;
        }

        $driver   = getenv('DB_DRIVER');
        $host     = getenv('DB_HOST');
        $port     = getenv('DB_PORT');
        $nombreBD = getenv('DB_NAME');
        $usuario  = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        // Construye el DSN (Data Source Name) según el driver
        $dsn = "$driver:host=$host;port=$port;dbname=$nombreBD";

        try {
            self::$conexion = new PDO($dsn, $usuario, $password, [
                // Lanza excepciones reales en errores SQL — más fácil de depurar
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Devuelve resultados como arrays asociativos (['id' => 1, 'nombre' => '...'])
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Usa prepared statements reales (más seguro contra inyección SQL)
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

        } catch (PDOException $e) {
            // En producción nunca mostramos el error real al usuario final
            // (podría revelar credenciales o estructura de la BD)
            error_log("Error de conexión a la BD: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        return self::$conexion;
    }
}