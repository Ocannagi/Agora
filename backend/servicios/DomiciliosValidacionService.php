<?php

use Utilidades\Output;
use Utilidades\Input;

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
        Input::trimStringDatos($domicilio);
        $this->validarDatoIdLocalidad($linkExterno, $domicilio->localidad);

        $this->validarCPA($domicilio->domCPA);
        $this->validarCalleRuta($domicilio->domCalleRuta);
        $this->validarNroKm($domicilio->domNroKm);
        $this->validarPiso($domicilio->domPiso);
        $this->validarDepto($domicilio->domDepto);

        if ($domicilio instanceof DomicilioDTO) {
            $this->validarExisteDomicilioModificar($domicilio->domId, $linkExterno);
            $this->validarSiYaFueRegistrado($domicilio, $linkExterno);
        } else {
            $this->validarSiYaFueRegistrado($domicilio, $linkExterno);
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

    private function validarCPA(string $CPA)
    {
        if (!$this->_esStringLongitud($CPA, 8, 8))
            Output::outputError(400, 'El CPA debe ser un string de 8 caracteres');

        if(!$this->validarFormatoCPA($CPA))
            Output::outputError(400, 'El CPA no tiene el formato correcto. Debe ser una letra mayúscula seguida de 4 dígitos y 3 letras mayúsculas.');
    }

    private function validarFormatoCPA(string $CPA) : bool
    {
         return preg_match('/^[A-Z]\d{4}[A-Z]{3}$/', $CPA) === 1;
    }

    private function validarCalleRuta(string $calleRuta)
    {
        if (!$this->_esStringLongitud($calleRuta, 1, 50)) {
            Output::outputError(400, 'La Calle-Ruta debe ser un string de al menos un caracter y un máximo de 50.');
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
        if (Input::esNotNullVacioBlanco($piso)) {

            if (!$this->_esStringLongitud($piso, 1, 10)) {
                Output::outputError(400, 'El Piso debe ser un string de al menos un caracter y un máximo de 10.');
            }

            if (!$this->_esDigitoNegativoOPositivo($piso) && !$this->_esLetraSinTilde($piso) && !$this->_esAlfaNumerico($piso)) {
                Output::outputError(400, 'El Piso debe ser un número entero o un string alfanumérico sin espacios en blanco ni caracteres especiales.');
            }
        }
    }

    private function validarDepto(?string $depto)
    {
        if (Input::esNotNullVacioBlanco($depto)) {

            if (!$this->_esStringLongitud($depto, 1, 10)) {
                Output::outputError(400, 'El Depto debe ser un string de al menos un caracter y un máximo de 10.');
            }

            if (!$this->_esDigitoNegativoOPositivo($depto) && !$this->_esLetraSinTilde($depto) && !$this->_esAlfaNumerico($depto)) {
                Output::outputError(400, 'El Depto debe ser un número entero o un string alfanumérico sin espacios en blanco ni caracteres especiales.');
            }
        }
    }

    private function validarExisteDomicilioModificar(int $domId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM domicilio WHERE domId = $domId AND domFechaBaja IS NULL", msg: 'obtener un domicilio por id para modificar')) {
            Output::outputError(409, 'El domicilio a modificar no existe.');
        }
    }

    private function validarSiYaFueRegistrado(DomicilioCreacionDTO | DomicilioDTO $domicilio, mysqli $linkExterno)
    {
        $qPiso = Input::esNotNullVacioBlanco($domicilio->domPiso) ? " AND domPiso = '{$domicilio->domPiso}'" : ' AND domPiso IS NULL';
        $qDepto = Input::esNotNullVacioBlanco($domicilio->domDepto) ? " AND domDepto = '{$domicilio->domDepto}'" : ' AND domDepto IS NULL';

        if ($domicilio instanceof DomicilioDTO) {
            $query = "SELECT 1 FROM domicilio WHERE domCalleRuta = '{$domicilio->domCalleRuta}' AND domNroKm = {$domicilio->domNroKm} $qPiso $qDepto AND domLocId = {$domicilio->localidad->locId} AND domId <> {$domicilio->domId} AND domFechaBaja IS NULL";
        } else {
            $query = "SELECT 1 FROM domicilio WHERE domCalleRuta = '{$domicilio->domCalleRuta}' AND domNroKm = {$domicilio->domNroKm} $qPiso $qDepto AND domLocId = {$domicilio->localidad->locId} AND domFechaBaja IS NULL";
        }

        if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'verificar si el domicilio ya fue registrado')) {
            Output::outputError(409, 'El domicilio ya fue registrado.');
        }
    }
}
