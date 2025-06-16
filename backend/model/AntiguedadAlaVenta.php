<?php
use Utilidades\Obligatorio;


class AntiguedadAlaVenta extends ClassBase
{
    private int $aavId;
    private Antiguedad $antiguedad;
    private Domicilio $domicilio;
    private float $aavPrecioVenta;
    private TasacionDigital $tasacionDigital;
    private DateTime $aavFechaPublicacion;
    private ?DateTime $aavFechaRetiro;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof AntiguedadAlaVentaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->aavAntId = $dto->aavAntId;
        $instance->aavDomOrigen = $dto->aavDomOrigen;
        $instance->aavPrecioVenta = $dto->aavPrecioVenta;
        $instance->aavTadId = $dto->aavTadId;
        $instance->aavFechaPublicacion = $dto->aavFechaPublicacion;
        $instance->aavFechaRetiro = $dto->aavFechaRetiro;

        return $instance;
    }
}