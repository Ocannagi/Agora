<?php

use Model\CustomException;
use Utilidades\Input;

class TasacionesDigitalesValidacionService extends ValidacionServiceBase
{
    private static $instancia = null;

    private function __construct()
    {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstancia(): TasacionesDigitalesValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone()
    {
        // Previene la clonación de la instancia
    }

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $tasacionDigital)
    {
        if (!($tasacionDigital instanceof TasacionDigitalCreacionDTO) && !($tasacionDigital instanceof TasacionDigitalDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'TasacionDigital', datos: get_object_vars($tasacionDigital));
        Input::trimStringDatos($tasacionDigital);

        $this->validarPeriodo($tasacionDigital->periodo, $linkExterno);
        $this->validarSubcategoria($tasacionDigital->subcategoria, $linkExterno);
        $this->validarDescripcion($tasacionDigital->tdDescripcion);
        $this->validarUsuario($tasacionDigital->usuario, $linkExterno);

        if ($tasacionDigital instanceof TasacionDigitalDTO) {
            $this->validarTipoEstado($tasacionDigital->tipoEstado);
            $this->validarExisteTasacionModificar($tasacionDigital->tdId, $linkExterno);
        } else {
            $this->validarSiYaFueRegistradoPorMismoUsuario($tasacionDigital, $linkExterno);
        }
    }


    private function validarPeriodo(PeriodoDTO $periodoDTO, mysqli $linkExterno)
    {
        if (!($periodoDTO instanceof PeriodoDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de periodo no es del tipo correcto.');
        }

        if (!isset($periodoDTO->perId)) {
            throw new InvalidArgumentException(message: 'El id del periodo no fue proporcionado.');
        }

        if (!is_int($periodoDTO->perId)) {
            throw new InvalidArgumentException(message: 'El id del periodo debe ser un entero.');
        }

        $this->validarExistePeriodo($periodoDTO->perId, $linkExterno);
    }


    private function validarSubcategoria(SubcategoriaDTO $subcategoriaDTO, mysqli $linkExterno)
    {
        if (!($subcategoriaDTO instanceof SubcategoriaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de subcategoría no es del tipo correcto.');
        }

        if (!isset($subcategoriaDTO->subId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría no fue proporcionado.');
        }

        if (!is_int($subcategoriaDTO->subId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría debe ser un entero.');
        }

        $this->validarExisteSubcategoria($subcategoriaDTO->subId, $linkExterno);
    }
}