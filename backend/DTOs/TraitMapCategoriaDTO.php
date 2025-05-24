<?php

trait TraitMapCategoriaDTO
{
    /**
     * Mapea un array o stdClass a un objeto CategoriaDTO.
     *
     * @param array|stdClass $data
     * @return CategoriaDTO|array|null Objeto CategoriaDTO, array de dicho objeto, o null si no se puede mapear.
     */
    
     private function mapCategoriaDTO(array | stdClass $data, bool $returnArray = false): CategoriaDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayCat = [];

        if (array_key_exists('categoria', $data))
            return $returnArray ? get_object_vars(new CategoriaDTO($data['categoria'])) : new CategoriaDTO($data['categoria']);

        if (array_key_exists('catId', $data))
            $arrayCat['catId'] = (int)$data['catId'];
        else if (array_key_exists('scatCatId', $data))
            $arrayCat['catId'] = (int)$data['scatCatId'];
        else
            return null;

        if (array_key_exists('catDescripcion', $data))
            $arrayCat['catDescripcion'] = (string)$data['catDescripcion'];

        return $returnArray ? $arrayCat : new CategoriaDTO($arrayCat);
    }
}