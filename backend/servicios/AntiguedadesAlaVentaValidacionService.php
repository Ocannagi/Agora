<?php

use Model\CustomException;
use Utilidades\Input;

define('RANGO_VARIACION_PRECIO_VENTA', 0.10); // 10%

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

        

        $this->validarDomicilio($antiguedadAlaVenta->antiguedad, $antiguedadAlaVenta->domicilioOrigen, $linkExterno);
        $this->validarTasacionDigitalDTO($antiguedadAlaVenta->tasacion, $extraParams);
        $this->validarPrecioVenta($antiguedadAlaVenta->aavPrecioVenta, $antiguedadAlaVenta->tasacion);

        if ($antiguedadAlaVenta instanceof AntiguedadAlaVentaDTO) {
            //no se valida la fecha de retiro, si existe, se la cambia por la fecha actual en el update
            $this->validarSiExisteAntiguedadAlaVentaAModificar($antiguedadAlaVenta, $linkExterno);
        } else {
            $this->validarAntiguedad($antiguedadAlaVenta, $extraParams);
            $this->validarSiYaFueRegistradoYSinBaja($antiguedadAlaVenta, $linkExterno);
        }
    }

    private function validarAntiguedad(AntiguedadAlaVentaCreacionDTO $antiguedadAlaVenta, ClaimDTO $claimDTO): void
    {
        if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $antiguedadAlaVenta->antiguedad->usuario->usrId !== $claimDTO->usrId) {
            throw new CustomException(code: 403, message: 'No tienes permiso para registrar a la venta esta antigüedad.');
        }

        if ($antiguedadAlaVenta->vendedor->usrId !== $antiguedadAlaVenta->antiguedad->usuario->usrId) {
            throw new CustomException(code: 403, message: 'El vendedor debe ser el propietario de la antigüedad.');
        }

        if (!$antiguedadAlaVenta->antiguedad->tipoEstado->isHabilitadoParaVenta()) {
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

    private function validarTasacionDigitalDTO(?TasacionDigitalDTO $tasacion, ClaimDTO $claimDTO): void
    {
        if ($tasacion !== null) {
            if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $tasacion->propietario->usrId !== $claimDTO->usrId) {
                throw new CustomException(code: 403, message: 'No tienes permiso para registrar una tasación digital que no te pertenece.');
            }
        }
    }

    private function validarPrecioVenta(float $precioVenta, ?TasacionDigitalDTO $tasacion): void
    {
        Input::esPrecioValido($precioVenta);

        if ($tasacion !== null) {
            $valorTasacion = $tasacion->tasacionInSitu?->tisPrecioInSitu ?? $tasacion->tadPrecioDigital;

            if ($valorTasacion == null) {
                throw new CustomException(code: 409, message: 'La tasación digital no tiene un precio de tasación asignado.');
            }

            if ($precioVenta < ($valorTasacion * (1 - RANGO_VARIACION_PRECIO_VENTA)) || $precioVenta > ($valorTasacion * (1 + RANGO_VARIACION_PRECIO_VENTA))) {
                throw new CustomException(code: 409, message: 'El precio de venta no puede ser menor al 90% ni mayor al 110% del valor de tasación.');
            }
        }
    }

    private function validarSiYaFueRegistradoYSinBaja(AntiguedadAlaVentaCreacionDTO $antiguedadAlaVenta, mysqli $linkExterno): void
    {
        $query = "SELECT 1 FROM antiguedadesalaventa aav
                  WHERE aav.aavAntId = {$antiguedadAlaVenta->antiguedad->antId}
                  AND aav.aavFechaRetiro IS NULL";
        if ($this->_existeEnBD($linkExterno, $query, "verificar si la antigüedad fue registrada a la venta y no fue retirada/vendida") === true) {
            throw new CustomException(code: 409, message: 'La antigüedad ya fue registrada a la venta y no fue retirada/vendida.');
        }
    }

    private function validarSiExisteAntiguedadAlaVentaAModificar(AntiguedadAlaVentaDTO $antiguedadAlaVenta, mysqli $linkExterno): void
    {

        if (!isset($antiguedadAlaVenta->aavId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        if (!is_int($antiguedadAlaVenta->aavId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no es un número entero.');
        }

        if ($antiguedadAlaVenta->aavId <= 0) {
            throw new InvalidArgumentException(message: "El id de la antigüedad no es válido: {$antiguedadAlaVenta->aavId}");
        }

        $query = "SELECT 1 FROM antiguedadesalaventa aav
                  WHERE aav.aavId = {$antiguedadAlaVenta->aavId}
                  AND aav.aavFechaRetiro IS NULL";
        if ($this->_existeEnBD($linkExterno, $query, "verificar si la antigüedad a la venta que se quiere modificar existe y no fue retirada/vendida") === false) {
            throw new CustomException(code: 409, message: 'La antigüedad a la venta que se quiere modificar no existe o ya fue retirada/vendida.');
        }
    }
}
