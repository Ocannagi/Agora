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
        $instance->antiguedad = Antiguedad::fromArray(get_object_vars($dto->antiguedad)); // Convertir el DTO de antigüedad a objeto
        $instance->domicilio = Domicilio::fromArray(get_object_vars($dto->domicilio)); // Convertir el DTO de domicilio a objeto
        $instance->aavPrecioVenta = $dto->aavPrecioVenta;
        $instance->tasacionDigital = TasacionDigital::fromArray(get_object_vars($dto->tasacion));
        $instance->aavFechaPublicacion = DateTime::createFromFormat('Y-m-d', $dto->aavFechaPublicacion);
        $instance->aavFechaRetiro = $dto->aavFechaRetiro !== null ? DateTime::createFromFormat('Y-m-d', $dto->aavFechaRetiro) : null;

        return $instance;
    }
}