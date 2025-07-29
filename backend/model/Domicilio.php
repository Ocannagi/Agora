<?php

use Utilidades\Obligatorio;

class Domicilio extends ClassBase
{
    private int $domId;
    #[Obligatorio]
    private string $domCPA; // Código Postal Argentino
    #[Obligatorio]
    private string $domCalleRuta;
    #[Obligatorio]
    private int $domNroKm;
    private ?string $domPiso;
    private ?string $domDepto;
    #[Obligatorio]
    private Localidad $localidad;

    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof DomicilioCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->domCalleRuta = $dto->domCalleRuta;
        $instance->domNroKm = $dto->domNroKm;
        $instance->domPiso = $dto->domPiso;
        $instance->domDepto = $dto->domDepto;
        $instance->localidad = Localidad::fromArray(['locId' => $dto->localidad->locId]);
        return $instance;
    }
}