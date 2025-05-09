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
            $this->validarExisteDomicilioModificar($domicilio->domId, $linkExterno);
            $this->validarSiYaFueRegistrado(descripcion: $domicilio->domCalleRuta, domLocId: $domicilio->localidad->locId, linkExterno: $linkExterno, domId: $domicilio->domId);
        } else {
            $this->validarSiYaFueRegistrado(descripcion: $domicilio->domCalleRuta, domLocId: $domicilio->localidad->locId, linkExterno: $linkExterno);
        }
    }

    private function validarDatoIdLocalidad(mysqli $linkExterno, LocalidadDTO $localidad)
    {
        if (!isset($localidad->locId)) {
            Output::outputError(400, 'El id de la localidad no fue proporcionado.');
        }
        
        if ($localidad->locId <= 0) {
            Output::outputError(400, 'El id de la localidad no es válido.');
        }

        if (!$this->existeLocalidad($localidad->locId, $linkExterno)) {
            Output::outputError(409, 'La localidad con ID ' . $localidad->locId . ' no existe.');
        }
    }

    private function existeLocalidad(int $locId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM localidad WHERE locId = $locId AND locFechaBaja IS NULL", msg: 'obtener una localidad por id');
    }

    private function validarCalleRuta(string $calleRuta)
    {
        if (!$this->_esStringLongitud($calleRuta, 1, 50)) {
            Output::outputError(400, 'La Calle/Ruta debe ser un string de al menos un caracter y un máximo de 50.');
        }
    }

    private function validarNroKm(int $nroKm)
    {
        if ($nroKm < 0) {
            Output::outputError(400, 'El número de km debe ser un valor positivo.');
        }
        if ($nroKm > 12000) {
            Output::outputError(400, 'El número de NroKm no puede ser mayor a 12000.');
        }
    }

    private function validarPiso(?string $piso)
    {
        if ($piso !== null){

           

        }
        
        
    }


}