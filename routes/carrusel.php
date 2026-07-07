<?php
// ─────────────────────────────────────────────────────────
// RUTA: /carrusel
// GET    → devuelve los 3 slots (público, lo usa el Hero)
// PUT    → actualiza un slot (protegido, solo admin)
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/../models/Carrusel.php';
require_once __DIR__ . '/../middleware/auth.php';

switch ($metodo) {

    // GET /carrusel — público, lo usa el Hero en el frontend
    case 'GET':
        responderExito(Carrusel::todos());
        break;

    // PUT /carrusel/1 — actualiza el slot de la posición indicada
    case 'PUT':
        requerirAutenticacion();

        if (!$id || !in_array((int)$id, [1, 2, 3])) {
            responderError('Posición inválida. Debe ser 1, 2 o 3.', 400);
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['url'])) {
            responderError('La URL de la imagen es obligatoria.', 422);
        }

        Carrusel::actualizarPosicion((int)$id, $datos);
        responderExito(Carrusel::todos());
        break;

    default:
        responderError('Método no permitido.', 405);
}