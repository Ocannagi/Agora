<?php

trait TraitMapLocalidadDTO
{
    use TraitMapProvinciaDTO;
    
    /**
     * Mapea un array o stdClass a un objeto LocalidadDTO.
     *
     * @param array|stdClass $data Datos a mapear.
     * @return LocalidadDTO|array|null Objeto LocalidadDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapLocalidadDTO(array | stdClass $data, bool $returnArray = false): LocalidadDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayLoc = [];

        if (array_key_exists('localidad', $data))
            return $returnArray ? get_object_vars(new LocalidadDTO($data['localidad'])) : new LocalidadDTO($data['localidad']);

        if (array_key_exists('locId', $data))
            $arrayLoc['locId'] = (int)$data['locId'];
        else if (array_key_exists('domLocId', $data))
            $arrayLoc['locId'] = (int)$data['domLocId'];
        else
            return null;

        if (array_key_exists('locDescripcion', $data))
            $arrayLoc['locDescripcion'] = (string)$data['locDescripcion'];

        $arrayProv = $this->mapProvinciaDTO($data, $returnArray);
        if ($arrayProv) {
            $arrayLoc['provincia'] = $arrayProv;
        }

        return $returnArray ? $arrayLoc : new LocalidadDTO($arrayLoc);
    }
}
