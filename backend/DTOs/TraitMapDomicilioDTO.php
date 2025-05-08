<?php

trait TraitMapDomicilioDTO
{
    /**
     * Mapea un array o stdClass a un objeto DomicilioDTO.
     *
     * @param array|stdClass $data Datos a mapear.
     * @return DomicilioDTO|null Objeto DomicilioDTO o null si no se puede mapear.
     */
    private function mapDomicilio(array | stdClass $data): DomicilioDTO | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('domicilio', $data)) {
            return new DomicilioDTO($data['domicilio']);
        } else if (array_key_exists('usrDomicilio', $data)) {
            return new DomicilioDTO(['domId' => $data['usrDomicilio']]);
        } else if (array_key_exists('domId', $data)) {
            $arrayDom = ['domId' => (int)$data['domId']];
            if (array_key_exists('domCalleRuta', $data))
                $arrayDom['domCalleRuta'] = (string)$data['domCalleRuta'];
            if (array_key_exists('domNroKm', $data))
                $arrayDom['domNroKm'] = (int)$data['domNroKm'];
            if (array_key_exists('domPiso', $data))
                $arrayDom['domPiso'] = (string)$data['domPiso'];
            if (array_key_exists('domDepto', $data))
                $arrayDom['domDepto'] = (string)$data['domDepto'];
            if (array_key_exists('domCPA', $data))
                $arrayDom['domCPA'] = (string)$data['domCPA'];
            if (array_key_exists('localidad', $data)) {
                $arrayDom['localidad'] = new LocalidadDTO($data['localidad']);
            } else if (array_key_exists('locId', $data)) {
                $arrayLoc = ['locId' => (int)$data['locId']];
                if (array_key_exists('locDescripcion', $data))
                    $arrayLoc['locDescripcion'] = (string)$data['locDescripcion'];
                if (array_key_exists('provincia', $data)) {
                    $arrayLoc['provincia'] = new ProvinciaDTO($data['provincia']);
                } else if (array_key_exists('provId', $data)) {
                    $arrayProv = ['provId' => (int)$data['provId']];
                    if (array_key_exists('provDescripcion', $data))
                        $arrayProv['provDescripcion'] = (string)$data['provDescripcion'];
                    $arrayLoc['provincia'] = new ProvinciaDTO($arrayProv);
                }
                $arrayDom['localidad'] = new LocalidadDTO($arrayLoc);
            }

            return new DomicilioDTO($arrayDom);
        }

        return null;
    }
}
