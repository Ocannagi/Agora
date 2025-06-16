<?php

use Model\CustomException;
use Utilidades\Querys;


trait TraitValidarDomicilio
{
    private function validarDomicilioDTO(DomicilioDTO $domicilio, mysqli $linkExterno)
    {
        if (!($domicilio instanceof DomicilioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de domicilio no es del tipo correcto.');
        }
        
        if (!isset($domicilio->domId)) {
            throw new InvalidArgumentException(message: 'El ID del domicilio no fue proporcionado.');
        }
        
        if ($domicilio->domId <= 0) {
            throw new InvalidArgumentException(message: 'El ID del domicilio no es válido: ' . $domicilio->domId);
        }
        
        if (is_int($domicilio->domId)) {
            if (!$this->_existeDomicilio($linkExterno, $domicilio->domId))
                throw new CustomException(code: 409, message: 'No está registrado el domicilo enviado');
        } else
            throw new InvalidArgumentException(message: 'El usrDomicilio/domId debe ser un integer, no debe enviarse como string.');
    }

    private function _existeDomicilio($link, int $domicilio)
    {
        $sql = "SELECT 1 FROM domicilio WHERE domId = $domicilio";
        return Querys::existeEnBD($link, $sql, 'obtener un domicilio por id');
    }

}