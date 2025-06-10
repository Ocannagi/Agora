<?php

use Utilidades\Obligatorio;

class Tasacion extends ClassBase
{
    private int $tasId;
    #[Obligatorio]
    private Usuario $tasador;
    #[Obligatorio]
    private Usuario $propietario;
    #[Obligatorio]
    private Antiguedad $antiguedad;
    #[Obligatorio]
    private DateTime $tasFechaSolicitud;
    private ?DateTime $tasFechaTasProviRealizada;
    private ?string $tasObservacionesProv = null;
    private ?float $tasPrecioProvisoria = null;
    private ?int $tasTisId = null; 
    private ?DateTime $tasFechaBaja = null;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof TasacionCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->tasador = Usuario::fromArray(get_object_vars($dto->tasador));
        $instance->propietario = Usuario::fromArray(get_object_vars($dto->propietario));
        $instance->antiguedad = Antiguedad::fromArray(get_object_vars($dto->antiguedad));
        $instance->tasFechaSolicitud = DateTime::createFromFormat('Y-m-d', $dto->tasFechaSolicitud);
        return $instance;
    }


}