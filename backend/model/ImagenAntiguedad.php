<?php

class ImagenAntiguedad extends ClassBase
{
    private int $imaId;
    private int $antId;
    private string $imaUrl;
    private string $imaDescripcion;
    private DateTime $imaFechaInsert;
    private ?DateTime $imaFechaBaja;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof ImagenAntiguedadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->imaUrl = $dto->imaUrl;
        $instance->imaDescripcion = $dto->imaDescripcion;
        $instance->antId = $dto->antId;
        return $instance;
    }

}