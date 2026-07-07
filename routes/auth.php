<?php
// ─────────────────────────────────────────────────────────
// RUTA: /auth/login
// Incluido desde index.php — usa $metodo ya calculada
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

if ($metodo !== 'POST') {
    responderError('Método no permitido.', 405);
}

$datos = json_decode(file_get_contents('php://input'), true);

$usuario  = $datos['usuario'] ?? '';
$password = $datos['password'] ?? '';

if (empty($usuario) || empty($password)) {
    responderError('Usuario y contraseña son obligatorios.', 422);
}

$pdo = Database::getConexion();

$stmt = $pdo->prepare(
    "SELECT id, usuario, password_hash, nombre_completo, activo
     FROM usuarios_admin WHERE usuario = :usuario"
);
$stmt->execute(['usuario' => $usuario]);
$admin = $stmt->fetch();

// Mensaje genérico a propósito — no decimos si el problema fue
// el usuario o la contraseña, para no dar pistas a un atacante
if (!$admin || !password_verify($password, $admin['password_hash'])) {
    responderError('Usuario o contraseña incorrectos.', 401);
}

if (!$admin['activo']) {
    responderError('Este usuario está desactivado.', 403);
}

// Actualiza la fecha del último login
$pdo->prepare("UPDATE usuarios_admin SET ultimo_login = CURRENT_TIMESTAMP WHERE id = :id")
    ->execute(['id' => $admin['id']]);

// Genera el token — solo incluimos lo necesario, nunca el hash
$token = JWT::generar([
    'id'      => $admin['id'],
    'usuario' => $admin['usuario'],
]);

responderExito([
    'token'   => $token,
    'usuario' => [
        'id'              => $admin['id'],
        'usuario'         => $admin['usuario'],
        'nombre_completo' => $admin['nombre_completo'],
    ],
]);