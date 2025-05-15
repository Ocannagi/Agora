<?php


use Utilidades\Output;
use Utilidades\Input;

class ImagenesAntiguedadValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $imagenAntiguedad)
    {
        if (!($imagenAntiguedad instanceof ImagenAntiguedadCreacionDTO) && !($imagenAntiguedad instanceof ImagenAntiguedadDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'ImagenAntiguedad', datos: get_object_vars($imagenAntiguedad));
        Input::trimStringDatos($imagenAntiguedad);
        $this->validarDatoIdAntiguedad($linkExterno, $imagenAntiguedad->antId);

        if ($imagenAntiguedad instanceof ImagenAntiguedadDTO) {
            $this->validarSiYaFueRegistrado($imagenAntiguedad, $linkExterno);
        } else {
            $this->validarSiYaFueRegistrado($imagenAntiguedad, $linkExterno);
        }
    }
}