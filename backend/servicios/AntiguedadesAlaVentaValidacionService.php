<?php

use Model\CustomException;
use Utilidades\Input;

class AntiguedadesAlaVentaValidacionService extends ValidacionServiceBase
{
    private static $instancia = null;

    private function __construct()
    {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstancia(): AntiguedadesAlaVentaValidacionService
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $antiguedadAlaVenta, mixed $extraParams = null): void
    {
        if (!($antiguedadAlaVenta instanceof AntiguedadAlaVentaCreacionDTO) && !($antiguedadAlaVenta instanceof AntiguedadAlaVentaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        if ($extraParams === null || !($extraParams instanceof ClaimDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: los parámetros extra son obligatorios y deben ser del tipo ClaimDTO.');
        }

        $this->validarDatosObligatorios(classModelName: AntiguedadAlaVenta::class, datos: get_object_vars($antiguedadAlaVenta));
        //Input::trimStringDatos($antiguedadAlaVenta); // no hay campos string importantes en AntiguedadAlaVenta

        $this->validarAntiguedad($antiguedadAlaVenta->antiguedad, $extraParams);
        $this->validarDomicilio($antiguedadAlaVenta->antiguedad, $antiguedadAlaVenta->domicilio, $linkExterno);

        if ($antiguedadAlaVenta instanceof AntiguedadAlaVentaDTO) {
            $this->validarSiYafueRegistradoModificar($antiguedadAlaVenta, $linkExterno);
            $this->validarAntiguedadDTO($antiguedadAlaVenta);
        } else {
            $this->validarAntiguedadCreacionDTO($antiguedadAlaVenta, $linkExterno, false);
            $this->validarSiYaFueRegistrado($antiguedadAlaVenta, $linkExterno);
        }
    }

    private function validarAntiguedad(AntiguedadDTO $antiguedad, ClaimDTO $claimDTO): void
    {
        if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $antiguedad->usuario->usrId !== $claimDTO->usrId) {
            throw new CustomException(code: 403, message: 'No tienes permiso para registrar antigüedades a la venta.');
        }

        if (!$antiguedad->tipoEstado->isHabilitadoParaVenta()) {
            throw new CustomException(code: 403, message: 'La antigüedad no está habilitada para la venta.');
        }
    }

    private function validarDomicilio(AntiguedadDTO $antiguedad, DomicilioDTO $domicilio, mysqli $linkExterno): void
    {
        $query = "SELECT 1 from usuariodomicilio WHERE udomUsr = {$antiguedad->usuario->usrId} AND udomDom = {$domicilio->domId} AND udomFechaBaja IS NULL";
        if ($this->_existeEnBD($linkExterno, $query, "verificar si el domicilio pertenece al usuario de la antigüedad") === false) {
            throw new CustomException(code: 403, message: 'El domicilio no pertenece al usuario de la antigüedad.');
        }
    }

}
