<?php
// ─────────────────────────────────────────────────────────
// CONFIGURACIÓN CORS
//
// CORS = Cross-Origin Resource Sharing. Por seguridad, los
// navegadores bloquean que una página en un dominio (tu React)
// llame a una API en otro dominio/puerto, A MENOS que el
// servidor le diga explícitamente "este origen tiene permiso".
//
// ⚠️ IMPORTANTE: "*" (cualquier origen) NO funciona cuando la
// petición incluye headers como "Authorization" — los navegadores
// modernos lo bloquean por seguridad. Por eso reflejamos el
// origen específico de la petición en vez de usar el comodín.
//
// Cuando el proyecto se suba al servidor municipal real, cambia
// la lista $origenesPermitidos por el dominio EXACTO del portal
// (ej. "https://descubreate.gob.pe").
// ─────────────────────────────────────────────────────────

$origenesPermitidos = [
    'http://localhost:5173',  // Vite (React) en desarrollo local
    'http://localhost:5174',  // Vite a veces usa este puerto si 5173 está ocupado
    'http://localhost:3000',  // por si usas otro puerto común
    'http://127.0.0.1:5173',
    'http://127.0.0.1:5174',
];

$origenSolicitante = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origenSolicitante, $origenesPermitidos)) {
    header("Access-Control-Allow-Origin: $origenSolicitante");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// El navegador a veces envía una petición "OPTIONS" antes de la
// petición real, solo para preguntar permisos. Respondemos vacío.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}