<?php
use Utilidades\Obligatorio;


class AntiguedadAlaVenta extends ClassBase
{
    private int $aavId;
    #[Obligatorio]
    private Antiguedad $antiguedad;
    #[Obligatorio]
    private Domicilio $domicilio;
    #[Obligatorio]
    private float $aavPrecioVenta;
    private ?TasacionDigital $tasacionDigital = null;
    #[Obligatorio]
    private DateTime $aavFechaPublicacion;
    private ?DateTime $aavFechaRetiro = null;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof AntiguedadAlaVentaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->antiguedad = Antiguedad::fromArray(['antId' => $dto->antiguedad->antId]); // Convertir el DTO de antigüedad a objeto
        $instance->domicilio = Domicilio::fromArray(['domId' => $dto->domicilio->domId]); // Convertir el DTO de domicilio a objeto
        $instance->aavPrecioVenta = $dto->aavPrecioVenta;
        if (isset($dto->tasacion)) {
            $instance->tasacionDigital = TasacionDigital::fromArray(['tadId' => $dto->tasacion->tadId]);
        }
        $instance->aavFechaPublicacion = new DateTime('now'); // Asignar la fecha actual como fecha de publicación

        return $instance;
    }
}