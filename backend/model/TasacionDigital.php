<?php

use Utilidades\Obligatorio;

class TasacionDigital extends ClassBase
{
    private int $tadId;
    #[Obligatorio]
    private Usuario $tasador;
    #[Obligatorio]
    private Usuario $propietario;
    #[Obligatorio]
    private Antiguedad $antiguedad;
    private DateTime $tadFechaSolicitud;
    private ?DateTime $tadFechaTasDigitalRealizada;
    private ?DateTime $tadFechaTasDigitalRechazada = null;
    private ?string $tadObservacionesDigital = null;
    private ?float $tadPrecioDigital = null;
    private ?DateTime $tadFechaBaja = null;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof TasacionDigitalCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->tasador = Usuario::fromArray(['usrId' => $dto->tasador->usrId]);
        $instance->propietario = Usuario::fromArray(['usrId' => $dto->propietario->usrId]);
        $instance->antiguedad = Antiguedad::fromArray(['antId' => $dto->antiguedad->antId]);
        $instance->tadFechaSolicitud = new DateTime();
        return $instance;
    }


}