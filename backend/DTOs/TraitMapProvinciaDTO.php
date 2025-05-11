<?php

trait TraitMapProvinciaDTO
{
    /**
     * Mapea un array o stdClass a un objeto ProvinciaDTO.
     *
     * @param array|stdClass $data Datos a mapear.
     * @return ProvinciaDTO|array|null Objeto ProvinciaDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapProvinciaDTO(array | stdClass $data, bool $returnArray = false): ProvinciaDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayProv = [];

        if (array_key_exists('provincia', $data))
            return $returnArray ? get_object_vars(new ProvinciaDTO($data['provincia'])) : new ProvinciaDTO($data['provincia']);

        if (array_key_exists('provId', $data))
            $arrayProv['provId'] = (int)$data['provId'];
        else if (array_key_exists('locProvId', $data))
            $arrayProv['provId'] = (int)$data['locProvId'];
        else
            return null;

        if (array_key_exists('provDescripcion', $data))
            $arrayProv['provDescripcion'] = (string)$data['provDescripcion'];

        return $returnArray ? $arrayProv : new ProvinciaDTO($arrayProv);
    }
}
