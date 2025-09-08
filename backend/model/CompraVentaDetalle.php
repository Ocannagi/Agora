<?php

use Utilidades\Obligatorio;

class CompraVentaDetalle extends ClassBase
{
    private int $cvdId; // CompraVentaDetalle ID
    #[Obligatorio]
    private int $covId; // CompraVenta ID
    #[Obligatorio]
    private int $aavId; // AntiguedadAlaVenta ID
    #[Obligatorio]
    private int $domId; // Domicilio Destino ID
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
        $instance->covId = $dto->covId;
        $instance->aavId = $dto->aavId;
        $instance->domId = $dto->domId;
        $instance->cvdFechaEntregaPrevista = new DateTime($dto->cvdFechaEntregaPrevista);
        return $instance;
    }
}
