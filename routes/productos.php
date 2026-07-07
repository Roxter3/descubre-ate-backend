<?php
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Artesano.php';
require_once __DIR__ . '/../middleware/auth.php';

switch ($metodo) {

    // GET /productos — lista completa con detalles de categoría y artesano
    // GET /productos/3 — un producto con todos sus detalles
    case 'GET':
        if ($id) {
            // Usa findConDetalles para incluir background y categoriaNombre
            $producto = Producto::findConDetalles($id);
            if (!$producto) {
                responderError('Producto no encontrado.', 404);
            }
            responderExito($producto);
        } else {
            responderExito(Producto::allConDetalles());
        }
        break;

    // POST /productos — crear nuevo (protegido)
    case 'POST':
        requerirAutenticacion();

        $datos = json_decode(file_get_contents('php://input'), true);

        $obligatorios = ['title', 'price', 'image', 'categoria_id', 'artesano_id'];
        foreach ($obligatorios as $campo) {
            if (empty($datos[$campo])) {
                responderError("El campo '$campo' es obligatorio.", 422);
            }
        }

        if (!Categoria::find($datos['categoria_id'])) {
            responderError('La categoría seleccionada no existe.', 422);
        }

        if (!Artesano::find($datos['artesano_id'])) {
            responderError('El artesano seleccionado no existe.', 422);
        }

        $nuevoId = Producto::create($datos);
        responderExito(Producto::findConDetalles($nuevoId), 201);
        break;

    // PUT /productos/3 — actualizar (protegido)
    case 'PUT':
        requerirAutenticacion();

        if (!$id) {
            responderError('Falta el id del producto a actualizar.', 400);
        }

        if (!Producto::find($id)) {
            responderError('Producto no encontrado.', 404);
        }

        $datos = json_decode(file_get_contents('php://input'), true);
        Producto::update($id, $datos);
        responderExito(Producto::findConDetalles($id));
        break;

    // DELETE /productos/3 — eliminar (protegido)
    case 'DELETE':
        requerirAutenticacion();

        if (!$id) {
            responderError('Falta el id del producto a eliminar.', 400);
        }

        if (!Producto::find($id)) {
            responderError('Producto no encontrado.', 404);
        }

        Producto::delete($id);
        responderExito(['mensaje' => 'Producto eliminado correctamente.']);
        break;

    default:
        responderError('Método no permitido.', 405);
}