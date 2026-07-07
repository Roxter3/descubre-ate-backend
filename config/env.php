<?php
// ─────────────────────────────────────────────────────────
// CARGADOR DE VARIABLES DE ENTORNO
//
// Ahora se llama UNA SOLA VEZ desde index.php (el punto de
// entrada de toda la API), así está disponible en cualquier
// ruta sin importar si esa ruta usa la base de datos o no.
//
// La bandera $yaCargado evita volver a leer el archivo si
// por alguna razón este archivo se incluye más de una vez
// (ej. database.php también lo requiere por seguridad).
// ─────────────────────────────────────────────────────────

function cargarEnv($rutaArchivo) {

    static $yaCargado = false;
    if ($yaCargado) {
        return; // ya se cargó antes en esta misma petición — no repetir
    }

    if (!file_exists($rutaArchivo)) {
        throw new Exception("No se encontró el archivo .env en: $rutaArchivo");
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }

        if (strpos($linea, '=') !== false) {
            list($clave, $valor) = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);

            putenv("$clave=$valor");
            $_ENV[$clave] = $valor;
        }
    }

    $yaCargado = true;
}

cargarEnv(__DIR__ . '/../.env');