<?php

use Utilidades\Obligatorio;

class CompraVentaDetalle extends ClassBase
{
    private int $cvdId; // CompraVentaDetalle ID
    private int $covId; // CompraVenta ID
    #[Obligatorio]
    private AntiguedadAlaVenta $antiguedadAlaVenta; // AntiguedadAlaVenta
    #[Obligatorio]
    private DateTime $cvdFechaEntregaPrevista; // Fecha de entrega prevista
    private ?DateTime $cvdFechaEntregaReal = null; // Fecha de entrega real
    private ?DateTime $cvdFechaBaja = null; // Fecha de baja


    public  static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof CompraVentaDetalleCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->antiguedadAlaVenta = AntiguedadAlaVenta::fromArray(['aavId' => $dto->antiguedadAlaVenta->aavId]);
        $instance->cvdFechaEntregaPrevista = new DateTime($dto->cvdFechaEntregaPrevista);
        return $instance;
    }
}
