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

        $this->validarTasacionDigital($tasacionDigital, $linkExterno);



        if ($tasacionDigital instanceof TasacionDigitalDTO) {
           
        } else {
            
        }
    }

   


}