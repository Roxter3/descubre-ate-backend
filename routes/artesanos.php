<?php
require_once __DIR__ . '/../models/Artesano.php';
require_once __DIR__ . '/../middleware/auth.php';

$subRecurso = $partes[2] ?? null;

switch ($metodo) {

    case 'GET':
        if ($id && $subRecurso === 'productos') {
            $artesano = Artesano::find($id);
            if (!$artesano) responderError('Artesano no encontrado.', 404);
            responderExito(Artesano::productos($id));

        } elseif ($id) {
            $artesano = Artesano::findConFotos($id);
            if (!$artesano) responderError('Artesano no encontrado.', 404);
            responderExito($artesano);

        } else {
            responderExito(Artesano::all());
        }
        break;

    case 'POST':
        requerirAutenticacion();
        $datos = json_decode(file_get_contents('php://input'), true);

        $obligatorios = ['nombre', 'apellidos', 'celular', 'whatsapp', 'foto_presentacion', 'especialidad'];
        foreach ($obligatorios as $campo) {
            if (empty($datos[$campo])) {
                responderError("El campo '$campo' es obligatorio.", 422);
            }
        }

        $nuevoId = Artesano::create($datos);
        responderExito(Artesano::find($nuevoId), 201);
        break;

    case 'PUT':
        requerirAutenticacion();
        if (!$id) responderError('Falta el id del artesano a actualizar.', 400);
        if (!Artesano::find($id)) responderError('Artesano no encontrado.', 404);

        $datos = json_decode(file_get_contents('php://input'), true);
        Artesano::update($id, $datos);
        responderExito(Artesano::findConFotos($id));
        break;

    case 'DELETE':
        requerirAutenticacion();
        if (!$id) responderError('Falta el id del artesano a eliminar.', 400);
        if (!Artesano::find($id)) responderError('Artesano no encontrado.', 404);

        try {
            Artesano::delete($id);
            responderExito(['mensaje' => 'Artesano eliminado correctamente.']);
        } catch (PDOException $e) {
            responderError('No se puede eliminar: este artesano tiene productos registrados.', 409);
        }
        break;

    default:
        responderError('Método no permitido.', 405);
}