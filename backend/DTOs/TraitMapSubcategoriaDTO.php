<?php

trait TraitMapSubcategoriaDTO
{
    use TraitMapCategoriaDTO;

    /**
     * Mapea un array o stdClass a un objeto SubcategoriaDTO.
     *
     * @param array|stdClass $data
     * @return SubcategoriaDTO|array|null Objeto SubcategoriaDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapSubcategoriaDTO(array | stdClass $data, bool $returnArray = false): SubcategoriaDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arraySub = [];

        if (array_key_exists('subcategoria', $data))
            return $returnArray ? get_object_vars(new SubcategoriaDTO($data['subcategoria'])) : new SubcategoriaDTO($data['subcategoria']);

        if (array_key_exists('scatId', $data))
            $arraySub['scatId'] = (int)$data['scatId'];
        else if (array_key_exists('antScatId', $data))
            $arraySub['scatId'] = (int)$data['antScatId'];
        else
            return null;

        if (array_key_exists('scatDescripcion', $data))
            $arraySub['scatDescripcion'] = (string)$data['scatDescripcion'];

        $arrayCat = $this->mapCategoriaDTO($data, $returnArray);
        if ($arrayCat) {
            $arraySub['categoria'] = $arrayCat;
        }

        return $returnArray ? $arraySub : new SubcategoriaDTO($arraySub);
    }
}
