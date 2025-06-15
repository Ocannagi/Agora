<?php

use Model\CustomException;
use Utilidades\Input;

trait TraitValidarTasacion
{
    use TraitGetInterno;
    use TraitGetByIdInterno;

    private function validarTasacionDigitalCreacionDTO(TasacionDigitalCreacionDTO $tasacionDigital, mysqli $linkExterno, bool $corroborarExistencia = true)
    {
        if (!($tasacionDigital instanceof TasacionDigitalCreacionDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasación digital no es del tipo correcto.');
        }

        if (!isset($tasacionDigital->tasador) || !($tasacionDigital->tasador instanceof UsuarioDTO)) {
            throw new InvalidArgumentException(message: 'El tasador no fue proporcionado o no es del tipo correcto.');
        }

        if (!isset($tasacionDigital->propietario) || !($tasacionDigital->propietario instanceof UsuarioDTO)) {
            throw new InvalidArgumentException(message: 'El propietario no fue proporcionado o no es del tipo correcto.');
        }

        if (!isset($tasacionDigital->antiguedad) || !($tasacionDigital->antiguedad instanceof AntiguedadDTO)) {
            throw new InvalidArgumentException(message: 'La antigüedad no fue proporcionada o no es del tipo correcto.');
        }

        $this->validarTasador($tasacionDigital->tasador, $linkExterno, $corroborarExistencia);
        $this->validarPropietario($tasacionDigital->propietario, $linkExterno, $corroborarExistencia);
        $this->validarAntiguedadDTO($tasacionDigital->antiguedad, $linkExterno, $corroborarExistencia);
        $this->validarAntieguedadPropietario($tasacionDigital->antiguedad, $tasacionDigital->propietario);
        $this->validarHabilidadesTasadorAntiguedad($tasacionDigital->tasador, $tasacionDigital->antiguedad, $linkExterno);
    }

    private function validarTasador(UsuarioDTO $tasadorDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($tasadorDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasador no es del tipo correcto.');
        }

        if (!isset($tasadorDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del tasador no fue proporcionado.');
        }

        if (!is_int($tasadorDTO->usrId) || $tasadorDTO->usrId <= 0) {
            throw new InvalidArgumentException(message: 'El id del tasador debe ser un entero mayor a cero.');
        }

        if (!isset($tasadorDTO->usrTipoUsuario)) {
            throw new InvalidArgumentException(message: 'El tipo de usuario del tasador no fue proporcionado.');
        }

        if (in_array($tasadorDTO->usrTipoUsuario, TipoUsuarioEnum::tasadorToArray()) === false) {
            throw new InvalidArgumentException(message: 'El usuario tasador debe ser de tipo "tasador".');
        }

        if ($corroborarExistencia) {

            if (!$this->_existeEnBD(
                link: $linkExterno,
                query: "SELECT usrId FROM usuario WHERE usrId = $tasadorDTO->usrId AND usrFechaBaja IS NULL AND usrTipoUsuario = '{$tasadorDTO->usrTipoUsuario}'",
                msg: 'validar tasador'
            )) {
                throw new InvalidArgumentException(message: 'El tasador no existe en la base de datos.');
            }
        }
    }

    private function validarPropietario(UsuarioDTO $propietarioDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($propietarioDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de propietario no es del tipo correcto.');
        }

        if (!isset($propietarioDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del propietario no fue proporcionado.');
        }

        if (!is_int($propietarioDTO->usrId) || $propietarioDTO->usrId <= 0) {
            throw new InvalidArgumentException(message: 'El id del propietario debe ser un entero mayor a cero.');
        }

        if (!isset($propietarioDTO->usrTipoUsuario)) {
            throw new InvalidArgumentException(message: 'El tipo de usuario del propietario no fue proporcionado.');
        }

        if (in_array($propietarioDTO->usrTipoUsuario, TipoUsuarioEnum::compradorVendedorToArray()) === false) {
            throw new InvalidArgumentException(message: 'El usuario propietario debe ser de tipo "anticuario" o "general".');
        }

        if ($corroborarExistencia) {

            if (!$this->_existeEnBD(
                link: $linkExterno,
                query: "SELECT usrId FROM usuario WHERE usrId = $propietarioDTO->usrId AND usrFechaBaja IS NULL AND usrTipoUsuario = '{$propietarioDTO->usrTipoUsuario}'",
                msg: 'validar propietario'
            )) {
                throw new InvalidArgumentException(message: 'El propietario no existe en la base de datos.');
            }
        }
    }

    private function validarAntiguedadDTO(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($antiguedadDTO->antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        $this->validarPeriodo($antiguedadDTO->periodo, $linkExterno, $corroborarExistencia);
        $this->validarSubcategoria($antiguedadDTO->subcategoria, $linkExterno, $corroborarExistencia);
        $this->_validarAntiguedadDTO($antiguedadDTO, $linkExterno, $corroborarExistencia);
    }

    private function validarPeriodo(PeriodoDTO $periodoDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($periodoDTO instanceof PeriodoDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de periodo no es del tipo correcto.');
        }

        if (!isset($periodoDTO->perId)) {
            throw new InvalidArgumentException(message: 'El id del periodo no fue proporcionado.');
        }

        if (!is_int($periodoDTO->perId)) {
            throw new InvalidArgumentException(message: 'El id del periodo no es un número entero.');
        }

        if ($periodoDTO->perId <= 0) {
            throw new InvalidArgumentException(message: "El id del periodo no es válido: $periodoDTO->perId");
        }

        if ($corroborarExistencia) {
            if (!$this->_existePeriodo($periodoDTO->perId, $linkExterno)) {
                throw new CustomException(code: 409, message: "El periodo con id $periodoDTO->perId no existe.");
            }
        }
    }

    private function _existePeriodo(int $perId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM periodo WHERE perId=$perId AND perFechaBaja IS NULL";
        return $this->_existeEnBD($linkExterno, $query, "obtener un periodo por id");
    }

    private function validarSubcategoria(SubcategoriaDTO $subcategoriaDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($subcategoriaDTO instanceof SubcategoriaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de subcategoría no es del tipo correcto.');
        }

        if (!isset($subcategoriaDTO->scatId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría no fue proporcionado.');
        }

        if (!is_int($subcategoriaDTO->scatId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría no es un número entero.');
        }

        if ($subcategoriaDTO->scatId <= 0) {
            throw new InvalidArgumentException(message: "El id de la subcategoría no es válido: $subcategoriaDTO->scatId");
        }

        if ($corroborarExistencia) {
            if (!$this->_existeSubcategoria($subcategoriaDTO->scatId, $linkExterno)) {
                throw new CustomException(code: 409, message: "La subcategoría con id $subcategoriaDTO->scatId no existe.");
            }
        }
    }

    private function _existeSubcategoria(int $scatId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM subcategoria WHERE scatId=$scatId AND scatFechaBaja IS NULL";
        return $this->_existeEnBD($linkExterno, $query, "obtener una subcategoría por id");
    }

    private function _validarAntiguedadDTO(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno, $corroborarExistencia = true)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($antiguedadDTO->antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        if (!is_int($antiguedadDTO->antId) || $antiguedadDTO->antId <= 0) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad debe ser un entero mayor a cero.');
        }

        if (!isset($antiguedadDTO->tipoEstado) || !($antiguedadDTO->tipoEstado instanceof TipoEstadoEnum)) {
            throw new InvalidArgumentException(message: 'El tipo de estado de la antigüedad no fue proporcionado o o no es del tipo correcto.');
        }

        if ($antiguedadDTO->tipoEstado === TipoEstadoEnum::RetiradoNoDisponible) {
            throw new InvalidArgumentException(message: 'El tipo de estado de la antigüedad no puede ser "RetiradoNoDisponible".');
        }

        if ($corroborarExistencia) {
            if (!$this->_existeEnBD(
                link: $linkExterno,
                query: "SELECT antId FROM antiguedad
                    WHERE antId = $antiguedadDTO->antId
                    AND antUsrId = '{$antiguedadDTO->usuario->usrId}'
                    AND antTipoEstado = '{$antiguedadDTO->tipoEstado->value}'",
                msg: 'validar antigüedad'
            )) {
                throw new InvalidArgumentException(message: 'La antigüedad no existe en la base de datos con los datos consignados.');
            }
        }
    }

    private function validarAntieguedadPropietario(AntiguedadDTO $antiguedadDTO, UsuarioDTO $propietarioDTO)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!($propietarioDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de propietario no es del tipo correcto.');
        }

        if ($antiguedadDTO->usuario->usrId !== $propietarioDTO->usrId) {
            throw new InvalidArgumentException(message: 'El id del usuario de la antigüedad debe ser el mismo que el del propietario de la tasación digital.');
        }
    }

    private function validarHabilidadesTasadorAntiguedad(UsuarioDTO $tasador, AntiguedadDTO $antiguedad, mysqli $linkExterno)
    {
        if (!($tasador instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasador no es del tipo correcto.');
        }

        if (!($antiguedad instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($tasador->usrId)) {
            throw new InvalidArgumentException(message: 'El id del tasador no fue proporcionado.');
        }

        $arrayHabilidadesMinDTO = $this->getInterno(
            query: "SELECT 
                        utsId
                        
                        ,usrId

                        ,perId, perDescripcion

                        ,scatId, catId, catDescripcion, scatDescripcion

                  FROM usuariotasadorhabilidad
                  INNER JOIN usuario ON utsUsrId = usrId
                  INNER JOIN periodo ON utsPerId = perId
                  INNER JOIN subcategoria ON utsScatId = scatId
                  INNER JOIN categoria ON scatCatId = catId
                  WHERE utsUsrId = $tasador->usrId",
            classDTO: HabilidadMinDTO::class,
            linkExterno: $linkExterno
        );

        if (empty($arrayHabilidadesMinDTO)) {
            throw new CustomException(code: 409, message: "El tasador con id $tasador->usrId no tiene habilidades registradas.");
        }

        $habilidadesValidas = array_filter($arrayHabilidadesMinDTO, function (HabilidadMinDTO $habilidad) use ($antiguedad) {
            return $habilidad->subcategoria->scatId === $antiguedad->subcategoria->scatId && $habilidad->periodo->perId === $antiguedad->periodo->perId;
        });

        if (empty($habilidadesValidas)) {
            throw new CustomException(code: 409, message: "El tasador con id $tasador->usrId no tiene habilidades para la antigüedad con id $antiguedad->antId.");
        }
    }


    /** TasacionDigitalDTO */


    private function validarTasacionDigitalDTO(TasacionDigitalDTO $tasacionDigitalDTO): void
    {
        if (!($tasacionDigitalDTO instanceof TasacionDigitalDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de tasación digital no es del tipo correcto.');
        }

        if (!isset($tasacionDigitalDTO->tadId)) {
            throw new InvalidArgumentException(message: 'El ID de la tasación digital no fue proporcionado.');
        }

        if ($tasacionDigitalDTO->tadId <= 0) {
            throw new InvalidArgumentException(message: 'El ID de la tasación digital no es válido: ' . $tasacionDigitalDTO->tadId);
        }

        $this->validarObservacionesDigital($tasacionDigitalDTO->tadObservacionesDigital);
        $this->validarFechasTasacionDigitalDTO($tasacionDigitalDTO);
        $this->validarPrecioDigital($tasacionDigitalDTO);
    }


    private function validarObservacionesDigital(?string $observacionesDigital)
    {
        if ($observacionesDigital !== null && !Input::esStringLongitud($observacionesDigital, 1, 500)) {
            throw new InvalidArgumentException(message: 'Las observaciones digitales deben ser un string de al menos un caracter y un máximo de 500.');
        }
    }

    private function validarFechasTasacionDigitalDTO(TasacionDigitalDTO $tasacionDigital)
    {
        if (Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRealizada)) {
            Input::esFechaValida($tasacionDigital->tadFechaTasDigitalRealizada);
        }

        if (Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRechazada)) {
            Input::esFechaValida($tasacionDigital->tadFechaTasDigitalRechazada);
        }

        if (Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRealizada) && Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRechazada)) {
            throw new InvalidArgumentException(message: 'No se puede establecer una fecha de tasación digital realizada y rechazada al mismo tiempo.');
        }

        if (!Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRealizada) && !Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRechazada)) {
            throw new InvalidArgumentException(message: 'Debe establecer al menos una fecha de tasación digital realizada o rechazada.');
        }

        $fSolicitud = new DateTime($tasacionDigital->tadFechaSolicitud);

        if (Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRealizada)) {
            $fRealizada = new DateTime($tasacionDigital->tadFechaTasDigitalRealizada);
            if ($fRealizada < $fSolicitud) {
                throw new InvalidArgumentException(message: 'La fecha de la tasación digital realizada no puede ser anterior a la fecha de solicitud.');
            }
        }
        if (Input::esNotNullVacioBlanco($tasacionDigital->tadFechaTasDigitalRechazada)) {
            $fRechazada = new DateTime($tasacionDigital->tadFechaTasDigitalRechazada);
            if ($fRechazada < $fSolicitud) {
                throw new InvalidArgumentException(message: 'La fecha de la tasación digital rechazada no puede ser anterior a la fecha de solicitud.');
            }
        }
    }

    private function validarPrecioDigital(TasacionDigitalDTO $tasacionDigitalDTO)
    {
        if ($tasacionDigitalDTO->tadPrecioDigital !== null) {
            if (!is_numeric($tasacionDigitalDTO->tadPrecioDigital)) {
                throw new InvalidArgumentException(message: 'El precio digital debe ser un número.');
            }
            if ($tasacionDigitalDTO->tadPrecioDigital < 0) {
                throw new InvalidArgumentException(message: 'El precio digital no puede ser negativo.');
            }
            if ($tasacionDigitalDTO->tadPrecioDigital > 9999999999999.99) {
                throw new InvalidArgumentException(message: 'El precio digital no puede ser mayor a 9999999999999.99');
            }
        }

        if (Input::esNotNullVacioBlanco($tasacionDigitalDTO->tadFechaTasDigitalRealizada) && $tasacionDigitalDTO->tadPrecioDigital === null) {
            throw new InvalidArgumentException(message: 'El precio digital debe ser proporcionado si la tasación digital fue realizada.');
        }
        if (Input::esNotNullVacioBlanco($tasacionDigitalDTO->tadFechaTasDigitalRechazada) && $tasacionDigitalDTO->tadPrecioDigital !== null) {
            throw new InvalidArgumentException(message: 'El precio digital no debe ser proporcionado si la tasación digital fue rechazada.');
        }
    }
}
