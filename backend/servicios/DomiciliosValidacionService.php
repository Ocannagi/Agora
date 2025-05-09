<?php

use Utilidades\Output;

class DomiciliosValidacionService extends ValidacionServiceBase
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $domicilio)
    {
        if (!($domicilio instanceof DomicilioCreacionDTO) && !($domicilio instanceof DomicilioDTO)) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Domicilio', datos: get_object_vars($domicilio));
        $this->validarDatoIdLocalidad($linkExterno, $domicilio->localidad);

        $this->validarCalleRuta($domicilio->domCalleRuta);
        $this->validarNroKm($domicilio->domNroKm);
        $this->validarPiso($domicilio->domPiso);
        $this->validarDepto($domicilio->domDepto);

        if ($domicilio instanceof DomicilioDTO) {
            $this->validarExisrteDomicilioModificar($domicilio->domId, $linkExterno);
            $this->validarSiYaFueRegistrado(descripcion: $domicilio->domCalleRuta, domLocId: $domicilio->localidad->locId, linkExterno: $linkExterno, domId: $domicilio->domId);
        } else {
            $this->validarSiYaFueRegistrado(descripcion: $domicilio->domCalleRuta, domLocId: $domicilio->localidad->locId, linkExterno: $linkExterno);
        }
    }
}