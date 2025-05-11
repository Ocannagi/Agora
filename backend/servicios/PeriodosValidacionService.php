<?php

use Utilidades\Output;
use Utilidades\Input;

class PeriodosValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $periodo)
    {
        if (!($periodo instanceof PeriodoCreacionDTO) && !($periodo instanceof PeriodoDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Periodo', datos: get_object_vars($periodo));
        Input::trimStringDatos($periodo);
        
        $this->validarDescripcion($periodo->perDescripcion);

        if ($periodo instanceof PeriodoDTO) {
            $this->validarExistePeriodoModificar($periodo->perId, $linkExterno);
            $this->validarSiYaFueRegistrado($periodo->perDescripcion, $linkExterno, $periodo->perId);
        } else {
            $this->validarSiYaFueRegistrado($periodo->perDescripcion, $linkExterno);
        }
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 50))
            Output::outputError(400, 'La Descripción del periodo debe ser un string de al menos un caracter y un máximo de 50.');
    }

    private function validarExistePeriodoModificar(int $perId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query:"SELECT 1 FROM periodo WHERE perId='$perId' AND perFechaBaja IS NULL", msg: 'obtener un periodo por id para modificar'))
            Output::outputError(409, 'El periodo a modificar no existe.');
    }

    private function validarSiYaFueRegistrado(string $descripcion, mysqli $linkExterno, ?int $perId = null)
    {
        $descripcion = $linkExterno->real_escape_string($descripcion);

        $query = $perId ? "SELECT 1 FROM periodo WHERE perId <> $perId AND perDescripcion='$descripcion' AND perFechaBaja is NULL" : "SELECT 1 FROM periodo WHERE perDescripcion='$descripcion' AND perFechaBaja is NULL";

        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'obtener un periodo por descripcion'))
            Output::outputError(409, $perId ? 'La descripción nueva que quiere registrar ya existe declarada en otro id' : 'Ya se encuentra registrada la descripción del periodo a crear.');
    }

}
