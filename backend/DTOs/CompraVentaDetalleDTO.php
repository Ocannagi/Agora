<?php

class CompraVentaDetalleDTO implements IDTO
{
    use TraitMapAntiguedadAlaVentaDTO; // Trait para mapear AntiguedadAlaVentaDTO
    use TraitMapDomicilioDTO; // Trait para mapear DomicilioDTO

    public int $cvdId; // CompraVentaDetalle ID
    public int $covId; // CompraVenta ID
    public AntiguedadAlaVentaDTO $antiguedadAlaVenta; // AntiguedadAlaVenta
    public string $cvdFechaEntregaPrevista; // Fecha de entrega prevista en formato 'Y-m-d H:i:s'
    public ?string $cvdFechaEntregaReal = null; // Fecha de entrega real en formato 'Y-m-d H:i:s'

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('cvdId', $data)) {
            $this->cvdId = (int)$data['cvdId'];
        }

        if (array_key_exists('covId', $data)) {
            $this->covId = (int)$data['covId'];
        } else if (array_key_exists('cvdCovId', $data)) {
            $this->covId = (int)$data['cvdCovId'];
        }

        if (array_key_exists('antiguedadAlaVenta', $data)) {
            if ($data['antiguedadAlaVenta'] instanceof AntiguedadAlaVentaDTO) {
                $this->antiguedadAlaVenta = $data['antiguedadAlaVenta'];
            } else {
                $aavDTO = $this->mapAntiguedadAlaVentaDTO($data['antiguedadAlaVenta']);
                if ($aavDTO !== null) {
                    $this->antiguedadAlaVenta = $aavDTO;
                }
            }
        } else if (array_key_exists('cvdAavId', $data)) {
            $this->antiguedadAlaVenta = new AntiguedadAlaVentaDTO(['aavId' => (int)$data['cvdAavId']]);
        } else if (array_key_exists('aavId', $data)) {
            $this->antiguedadAlaVenta = new AntiguedadAlaVentaDTO(['aavId' => (int)$data['aavId']]);
        }

        if (array_key_exists('cvdFechaEntregaPrevista', $data)) {
            $this->cvdFechaEntregaPrevista = (string)$data['cvdFechaEntregaPrevista'];
        }

        if (array_key_exists('cvdFechaEntregaReal', $data)) {
            $this->cvdFechaEntregaReal = $data['cvdFechaEntregaReal'] !== null ? (string)$data['cvdFechaEntregaReal'] : null;
        }
    }
}