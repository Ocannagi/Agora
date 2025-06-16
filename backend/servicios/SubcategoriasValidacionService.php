<?php

use Model\CustomException;
use Utilidades\Input;

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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $subcategoria, mixed $extraParams = null): void
    {
        if (!($subcategoria instanceof SubcategoriaCreacionDTO) && !($subcategoria instanceof SubcategoriaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Subcategoria', datos: get_object_vars($subcategoria));
        Input::trimStringDatos($subcategoria);
        
        $this->validarDatoIdCategoria($linkExterno, $subcategoria->categoria);


        $this->validarDescripcion($subcategoria->scatDescripcion);

        if ($subcategoria instanceof SubcategoriaDTO) {
            $this->validarExisteSubcategoriaModificar($subcategoria->scatId, $linkExterno);
            $this->validarSiYaFueRegistrado(descripcion: $subcategoria->scatDescripcion, scatCatId: $subcategoria->categoria->catId, linkExterno: $linkExterno, scatId: $subcategoria->scatId);
        } else {
            $this->validarSiYaFueRegistrado(descripcion: $subcategoria->scatDescripcion, scatCatId: $subcategoria->categoria->catId, linkExterno: $linkExterno);

        }
    }

    private function validarDatoIdCategoria(mysqli $linkExterno, CategoriaDTO $categoriaDTO)
    {
        if (!isset($categoriaDTO->catId)) {
            throw new InvalidArgumentException(message: 'El id de la categoría no fue proporcionado.');
        }
        
        if (!($categoriaDTO instanceof CategoriaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de categoría no es del tipo correcto.');
        }

        if ($categoriaDTO->catId <= 0) {
            throw new InvalidArgumentException(message: "El ID de la categoría no es válido: $categoriaDTO->catId.");
        }

        if (!$this->existeCategoria($categoriaDTO->catId, $linkExterno)) {
            throw new CustomException(code: 409, message: "La categoría con ID $categoriaDTO->catId no existe.");
        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 50))
            throw new InvalidArgumentException(message: 'La Descripción de la subcategoría debe ser un string de al menos un caracter y un máximo de 50.');
    }


    private function validarExisteSubcategoriaModificar(int $scatId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query:"SELECT 1 FROM subcategoria WHERE scatId='$scatId' AND scatFechaBaja IS NULL", msg: 'obtener una subcategoría por id para modificar'))
            throw new CustomException(code: 409, message: 'La subcategoría a modificar no existe.');
    }

    private function validarSiYaFueRegistrado(string $descripcion, int $scatCatId, mysqli $linkExterno, ?int $scatId = null)
    {
        $descripcion = $linkExterno->real_escape_string($descripcion);

        $query = $scatId ? "SELECT 1
                            FROM subcategoria
                            WHERE scatId <> $scatId
                            AND scatDescripcion='$descripcion'
                            AND scatCatId = $scatCatId
                            AND scatFechaBaja is NULL" : 
                            
                            "SELECT 1 FROM subcategoria
                            WHERE scatDescripcion='$descripcion'
                            AND scatCatId = $scatCatId
                            AND scatFechaBaja is NULL";
                            
        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener una subcategoría por descripción'))
            throw new CustomException(code: 409, message: $scatId ? "La descripción nueva que quiere registrar para el ID categoría $scatCatId ya existe declarada en otra subcategoría" : "Ya se encuentra registrada la descripción de la subcategoría a crear para el ID categoría $scatCatId.");
    }

    private function existeCategoria(int $catId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM categoria WHERE catId='$catId' AND catFechaBaja IS NULL", msg: 'obtener una categoría por id');
    }

    
}