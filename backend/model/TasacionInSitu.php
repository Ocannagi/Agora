<?php

use Utilidades\Obligatorio;
class TasacionInSitu extends ClassBase
{
    private int $tisId;
    #[Obligatorio]
    private int $tisDomTasId; // Identificador del domicilio de la tasación in situ.
    private DateTime $tisFechaTasInSituSolicitada;
    #[Obligatorio]
    private DateTime $tisFechaTasInSituAcordada;
    private ?DateTime $tisFechaTasInSituRealizada = null;
    private ?DateTime $tisFechaTasInSituRechazada  = null;
    private ?string $tisObservacionesInSitu = null;
    private ?float $tisPrecioInSitu = null;
    private ?DateTime $tisFechaBaja = null;
    private bool $tisActivo = false;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof TasacionInSituCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->tisDomTasId = $dto->domicilio->domId;
        $instance->tisFechaTasInSituSolicitada = new DateTime();
        $instance->tisActivo = true; // Por defecto, una tasación in situ es activa al crearse
        return $instance;
        
    }
}