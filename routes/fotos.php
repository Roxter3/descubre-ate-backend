<?php
// ─────────────────────────────────────────────────────────
// RUTAS DE FOTOS DE GALERÍA
//
// Maneja las fotos adicionales de artesanos y productos.
// Todas las operaciones de escritura requieren autenticación.
//
// Rutas disponibles:
//   GET    /fotos/artesano/:id        → listar fotos del artesano
//   POST   /fotos/artesano/:id        → subir foto al artesano
//   DELETE /fotos/artesano-foto/:id   → eliminar una foto de artesano
//   GET    /fotos/producto/:id        → listar fotos del producto
//   POST   /fotos/producto/:id        → subir foto al producto
//   DELETE /fotos/producto-foto/:id   → eliminar una foto de producto
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/../models/FotoArtesano.php';
require_once __DIR__ . '/../models/FotoProducto.php';
require_once __DIR__ . '/../models/Artesano.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../middleware/auth.php';

// $partes viene de index.php:
// /fotos/artesano/3  → partes = ['fotos', 'artesano', '3']
// /fotos/producto/5  → partes = ['fotos', 'producto', '5']
$subRecurso = $partes[1] ?? '';   // 'artesano', 'producto', 'artesano-foto', 'producto-foto'
$subId      = $partes[2] ?? null; // id del artesano/producto o de la foto

// ─────────────────────────────────────────────────────────
// FOTOS DE ARTESANO
// ─────────────────────────────────────────────────────────
if ($subRecurso === 'artesano') {

    if ($metodo === 'GET') {
        // Lista las fotos del artesano
        if (!$subId) responderError('Falta el id del artesano.', 400);
        responderExito(FotoArtesano::porArtesano($subId));

    } elseif ($metodo === 'POST') {
        // Sube una foto nueva al artesano
        requerirAutenticacion();

        if (!$subId) responderError('Falta el id del artesano.', 400);
        if (!Artesano::find($subId)) responderError('Artesano no encontrado.', 404);
        if (empty($_FILES['imagen'])) responderError('No se recibió ningún archivo.', 422);

        $url = subirImagen($_FILES['imagen']);

        // Calcula el siguiente orden
        $fotos   = FotoArtesano::porArtesano($subId);
        $orden   = count($fotos);

        $nuevoId = FotoArtesano::create([
            'artesano_id' => $subId,
            'url'         => $url,
            'orden'       => $orden,
        ]);

        responderExito(FotoArtesano::find($nuevoId), 201);

    } else {
        responderError('Método no permitido.', 405);
    }

// ─────────────────────────────────────────────────────────
// ELIMINAR FOTO DE ARTESANO
// ─────────────────────────────────────────────────────────
} elseif ($subRecurso === 'artesano-foto') {

    if ($metodo === 'DELETE') {
        requerirAutenticacion();
        if (!$subId) responderError('Falta el id de la foto.', 400);
        $foto = FotoArtesano::find($subId);
        if (!$foto) responderError('Foto no encontrada.', 404);

        // Intenta borrar el archivo físico del servidor
        $rutaFisica = __DIR__ . '/../media/' . basename($foto['url']);
        if (file_exists($rutaFisica)) unlink($rutaFisica);

        FotoArtesano::delete($subId);
        responderExito(['mensaje' => 'Foto eliminada correctamente.']);
    } else {
        responderError('Método no permitido.', 405);
    }

// ─────────────────────────────────────────────────────────
// FOTOS DE PRODUCTO
// ─────────────────────────────────────────────────────────
} elseif ($subRecurso === 'producto') {

    if ($metodo === 'GET') {
        if (!$subId) responderError('Falta el id del producto.', 400);
        responderExito(FotoProducto::porProducto($subId));

    } elseif ($metodo === 'POST') {
        requerirAutenticacion();

        if (!$subId) responderError('Falta el id del producto.', 400);
        if (!Producto::find($subId)) responderError('Producto no encontrado.', 404);
        if (empty($_FILES['imagen'])) responderError('No se recibió ningún archivo.', 422);

        $url = subirImagen($_FILES['imagen']);

        $fotos   = FotoProducto::porProducto($subId);
        $orden   = count($fotos);

        $nuevoId = FotoProducto::create([
            'producto_id' => $subId,
            'url'         => $url,
            'orden'       => $orden,
        ]);

        responderExito(FotoProducto::find($nuevoId), 201);

    } else {
        responderError('Método no permitido.', 405);
    }

// ─────────────────────────────────────────────────────────
// ELIMINAR FOTO DE PRODUCTO
// ─────────────────────────────────────────────────────────
} elseif ($subRecurso === 'producto-foto') {

    if ($metodo === 'DELETE') {
        requerirAutenticacion();
        if (!$subId) responderError('Falta el id de la foto.', 400);
        $foto = FotoProducto::find($subId);
        if (!$foto) responderError('Foto no encontrada.', 404);

        $rutaFisica = __DIR__ . '/../media/' . basename($foto['url']);
        if (file_exists($rutaFisica)) unlink($rutaFisica);

        FotoProducto::delete($subId);
        responderExito(['mensaje' => 'Foto eliminada correctamente.']);
    } else {
        responderError('Método no permitido.', 405);
    }

} else {
    responderError('Sub-ruta de fotos no encontrada.', 404);
}

// ─────────────────────────────────────────────────────────
// Función auxiliar reutilizada por artesano y producto
// ─────────────────────────────────────────────────────────
function subirImagen($archivo) {
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        responderError('Error al recibir el archivo.', 422);
    }

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $tipoReal = mime_content_type($archivo['tmp_name']);
    if (!in_array($tipoReal, $tiposPermitidos)) {
        responderError('Tipo de archivo no permitido. Solo JPG, PNG, WEBP o GIF.', 422);
    }

    $limiteBytes = 5 * 1024 * 1024;
    if ($archivo['size'] > $limiteBytes) {
        responderError('El archivo supera el límite de 5MB.', 422);
    }

    $carpetaMedia = __DIR__ . '/../media/';
    if (!is_dir($carpetaMedia)) mkdir($carpetaMedia, 0755, true);

    $extension   = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreUnico = uniqid('img_', true) . '.' . strtolower($extension);
    $rutaDestino = $carpetaMedia . $nombreUnico;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        responderError('No se pudo guardar el archivo en el servidor.', 500);
    }

    $urlBase = getenv('APP_URL') ?: 'http://localhost/descubre-ate-backend';
    return $urlBase . '/media/' . $nombreUnico;
}