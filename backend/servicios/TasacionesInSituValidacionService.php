<?php

use Model\CustomException;
use Utilidades\Input;

class TasacionesInSituValidacionService extends ValidacionServiceBase
{
    use TraitValidarTasacion;

    private static $instancia = null;

    private function __construct()
    {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstancia(): TasacionesInSituValidacionService
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $tasacionInSitu, mixed $extraParams = null): void
    {
        if (!($tasacionInSitu instanceof TasacionInSituCreacionDTO) && !($tasacionInSitu instanceof TasacionInSituDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

         if(!isset($extraParams) || !$extraParams instanceof ClaimDTO) {
            throw new CustomException(code: 500, message: 'Error interno: se requiere ClaimDTO para validar la tasación In Situ.');
        }

        $this->validarDatosObligatorios(classModelName: 'TasacionInSitu', datos: get_object_vars($tasacionInSitu));
        Input::trimStringDatos($tasacionInSitu);

        if ($tasacionInSitu instanceof TasacionInSituDTO) {
            $this->validarSiYafueRegistradoModificar($tasacionInSitu, $linkExterno);
            $this->validarTasacionInSituDTO($tasacionInSitu);
        } else {;
            $this->validarTasacionInSituCreacionDTO($tasacionInSitu, $extraParams, $linkExterno);
            $this->validarSiYaFueRegistrado($tasacionInSitu, $linkExterno);
        }
    }

    private function validarSiYaFueRegistrado(TasacionInSituCreacionDTO $tasacionInSitu, mysqli $linkExterno)
    {
        $query = "SELECT 1 FROM tasacioninsitu
                 WHERE tisTadId = {$tasacionInSitu->tasacionDigital->tadId}
                 AND tisFechaBaja IS NULL";

        if ($this->_existeEnBD(
            link: $linkExterno,
            query: $query,
            msg: 'obtener una tasación In Situ'
        )) {
            throw new CustomException(code: 400, message: 'Ya existe una tasación In Situ para la tasación digital proporcionada.');
        }
    }

    private function validarSiYafueRegistradoModificar(TasacionInSituDTO $tasacionInSitu, mysqli $linkExterno)
    {
        if (!isset($tasacionInSitu->tisId) || $tasacionInSitu->tisId <= 0) {
            throw new CustomException(code: 400, message: 'El ID de la tasación In Situ no es válido.');
        }

        $query = "SELECT 1 FROM tasacioninsitu
                 WHERE tisId = {$tasacionInSitu->tisId}
                 AND tisFechaTasInSituRealizada IS NULL
                 AND tisFechaTasInSituRechazada IS NULL
                 AND tisFechaBaja IS NULL";

        if (!$this->_existeEnBD(
            link: $linkExterno,
            query: $query,
            msg: 'obtener una tasación In Situ por ID'
        )) {
            throw new CustomException(code: 404, message: 'No se encontró una tasación In Situ en curso con el ID proporcionado.');
        }
    }
}