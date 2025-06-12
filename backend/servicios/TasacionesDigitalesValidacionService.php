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

        $this->validarTasador($tasacionDigital->tasador, $linkExterno);
        $this->validarPropietario($tasacionDigital->propietario, $linkExterno);
        $this->validarAntiguedad($tasacionDigital->antiguedad, $linkExterno);

        $this->validarTasacionDigital($tasacionDigital, $linkExterno);



        if ($tasacionDigital instanceof TasacionDigitalDTO) {
           
        } else {
            
        }
    }

    private function validarTasador(UsuarioDTO $tasadorDTO, mysqli $linkExterno)
    {
        if (!($tasadorDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasador no es del tipo correcto.');
        }

        if (!isset($tasadorDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del tasador no fue proporcionado.');
        }

        if (!is_int($tasadorDTO->usrId)||$tasadorDTO->usrId <= 0) {
            throw new InvalidArgumentException(message: 'El id del tasador debe ser un entero mayor a cero.');
        }

        if (!isset($tasadorDTO->usrTipoUsuario)) {
            throw new InvalidArgumentException(message: 'El tipo de usuario del tasador no fue proporcionado.');
        }

        // Validar que el tipo de usuario sea uno de los permitidos (Soporte Técnico solo para pruebas)
        if(in_array($tasadorDTO->usrTipoUsuario, [TipoUsuarioEnum::UsuarioTasador->value, TipoUsuarioEnum::UsuarioAnticuario->value, TipoUsuarioEnum::SoporteTecnico->value]) === false) {
            throw new InvalidArgumentException(message: 'El usuario tasador debe ser de tipo "tasador" o "anticuario".');
        }

        if(!$this->_existeEnBD(
            link: $linkExterno,
            query: "SELECT usrId FROM usuario WHERE usrId = $tasadorDTO->usrId AND usrTipoUsuario = '$tasadorDTO->usrTipoUsuario' AND usrFechaBaja IS NULL",
            msg: 'validar tasador'
        )) {
            throw new InvalidArgumentException(message: 'El tasador o anticuario no existe en la base de datos.');
        }

    }

    private function validarPropietario(UsuarioDTO $propietarioDTO, mysqli $linkExterno)
    {
        if (!($propietarioDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de propietario no es del tipo correcto.');
        }

        if (!isset($propietarioDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del propietario no fue proporcionado.');
        }

        if (!is_int($propietarioDTO->usrId) || $propietarioDTO->usrId <= 0) {
            throw new InvalidArgumentException(message: 'El id del propietario debe ser un entero mayor a cero.');
        }

        if (!isset($propietarioDTO->usrTipoUsuario)) {
            throw new InvalidArgumentException(message: 'El tipo de usuario del propietario no fue proporcionado.');
        }

        if(in_array($propietarioDTO->usrTipoUsuario, [TipoUsuarioEnum::UsuarioAnticuario->value, TipoUsuarioEnum::UsuarioGeneral->value]) === false) {
            throw new InvalidArgumentException(message: 'El usuario propietario debe ser de tipo "anticuario" o "general".');
        }

        if(!$this->_existeEnBD(
            link: $linkExterno,
            query: "SELECT usrId FROM usuario WHERE usrId = $propietarioDTO->usrId AND usrFechaBaja IS NULL",
            msg: 'validar propietario'
        )) {
            throw new InvalidArgumentException(message: 'El propietario no existe en la base de datos.');
        }
    }

    private function validarAntiguedad(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($antiguedadDTO->antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        if (!is_int($antiguedadDTO->antId) || $antiguedadDTO->antId <= 0) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad debe ser un entero mayor a cero.');
        }

        if (!isset($antiguedadDTO->tipoEstado) || !($antiguedadDTO->tipoEstado instanceof TipoEstadoEnum)) {
            throw new InvalidArgumentException(message: 'El tipo de estado de la antigüedad no fue proporcionado o o no es del tipo correcto.');
        }

        if($antiguedadDTO->tipoEstado === TipoEstadoEnum::RetiradoNoDisponible) {
            throw new InvalidArgumentException(message: 'El tipo de estado de la antigüedad no puede ser "RetiradoNoDisponible" para una tasación digital.');
        }
     
        if (!$this->_existeEnBD(
            link: $linkExterno,
            query: "SELECT antId FROM antiguedad
                    WHERE antId = $antiguedadDTO->antId
                    AND antUsrId = '{$antiguedadDTO->usuario->usrId}'
                    AND antTipoEstado = '{$antiguedadDTO->tipoEstado->value}'",
            msg: 'validar antigüedad'
        )) {
            throw new InvalidArgumentException(message: 'La antigüedad no existe en la base de datos con los datos consignados.');
        }
    }

    private function validarTasacionDigital(TasacionDigitalCreacionDTO|TasacionDigitalDTO $tasacionDigital, mysqli $linkExterno)
    {
        if (!($tasacionDigital instanceof TasacionDigitalCreacionDTO) && !($tasacionDigital instanceof TasacionDigitalDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasación digital no es del tipo correcto.');
        }

        if (!isset($tasacionDigital->antiguedad->usuario->usrId)) {
            throw new InvalidArgumentException(message: 'El id del usuario de la antigüedad no fue proporcionado.');
        }

        if ($tasacionDigital->antiguedad->usuario->usrId !== $tasacionDigital->propietario->usrId) {
            throw new InvalidArgumentException(message: 'El id del usuario de la antigüedad debe ser el mismo que el del propietario de la tasación digital.');
        }
           
    }



}