<?php

trait TraitMapPeriodoDTO 
{
    /**
     * Mapea un array o stdClass a un objeto PeriodoDTO.
     *
     * @param array|stdClass $data
     * @return PeriodoDTO|array|null Objeto PeriodoDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapPeriodoDTO(array | stdClass $data, bool $returnArray = false): PeriodoDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayPer = [];

        if (array_key_exists('periodo', $data))
            return $returnArray ? get_object_vars(new PeriodoDTO($data['periodo'])) : new PeriodoDTO($data['periodo']);

        if (array_key_exists('perId', $data))
            $arrayPer['perId'] = (int)$data['perId'];
        else if (array_key_exists('antPerId', $data))
            $arrayPer['perId'] = (int)$data['antPerId'];
        else
            return null;

        if (array_key_exists('perDescripcion', $data))
            $arrayPer['perDescripcion'] = (string)$data['perDescripcion'];

        return $returnArray ? $arrayPer : new PeriodoDTO($arrayPer);
    }
}