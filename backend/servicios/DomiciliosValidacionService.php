<?php

use Model\CustomException;
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

    public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $domicilio, mixed $extraParams = null): void
    {
        if (!($domicilio instanceof DomicilioCreacionDTO) && !($domicilio instanceof DomicilioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Domicilio', datos: get_object_vars($domicilio));
        Input::trimStringDatos($domicilio);
        $this->validarDatoIdLocalidad($linkExterno, $domicilio->localidad);

        $this->validarCPA($domicilio->domCPA);
        $this->validarCalleRuta($domicilio->domCalleRuta);
        $this->validarNroKm($domicilio->domNroKm);
        $this->validarPiso($domicilio->domPiso);
        $this->validarDepto($domicilio->domDepto);

        
        /** Esta validación fue hecha antes de la creación de la tabla UsuarioDomicilio, actualmente no está habilitada la modificación de domicilios */
        if ($domicilio instanceof DomicilioDTO) {
            $this->validarExisteDomicilioModificar($domicilio->domId, $linkExterno); //Sin uso
            $this->validarSiYaFueRegistrado($domicilio, $linkExterno); // Sin uso
        } else {
            $this->validarSiYaFueRegistrado($domicilio, $linkExterno);
        }
    }

    private function validarDatoIdLocalidad(mysqli $linkExterno, LocalidadDTO $localidad)
    {
        if (!isset($localidad->locId)) {
            throw new InvalidArgumentException(message: 'El id de la localidad no fue proporcionado.');
        }

        if ($localidad->locId <= 0) {
            throw new InvalidArgumentException(message: 'El id de la localidad no es válido.');
        }

        if (!$this->existeLocalidad($localidad->locId, $linkExterno)) {
            throw new CustomException(code: 409, message: 'La localidad con ID ' . $localidad->locId . ' no existe.');
        }
    }

    private function existeLocalidad(int $locId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM localidad WHERE locId = $locId AND locFechaBaja IS NULL", msg: 'obtener una localidad por id');
    }

    private function validarCPA(string $CPA)
    {
        if (!$this->_esStringLongitud($CPA, 8, 8))
            throw new InvalidArgumentException(message: 'El CPA debe ser un string de 8 caracteres');

        if(!$this->validarFormatoCPA($CPA))
            throw new InvalidArgumentException(message: 'El CPA no tiene el formato correcto. Debe ser una letra mayúscula seguida de 4 dígitos y 3 letras mayúsculas.');
    }

    private function validarFormatoCPA(string $CPA) : bool
    {
         return preg_match('/^[A-Z]\d{4}[A-Z]{3}$/', $CPA) === 1;
    }

    private function validarCalleRuta(string $calleRuta)
    {
        if (!$this->_esStringLongitud($calleRuta, 1, 50)) {
            throw new InvalidArgumentException(message: 'La Calle-Ruta debe ser un string de al menos un caracter y un máximo de 50.');
        }
    }

    private function validarNroKm(int $nroKm)
    {
        if ($nroKm < 0) {
            throw new InvalidArgumentException(message: 'El número de km debe ser un valor positivo.');
        }
        if ($nroKm > 12000) {
            throw new InvalidArgumentException(message: 'El número de NroKm no puede ser mayor a 12000.');
        }
    }

    private function validarPiso(?string $piso)
    {
        if (Input::esNotNullVacioBlanco($piso)) {

            if (!$this->_esStringLongitud($piso, 1, 10)) {
                throw new InvalidArgumentException(message: 'El Piso debe ser un string de al menos un caracter y un máximo de 10.');
            }

            if (!$this->_esDigitoNegativoOPositivo($piso) && !$this->_esLetraSinTilde($piso) && !$this->_esAlfaNumerico($piso)) {
                throw new InvalidArgumentException(message: 'El Piso debe ser un número entero o un string alfanumérico sin espacios en blanco ni caracteres especiales.');
            }
        }
    }

    private function validarDepto(?string $depto)
    {
        if (Input::esNotNullVacioBlanco($depto)) {

            if (!$this->_esStringLongitud($depto, 1, 10)) {
                throw new InvalidArgumentException(message: 'El Depto debe ser un string de al menos un caracter y un máximo de 10.');
            }

            if (!$this->_esDigitoNegativoOPositivo($depto) && !$this->_esLetraSinTilde($depto) && !$this->_esAlfaNumerico($depto)) {
                throw new InvalidArgumentException(message: 'El Depto debe ser un número entero o un string alfanumérico sin espacios en blanco ni caracteres especiales.');
            }
        }
    }

    private function validarExisteDomicilioModificar(int $domId, mysqli $linkExterno)
    {
        if (!$this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM domicilio WHERE domId = $domId AND domFechaBaja IS NULL", msg: 'obtener un domicilio por id para modificar')) {
            throw new CustomException(code: 409, message: 'El domicilio a modificar no existe.');
        }
    }

    private function validarSiYaFueRegistrado(DomicilioCreacionDTO | DomicilioDTO $domicilio, mysqli $linkExterno)
    {
        $qPiso = Input::esNotNullVacioBlanco($domicilio->domPiso) ? " AND domPiso = '{$domicilio->domPiso}'" : ' AND domPiso IS NULL';
        $qDepto = Input::esNotNullVacioBlanco($domicilio->domDepto) ? " AND domDepto = '{$domicilio->domDepto}'" : ' AND domDepto IS NULL';

        if ($domicilio instanceof DomicilioDTO) {
            $query = "SELECT 1 FROM domicilio WHERE domCalleRuta = '{$domicilio->domCalleRuta}' AND domNroKm = {$domicilio->domNroKm} $qPiso $qDepto AND domLocId = {$domicilio->localidad->locId} AND domId <> {$domicilio->domId} AND domFechaBaja IS NULL";
            if ($this->_existeEnBD(link: $linkExterno, query: $query, msg: 'verificar si el domicilio ya fue registrado')) {
                throw new CustomException(code: 409, message: 'El domicilio ya fue registrado.');
        }
        } else {
            $query = "SELECT domId FROM domicilio WHERE domCalleRuta = '{$domicilio->domCalleRuta}' AND domNroKm = {$domicilio->domNroKm} $qPiso $qDepto AND domLocId = {$domicilio->localidad->locId} AND domFechaBaja IS NULL";
            $id = $this->_existeEnBD(link: $linkExterno, query: $query, msg: 'verificar si el domicilio ya fue registrado', columnId: 'domId');
            if ($id !== 0) {
                throw new CustomException(code: 409, message: "El domicilio ya fue registrado bajo el ID_$id"); //TODO: El Frontend debe manejar este mensaje para capturar el ID y llamar al controller de usuarioDomicilio
            }
        }
        
    }
}
