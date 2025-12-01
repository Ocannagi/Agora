<?php

class CompraVentaDetalleCreacionDTO implements ICreacionDTO
{
    use TraitMapAntiguedadAlaVentaDTO; // Trait para mapear AntiguedadAlaVentaDTO
    
    public AntiguedadAlaVentaDTO $antiguedadAlaVenta; // AntiguedadAlaVenta
    public string $cvdFechaEntregaPrevista; // Fecha de entrega prevista en formato 'Y-m-d H:i:s'

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if(array_key_exists('antiguedadAlaVenta', $data)) {
            if($data['antiguedadAlaVenta'] instanceof AntiguedadAlaVentaDTO) {
                $this->antiguedadAlaVenta = $data['antiguedadAlaVenta'];
            } else {
                $aavDTO = $this->mapAntiguedadAlaVentaDTO($data['antiguedadAlaVenta']);
                if($aavDTO !== null) {
                    $this->antiguedadAlaVenta = $aavDTO;
                }
            }
        } else if (array_key_exists('aavId', $data)) {
            $this->antiguedadAlaVenta = new AntiguedadAlaVentaDTO([ 'aavId' => (int)$data['aavId']]);
        }

        if (array_key_exists('cvdFechaEntregaPrevista', $data)) {
            $this->cvdFechaEntregaPrevista = (string)$data['cvdFechaEntregaPrevista'];
        }
    }
}