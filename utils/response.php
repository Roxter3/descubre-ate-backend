<?php
// ─────────────────────────────────────────────────────────
// RESPUESTAS JSON ESTANDARIZADAS
// Para que TODAS las respuestas de la API tengan el mismo
// formato, sin importar qué ruta las genere.
// ─────────────────────────────────────────────────────────

function responderExito($datos, $codigoHttp = 200) {
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'exito' => true,
        'datos' => $datos,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function responderError($mensaje, $codigoHttp = 400) {
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'exito' => false,
        'error' => $mensaje,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}