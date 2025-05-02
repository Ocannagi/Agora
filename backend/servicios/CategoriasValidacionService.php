<?php

use Utilidades\Output;
use Utilidades\Input;

class CategoriasValidacionService extends ValidacionServiceBase
{
    private static $instancia = null;

    private function __construct() {}

    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $categoria)
    {
        if (!($categoria instanceof CategoriaCreacionDTO) && !($categoria instanceof CategoriaDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Categoria', datos: get_object_vars($categoria));
        $this->validarDescripcion($categoria->catDescripcion);

        if ($categoria instanceof CategoriaDTO) {
            $this->validarExisteCategoriaModificar($categoria->catId, $linkExterno);
            $this->validarSiYaFueRegistrado($categoria->catDescripcion, $linkExterno, $categoria->catId);
        } else {
            $this->validarSiYaFueRegistrado($categoria->catDescripcion, $linkExterno);

        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 50))
            Output::outputError(400, 'La Descripción de la categoría debe ser un string de al menos un caracter y un máximo de 50.');
    }

    private function validarExisteCategoriaModificar(int $catId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query:"SELECT 1 FROM categoria WHERE catId='$catId' AND catFechaBaja IS NULL", msg: 'obtener una categoría por id para modificar'))
            Output::outputError(409, 'La categoría a modificar no existe.');
    }

    private function validarSiYaFueRegistrado(string $descripcion, mysqli $linkExterno, ?int $catId = null)
    {
        $descripcion = $linkExterno->real_escape_string($descripcion);

        $query = $catId ? "SELECT 1 FROM categoria WHERE catId <> $catId AND catDescripcion='$descripcion' AND catFechaBaja is NULL" : "SELECT 1 FROM categoria WHERE catDescripcion='$descripcion' AND catFechaBaja is NULL";

        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener una categoría por descripcion'))
            Output::outputError(409, $catId ? 'La descripción nueva que quiere registrar ya existe declarada en otro id' : 'Ya se encuentra registrada la descripción de la categoría a crear.');
    }

}