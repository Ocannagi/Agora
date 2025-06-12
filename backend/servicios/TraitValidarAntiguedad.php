<?php

use Model\CustomException;

trait TraitValidarAntiguedad
{
    private function validarAntiguedad(AntiguedadDTO $antiguedadDTO, mysqli $linkExterno)
    {
        if (!($antiguedadDTO instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if (!isset($antiguedadDTO->antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        $this->validarPeriodo($antiguedadDTO->periodo, $linkExterno);
        $this->validarSubcategoria($antiguedadDTO->subcategoria, $linkExterno);
    }
}