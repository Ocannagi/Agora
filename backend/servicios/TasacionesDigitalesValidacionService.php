<?php

use Model\CustomException;
use Utilidades\Input;

class TasacionesDigitalesValidacionService extends ValidacionServiceBase
{
    use TraitValidarTasacion;

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

        if ($tasacionDigital instanceof TasacionDigitalDTO) {
            $this->validarSiYafueRegistradoModificar($tasacionDigital, $linkExterno);
            $this->validarTasacionDigitalDTO($tasacionDigital);
        } else {
            $this->validarTasacionDigitalCreacionDTO($tasacionDigital, $linkExterno, false);
            $this->validarSiYaFueRegistrado($tasacionDigital, $linkExterno);
        }
    }

    private function validarSiYaFueRegistrado(TasacionDigitalCreacionDTO $tasacionDigital, mysqli $linkExterno)
    {
        $query = "SELECT 1 FROM tasaciondigital
                 WHERE tadUsrTasId = {$tasacionDigital->tasador->usrId}
                 AND tadUsrPropId = {$tasacionDigital->propietario->usrId}
                 AND tadAntId = {$tasacionDigital->antiguedad->antId}
                 AND tadFechaBaja IS NULL
                 AND tadFechaTasDigitalRealizada IS NULL
                 AND tadFechaTasDigitalRechazada IS NULL";

        if ($this->_existeEnBD(
            link: $linkExterno,
            query: $query,
            msg: 'verificar si ya existe una tasación digital pendiente de realizar o rechazar.'
        )) {
            throw new CustomException(code: 409, message: 'La tasación digital ya fue registrada. Esta pendiente de realizar o rechazar.');
        }
    }

    private function validarSiYafueRegistradoModificar(TasacionDigitalDTO $tasacionDigital, mysqli $linkExterno)
    {
        $query = "SELECT 1 FROM tasaciondigital
                 WHERE tadId = {$tasacionDigital->tadId}
                 AND tadFechaBaja IS NULL
                 AND tadFechaTasDigitalRealizada IS NULL
                 AND tadFechaTasDigitalRechazada IS NULL";

        if (!$this->_existeEnBD(
            link: $linkExterno,
            query: $query,
            msg: 'verificar si ya existe una tasación digital pendiente de realizar o rechazar.'
        )) {
            throw new CustomException(code: 409, message: 'No existe una tasación digital pendiente de realizar o rechazar.');
        }
    }

}
