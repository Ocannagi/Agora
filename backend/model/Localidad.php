
<?php

use Utilidades\Obligatorio;

class Localidad extends ClassBase
{
    private int $locId;
    #[Obligatorio]
    private string $locDescripcion;
    #[Obligatorio]
    private Provincia $provincia;
    private DateTime $locFechaInsert;
    private ?DateTime $locFechaBaja;

    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof LocalidadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->locDescripcion = $dto->locDescripcion;
        $instance->provincia = Provincia::fromArray(get_object_vars($dto->provincia));
        return $instance;

    }

}