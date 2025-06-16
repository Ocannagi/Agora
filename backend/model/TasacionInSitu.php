<?php

use Utilidades\Obligatorio;
class TasacionInSitu extends ClassBase
{
    private int $tisId;
    #[Obligatorio]
    private TasacionDigital $tasacionDigital;
    #[Obligatorio]
    private Domicilio $domicilio; // Identificador del domicilio de la tasación in situ.
    private DateTime $tisFechaTasInSituSolicitada;
    #[Obligatorio]
    private DateTime $tisFechaTasInSituProvisoria;
    private ?DateTime $tisFechaTasInSituRealizada = null;
    private ?DateTime $tisFechaTasInSituRechazada  = null;
    private ?string $tisObservacionesInSitu = null;
    private ?float $tisPrecioInSitu = null;
    private ?DateTime $tisFechaBaja = null;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof TasacionInSituCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->tasacionDigital = TasacionDigital::fromArray(get_object_vars($dto->tasacionDigital));
        $instance->domicilio = Domicilio::fromArray(get_object_vars($dto->domicilio));
        $instance->tisFechaTasInSituSolicitada = new DateTime();
        $instance->tisFechaTasInSituProvisoria = new DateTime($dto->tisFechaTasInSituProvisoria);
        return $instance;
    }
}