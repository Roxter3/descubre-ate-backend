<?php
require_once __DIR__ . '/../utils/jwt.php';

// ─────────────────────────────────────────────────────────
// MIDDLEWARE DE AUTENTICACIÓN
//
// NOTA: en XAMPP/Apache en Windows, getallheaders() a veces no
// captura "Authorization" correctamente — por eso revisamos
// también $_SERVER como respaldo.
// ─────────────────────────────────────────────────────────

function obtenerHeaderAuthorization() {

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return $value;
            }
        }
    }

    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    return '';
}

function requerirAutenticacion() {

    $authHeader = obtenerHeaderAuthorization();

    if (!str_starts_with($authHeader, 'Bearer ')) {
        responderError('No autorizado. Falta el token de acceso.', 401);
    }

    $token = substr($authHeader, 7);

    try {
        $payload = JWT::verificar($token);
        return $payload;

    } catch (Exception $e) {
        responderError('Sesión inválida o expirada. Inicia sesión de nuevo.', 401);
    }
}