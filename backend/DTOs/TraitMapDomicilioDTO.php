<?php

trait TraitMapDomicilioDTO
{
    use TraitMapLocalidadDTO;

    /**
     * Mapea un array o stdClass a un objeto DomicilioDTO.
     *
     * @param array|stdClass $data Datos a mapear.
     * @return DomicilioDTO|array|null Objeto DomicilioDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapDomicilioDTO(array | stdClass $data, bool $returnArray = false): DomicilioDTO | null | array
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayDom = [];

        //$domicilioDTO = null;

        // Verifica si el array o stdClass contiene la clave 'domicilio' y crea y retorna un objeto DomicilioDTO
        if (array_key_exists('domicilio', $data))
             return $returnArray ? get_object_vars(new DomicilioDTO($data['domicilio'])) : new DomicilioDTO($data['domicilio']);


        //Si no existe la clave 'domicilio' se verifica si existe la clave 'domId' o 'usrDomicilio'
        // y se asigna el valor a la clave 'domId' del array $arrayDom
        // de lo contrario retorna null

        if (array_key_exists('domId', $data))
            $arrayDom['domId'] = $data['domId'];
        else if (array_key_exists('usrDomicilio', $data))
            $arrayDom['domId'] = $data['usrDomicilio'];
        else if (array_key_exists('tisDomTasId', $data))
            $arrayDom['domId'] = $data['tisDomTasId'];
        else if (array_key_exists('udomDom', $data))
            $arrayDom['domId'] = (int)$data['udomDom'];
        else
            return null;


        // Se asignan los valores de las claves 'domCPA', 'domCalleRuta', 'domNroKm', 'domPiso' y 'domDepto'

        if (array_key_exists('domCPA', $data))
            $arrayDom['domCPA'] = (string)$data['domCPA'];
        if (array_key_exists('domCalleRuta', $data))
            $arrayDom['domCalleRuta'] = (string)$data['domCalleRuta'];
        if (array_key_exists('domNroKm', $data))
            $arrayDom['domNroKm'] = (int)$data['domNroKm'];
        if (array_key_exists('domPiso', $data))
            $arrayDom['domPiso'] = (string)$data['domPiso'];
        if (array_key_exists('domDepto', $data))
            $arrayDom['domDepto'] = (string)$data['domDepto'];

        $arrayLoc = $this->mapLocalidadDTO($data, $returnArray);
        if ($arrayLoc) {
            $arrayDom['localidad'] = $arrayLoc;
        }

        // Si $returnArray es true, retorna el array $arrayDom
        // de lo contrario retorna un objeto DomicilioDTO creado con el array $arrayDom
        return $returnArray ? $arrayDom : new DomicilioDTO($arrayDom);

    }
}
