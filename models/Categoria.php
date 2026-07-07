<?php
require_once __DIR__ . '/Model.php';

// ─────────────────────────────────────────────────────────
// MODELO: Categoria
// Hereda toda la lógica de Model.php (all, find, create,
// update, delete). Solo define la tabla y los campos válidos.
// ─────────────────────────────────────────────────────────

class Categoria extends Model {

    protected static $tabla = 'categorias';

    protected static $camposEditables = [
        'nombre',
        'background',
    ];
}