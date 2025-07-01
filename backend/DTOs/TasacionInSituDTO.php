<?php

use Utilidades\Input;

class TasacionInSituDTO implements IDTO
{
    public int $tisId;
    public int $tadId; // Tasación digital asociada a la tasación in situ.
    public DomicilioDTO $domicilio; // Identificador del domicilio de la tasación in situ.
    public string $tisFechaTasInSituSolicitada; // Fecha solicitada para la tasación in situ.
    public string $tisFechaTasInSituProvisoria; // Fecha provisoria para la tasación in situ.
    public ?string $tisFechaTasInSituRealizada = null; // Fecha en que se realizó la tasación in situ.
    public ?string $tisFechaTasInSituRechazada = null; // Fecha en que se rechazó la tasación in situ.
    public ?string $tisObservacionesInSitu = null; // Observaciones de la tasación in situ.
    public ?float $tisPrecioInSitu = null; // Precio de la tasación in situ.

    use TraitMapDomicilioDTO; // Trait para mapear el domicilio desde el array o stdClass.
    

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('tisId', $data)) {
            $this->tisId = (int)$data['tisId'];
        }

        if (array_key_exists('tadId', $data)) {
            $this->tadId = (int)$data['tadId'];
        } else if (array_key_exists('tisTadId', $data)) {
            $this->tadId = (int)$data['tisTadId'];
        }

        if (array_key_exists('domicilio', $data) && $data['domicilio'] instanceof DomicilioDTO) {
            $this->domicilio = $data['domicilio'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data);
            if ($domicilioDTO !== null) {
                $this->domicilio = $domicilioDTO;
            }
        }

        if (array_key_exists('tisFechaTasInSituSolicitada', $data)) {
            $this->tisFechaTasInSituSolicitada = (string)$data['tisFechaTasInSituSolicitada'];
        }
        if (array_key_exists('tisFechaTasInSituProvisoria', $data)) {
            $this->tisFechaTasInSituProvisoria = (string)$data['tisFechaTasInSituProvisoria'];
        }
        if (array_key_exists('tisFechaTasInSituRealizada', $data)) {
            $this->tisFechaTasInSituRealizada = (string)$data['tisFechaTasInSituRealizada'];
        }
        if (array_key_exists('tisFechaTasInSituRechazada', $data)) {
            $this->tisFechaTasInSituRechazada = (string)$data['tisFechaTasInSituRechazada'];
        }
        if (array_key_exists('tisObservacionesInSitu', $data)) {
            $this->tisObservacionesInSitu = (string)$data['tisObservacionesInSitu'];
        }
        if (array_key_exists('tisPrecioInSitu', $data)) {
            $this->tisPrecioInSitu = Input::esNotNullVacioBlanco($data['tisPrecioInSitu']) ? (float)$data['tisPrecioInSitu'] : null;
        }

    }
}
