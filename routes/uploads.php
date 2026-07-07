<?php
// ─────────────────────────────────────────────────────────
// RUTA: /uploads (endpoint de la API)
//
// Recibe un archivo de imagen, lo guarda físicamente en la
// carpeta /media/ del backend (NO /uploads/ — ese nombre está
// reservado para este endpoint y choca con Apache si coinciden),
// y devuelve la URL corta que se guardará en la base de datos.
//
// Protegida — solo admins pueden subir imágenes.
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/../middleware/auth.php';

if ($metodo !== 'POST') {
    responderError('Método no permitido.', 405);
}

requerirAutenticacion();

if (empty($_FILES['imagen'])) {
    responderError('No se recibió ningún archivo.', 422);
}

$archivo = $_FILES['imagen'];

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

// Carpeta física donde se guardan las imágenes — "media", no "uploads"
// (evita el conflicto con la ruta de la API que tiene el mismo nombre)
$carpetaMedia = __DIR__ . '/../media/';
if (!is_dir($carpetaMedia)) {
    mkdir($carpetaMedia, 0755, true);
}

$extension    = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$nombreUnico  = uniqid('img_', true) . '.' . strtolower($extension);
$rutaDestino  = $carpetaMedia . $nombreUnico;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    responderError('No se pudo guardar el archivo en el servidor.', 500);
}

// La URL pública sigue usando /media/ porque así se llama la carpeta real
$urlBase = getenv('APP_URL') ?: 'http://localhost/descubre-ate-backend';
$urlImagen = $urlBase . '/media/' . $nombreUnico;

responderExito([
    'url' => $urlImagen,
    'nombre' => $nombreUnico,
]);