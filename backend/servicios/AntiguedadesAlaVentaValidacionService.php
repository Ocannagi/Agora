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

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $antiguedad, mixed $extraParams = null): void
    {
        if (!($antiguedad instanceof AntiguedadCreacionDTO) && !($antiguedad instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Antiguedad', datos: get_object_vars($antiguedad));
        Input::trimStringDatos($antiguedad);

        if ($antiguedad instanceof AntiguedadDTO) {
            $this->validarSiYafueRegistradoModificar($antiguedad, $linkExterno);
            $this->validarAntiguedadDTO($antiguedad);
        } else {
            $this->validarAntiguedadCreacionDTO($antiguedad, $linkExterno, false);
            $this->validarSiYaFueRegistrado($antiguedad, $linkExterno);
        }
    }

    private function validarSiYaFueRegistrado(AntiguedadCreacionDTO $antiguedad, mysqli $linkExterno)
    {
        $query = "SELECT 1 FROM antiguedad
                 WHERE antUsrId = {$antiguedad->usuario->usrId}
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