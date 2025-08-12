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

        if ($extraParams === null) {
            throw new CustomException(code: 500, message: 'Error interno: los parámetros extra son obligatorios.');
        }
        elseif (!($extraParams instanceof ClaimDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: los parámetros extra deben ser del tipo ClaimDTO.');
        }

        $this->validarDatosObligatorios(classModelName: AntiguedadAlaVenta::class, datos: get_object_vars($antiguedadAlaVenta));
        //Input::trimStringDatos($antiguedadAlaVenta); // no hay campos string importantes en AntiguedadAlaVenta

        $this->validarAntiguedad($antiguedadAlaVenta->antiguedad, $linkExterno);
        $this->validarDomicilio($antiguedadAlaVenta->domicilio, $linkExterno);

        if ($antiguedadAlaVenta instanceof AntiguedadAlaVentaDTO) {
            $this->validarSiYafueRegistradoModificar($antiguedadAlaVenta, $linkExterno);
            $this->validarAntiguedadDTO($antiguedadAlaVenta);
        } else {
            $this->validarAntiguedadCreacionDTO($antiguedadAlaVenta, $linkExterno, false);
            $this->validarSiYaFueRegistrado($antiguedadAlaVenta, $linkExterno);
        }
    }

    private function validarSiYaFueRegistrado(AntiguedadAlaVentaCreacionDTO $antiguedadAlaVenta, mysqli $linkExterno)
    {
        $query = "SELECT 1 FROM antiguedad
                 WHERE antUsrId = {$antiguedadAlaVenta->usuario->usrId}
                 AND antFechaBaja IS NULL";

        if ($this->_existeEnBD(
            link: $linkExterno,
            query: $query,
            msg: 'verificar si ya existe una antigüedad a la venta.'
        )) {
            throw new CustomException(
                code: 400,
                message: 'Ya existe una antigüedad a la venta registrada para este usuario.'
            );
        }
    }
    }