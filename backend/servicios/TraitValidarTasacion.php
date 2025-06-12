<?php

use Model\CustomException;

trait TraitValidarTasacion
{
    use TraitGetInterno;

    private function validarTasacionDigital(TasacionDigitalCreacionDTO|TasacionDigitalDTO $tasacionDigital, mysqli $linkExterno)
    {
        if (!($tasacionDigital instanceof TasacionDigitalCreacionDTO) && !($tasacionDigital instanceof TasacionDigitalDTO)) {
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

        $this->validarTasador($tasacionDigital->tasador, $linkExterno);
        $this->validarPropietario($tasacionDigital->propietario, $linkExterno);
        $this->validarAntiguedadDTO($tasacionDigital->antiguedad, $linkExterno);
        $this->validarAntieguedadPropietario($tasacionDigital->antiguedad, $tasacionDigital->propietario, $linkExterno);
        $this->validarHabilidadesTasadorAntiguedad($tasacionDigital->tasador, $tasacionDigital->antiguedad, $linkExterno);
    }

    private function validarTasador(UsuarioDTO $tasadorDTO, mysqli $linkExterno)
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

        if(in_array($tasadorDTO->usrTipoUsuario, TipoUsuarioEnum::tasadorToArray()) === false) {
            throw new InvalidArgumentException(message: 'El usuario tasador debe ser de tipo "tasador".');
        }

        if(!$this->_existeEnBD(
            link: $linkExterno,
            query: "SELECT usrId FROM usuario WHERE usrId = $tasadorDTO->usrId AND usrFechaBaja IS NULL AND usrTipoUsuario = '{$tasadorDTO->usrTipoUsuario}'",
            msg: 'validar tasador'
        )) {
            throw new InvalidArgumentException(message: 'El tasador no existe en la base de datos.');
        }
    }
    
    private function validarPropietario(UsuarioDTO $propietarioDTO, mysqli $linkExterno)
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

        if(in_array($propietarioDTO->usrTipoUsuario, TipoUsuarioEnum::compradorVendedorToArray()) === false) {
            throw new InvalidArgumentException(message: 'El usuario propietario debe ser de tipo "anticuario" o "general".');
        }

        if(!$this->_existeEnBD(
            link: $linkExterno,
            query: "SELECT usrId FROM usuario WHERE usrId = $propietarioDTO->usrId AND usrFechaBaja IS NULL AND usrTipoUsuario = '{$propietarioDTO->usrTipoUsuario}'",
            msg: 'validar propietario'
        )) {
            throw new InvalidArgumentException(message: 'El propietario no existe en la base de datos.');
        }
    }
    
    private function validarAntiguedadDTO(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($antiguedadDTO->antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        $this->validarPeriodo($antiguedadDTO->periodo, $linkExterno);
        $this->validarSubcategoria($antiguedadDTO->subcategoria, $linkExterno);
        $this->_validarAntiguedadDTO($antiguedadDTO, $linkExterno);
    }

    private function validarPeriodo(PeriodoDTO $periodoDTO, mysqli $linkExterno)
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

        if (!$this->_existePeriodo($periodoDTO->perId, $linkExterno)) {
            throw new CustomException(code: 409, message: "El periodo con id $periodoDTO->perId no existe.");
        }
    }

    private function _existePeriodo(int $perId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM periodo WHERE perId=$perId AND perFechaBaja IS NULL";
        return $this->_existeEnBD($linkExterno, $query, "obtener un periodo por id");
    }

    private function validarSubcategoria(SubcategoriaDTO $subcategoriaDTO, mysqli $linkExterno)
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

        if (!$this->_existeSubcategoria($subcategoriaDTO->scatId, $linkExterno)) {
            throw new CustomException(code: 409, message: "La subcategoría con id $subcategoriaDTO->scatId no existe.");
        }
    }

    private function _existeSubcategoria(int $scatId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM subcategoria WHERE scatId=$scatId AND scatFechaBaja IS NULL";
        return $this->_existeEnBD($linkExterno, $query, "obtener una subcategoría por id");
    }

    private function _validarAntiguedadDTO(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno)
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

    private function validarAntieguedadPropietario(AntiguedadDTO $antiguedadDTO, UsuarioDTO $propietarioDTO, mysqli $linkExterno)
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
}
