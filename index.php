<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/utils/response.php';

set_exception_handler(function ($e) {
    error_log($e->getMessage());
    responderError('Error interno del servidor.', 500);
});

$ruta   = $_GET['ruta'] ?? '';
$ruta   = trim($ruta, '/');
$partes = explode('/', $ruta);

$recurso = $partes[0] ?? '';
$id      = $partes[1] ?? null;
$metodo  = $_SERVER['REQUEST_METHOD'];

switch ($recurso) {

    case 'auth':
        $accionAuth = $partes[1] ?? '';
        if ($accionAuth === 'login') {
            require __DIR__ . '/routes/auth.php';
        } else {
            responderError('Ruta de autenticación no encontrada.', 404);
        }
        break;

    case 'uploads':
        require __DIR__ . '/routes/uploads.php';
        break;

    case 'carrusel':
        require __DIR__ . '/routes/carrusel.php';
        break;

    case 'fotos':
        // /fotos/artesano/:id, /fotos/producto/:id, etc.
        require __DIR__ . '/routes/fotos.php';
        break;

    case 'categorias':
        require __DIR__ . '/routes/categorias.php';
        break;

    case 'productos':
        require __DIR__ . '/routes/productos.php';
        break;

    case 'artesanos':
        require __DIR__ . '/routes/artesanos.php';
        break;

    case '':
        responderExito(['mensaje' => 'API Descubre Ate - Módulo Artesanías']);
        break;

    default:
        responderError('Recurso no encontrado.', 404);
}