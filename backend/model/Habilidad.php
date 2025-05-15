<?php

use Utilidades\Obligatorio;

class Habilidad extends ClassBase
{
    private int $utsId; // ID de la habilidad.
    #[Obligatorio]
    private Usuario $usuario; // Usuario al que pertenece la habilidad.
    #[Obligatorio]
    private Periodo $periodo; // Periodo de la habilidad.
    #[Obligatorio]
    private Subcategoria $subcategoria; // Subcategoría de la habilidad.
    private DateTime $utsFechaInsert;
    private ?DateTime $utsFechaBaja;

    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof HabilidadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->usuario = Usuario::fromArray(['usrId' => $dto->usrId]);
        $instance->periodo = Periodo::fromArray(get_object_vars($dto->periodo));
        $instance->subcategoria = Subcategoria::fromArray(get_object_vars($dto->subcategoria));
        return $instance;

    }
}