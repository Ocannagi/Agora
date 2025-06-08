<?php

use Utilidades\Obligatorio;

class ImagenAntiguedad extends ClassBase
{
    private int $imaId;
    #[Obligatorio]
    private int $antId;
    #[Obligatorio]
    private string $imaUrl;
    #[Obligatorio]
    private string $imaNombreArchivo;
    private DateTime $imaFechaInsert;
    #[Obligatorio]
    private int $imaOrden;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof ImagenAntiguedadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->imaUrl = $dto->imaUrl;
        $instance->antId = $dto->antId;
        $instance->imaNombreArchivo = $dto->imaNombreArchivo;
        return $instance;
    }

}