
<?php

use Utilidades\Obligatorio;

class Localidad extends ClassBase
{
    private int $locId;
    #[Obligatorio]
    private string $locDescripcion;
    #[Obligatorio]
    private int $locProvId;

    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof LocalidadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->locDescripcion = $dto->locDescripcion;
        $instance->locProvId = $dto->locProvId;
        return $instance;

    }

}