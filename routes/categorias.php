<?php
// ─────────────────────────────────────────────────────────
// RUTA: /categorias
//
// Este archivo es incluido (require) desde index.php, así que
// ya tiene disponibles las variables $id y $metodo calculadas
// allá. No es un archivo independiente que se acceda directo.
// ─────────────────────────────────────────────────────────

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../middleware/auth.php';

switch ($metodo) {

    // ───── GET /categorias o GET /categorias/3 ─────
    // Pública — la vista pública del sitio necesita leer sin login
    case 'GET':
        if ($id) {
            $categoria = Categoria::find($id);
            if (!$categoria) {
                responderError('Categoría no encontrada.', 404);
            }
            responderExito($categoria);
        } else {
            responderExito(Categoria::all());
        }
        break;

    // ───── POST /categorias ─────
    // Protegida — requiere estar logueado como admin
    case 'POST':
        requerirAutenticacion();

        $datos = json_decode(file_get_contents('php://input'), true);

        // Validación básica de campos obligatorios
        if (empty($datos['nombre']) || empty($datos['background'])) {
            responderError('Los campos nombre y background son obligatorios.', 422);
        }

        try {
            $nuevoId = Categoria::create($datos);
            responderExito(Categoria::find($nuevoId), 201);

        } catch (PDOException $e) {
            // Código 23505 = violación de restricción UNIQUE en Postgres
            if ($e->getCode() === '23505') {
                responderError('Ya existe una categoría con ese nombre.', 409);
            }
            throw $e; // cualquier otro error sigue su curso normal
        }
        break;

    // ───── PUT /categorias/3 ─────
    // Protegida
    case 'PUT':
        requerirAutenticacion();

        if (!$id) {
            responderError('Falta el id de la categoría a actualizar.', 400);
        }

        $existente = Categoria::find($id);
        if (!$existente) {
            responderError('Categoría no encontrada.', 404);
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        try {
            Categoria::update($id, $datos);
            responderExito(Categoria::find($id));

        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                responderError('Ya existe otra categoría con ese nombre.', 409);
            }
            throw $e;
        }
        break;

    // ───── DELETE /categorias/3 ─────
    // Protegida
    case 'DELETE':
        requerirAutenticacion();

        if (!$id) {
            responderError('Falta el id de la categoría a eliminar.', 400);
        }

        $existente = Categoria::find($id);
        if (!$existente) {
            responderError('Categoría no encontrada.', 404);
        }

        try {
            Categoria::delete($id);
            responderExito(['mensaje' => 'Categoría eliminada correctamente.']);
        } catch (PDOException $e) {
            // Esto ocurre si hay productos/artesanos usando esta categoría
            // (la base de datos lo bloquea por el FOREIGN KEY ON DELETE RESTRICT)
            responderError(
                'No se puede eliminar: hay productos o artesanos usando esta categoría.',
                409
            );
        }
        break;

    default:
        responderError('Método no permitido.', 405);
}