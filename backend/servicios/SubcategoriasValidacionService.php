<?php

use Utilidades\Output;

class SubcategoriasValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $subcategoria)
    {
        if (!($subcategoria instanceof SubcategoriaCreacionDTO) && !($subcategoria instanceof SubcategoriaDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Subcategoria', datos: get_object_vars($subcategoria));
        $this->validarDescripcion($subcategoria->scatDescripcion);

        if ($subcategoria instanceof SubcategoriaDTO) {
            $this->validarExisteSubcategoriaModificar($subcategoria->scatId, $linkExterno);
            $this->validarSiYaFueRegistrado(descripcion: $subcategoria->scatDescripcion, scatCatId: $subcategoria->scatCatId, linkExterno: $linkExterno, scatId: $subcategoria->scatId);
        } else {
            $this->validarSiYaFueRegistrado(descripcion: $subcategoria->scatDescripcion, scatCatId: $subcategoria->scatCatId, linkExterno: $linkExterno);

        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 50))
            Output::outputError(400, 'La Descripción de la subcategoría debe ser un string de al menos un caracter y un máximo de 50.');
    }


    private function validarExisteSubcategoriaModificar(int $scatId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query:"SELECT 1 FROM subcategoria WHERE scatId='$scatId' AND scatFechaBaja IS NULL", msg: 'obtener una subcategoría por id para modificar'))
            Output::outputError(409, 'La subcategoría a modificar no existe.');
    }

    private function validarSiYaFueRegistrado(string $descripcion, int $scatCatId, mysqli $linkExterno, ?int $scatId = null)
    {
        $descripcion = $linkExterno->real_escape_string($descripcion);

        $query = $scatId ? "SELECT 1 FROM subcategoria WHERE scatId <> $scatId AND scatDescripcion='$descripcion' AND scatCatId = $scatCatId AND scatFechaBaja is NULL" : "SELECT 1 FROM subcategoria WHERE scatDescripcion='$descripcion' AND scatCatId = $scatCatId AND scatFechaBaja is NULL";

        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener una subcategoría por descripción'))
            Output::outputError(409, $scatId ? "La descripción nueva que quiere registrar para el ID categoría $scatCatId ya existe declarada en otra subcategoría" : "Ya se encuentra registrada la descripción de la subcategoría a crear para el ID categoría $scatCatId.");
    }

    
}