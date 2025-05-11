<?php

use Utilidades\Output;
use Utilidades\Input;

class LocalidadesValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $localidad)
    {
        if (!($localidad instanceof LocalidadCreacionDTO) && !($localidad instanceof LocalidadDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Localidad', datos: get_object_vars($localidad));
        Input::trimStringDatos($localidad);
        
        $this->validarProvincia($localidad->provincia, $linkExterno);


        $this->validarDescripcion($localidad->locDescripcion);

        if ($localidad instanceof LocalidadDTO) {
            $this->validarSiYaFueRegistrado($localidad->locDescripcion, $localidad->provincia->provId ,$linkExterno, $localidad->locId);
            $this->validarExisteLocalidadModificar($localidad->locId, $linkExterno);
        } else {
            $this->validarSiYaFueRegistrado($localidad->locDescripcion, $localidad->provincia->provId, $linkExterno);
        }
    }
    
    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 50))
            Output::outputError(400, 'La Descripción de la localidad debe ser un string de al menos un caracter y un máximo de 50.');
    }

    private function validarProvincia(ProvinciaDTO $provinciaDTO, mysqli $linkExterno)
    {
        if (!($provinciaDTO instanceof ProvinciaDTO)) {
            Output::outputError(500, 'Error interno: el DTO de provincia no es del tipo correcto.');
        }

        if (!isset($provinciaDTO->provId)) {
            Output::outputError(400, 'El id de la provincia no fue proporcionado.');
        }
        
        if ($provinciaDTO->provId <= 0) {
            Output::outputError(400, "El ID de la provincia no es válido: $provinciaDTO->provId.");
        }

        if (!$this->existeProvincia($provinciaDTO->provId, $linkExterno)) {
            Output::outputError(409, "La provincia con ID $provinciaDTO->provId no existe.");
        }
    }

    private function existeProvincia(int $provId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM provincia WHERE provId='$provId'";
        return $this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener una provincia por id');
    }

    private function validarSiYaFueRegistrado(string $descripcion, int $provId, mysqli $linkExterno, ?int $locId = null)
    {
        $descripcion = $linkExterno->real_escape_string($descripcion);

        $query = $locId ? "SELECT 1 FROM localidad WHERE locId <> $locId AND locDescripcion='$descripcion' AND locProvId = $provId AND locFechaBaja is NULL" : "SELECT 1 FROM localidad WHERE locDescripcion='$descripcion' AND locProvId = $provId AND locFechaBaja is NULL";

        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener una localidad por descripcion'))
            Output::outputError(409, $locId ? 'La descripción nueva que quiere registrar ya existe declarada en otro id' : 'Ya se encuentra registrada la descripción de la localidad a crear.');
    }

    private function validarExisteLocalidadModificar(int $locId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query:"SELECT 1 FROM localidad WHERE locId='$locId' AND locFechaBaja IS NULL", msg: 'obtener una localidad por id para modificar'))
            Output::outputError(409, 'La localidad a modificar no existe.');
    }
}