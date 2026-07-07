<?php
// ─────────────────────────────────────────────────────────
// ARCHIVO DE PRUEBA — verificar que la conexión y los
// modelos funcionan correctamente.
//
// ⚠️ Este archivo es solo para pruebas en desarrollo.
// Bórralo o protégelo antes de subir el proyecto a producción.
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/models/Categoria.php';

header('Content-Type: application/json');

try {
    $categorias = Categoria::all();

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Conexión exitosa a PostgreSQL',
        'total_categorias' => count($categorias),
        'categorias' => $categorias,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}