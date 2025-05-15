<?php

class HabilidadCreacionDTO implements ICreacionDTO
{
    public int $usrId; // ID del usuario al que pertenece la habilidad.
    public PeriodoDTO $periodo; // Periodo de la habilidad.
    public SubcategoriaDTO $subcategoria; // Subcategoría de la habilidad.

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('usrId', $data)) {
            $this->usrId = (int)$data['usrId'];
        } else if (array_key_exists('utsUsrId', $data)) {
            $this->usrId = (int)$data['utsUsrId'];
        } else {
            $this->usrId = 0;
        }
        
        if (array_key_exists('periodo', $data) && $data['periodo'] instanceof PeriodoDTO) {
            $this->periodo = $data['periodo'];
        } else {
            $this->periodo = new PeriodoDTO($data);
        }

        if (array_key_exists('subcategoria', $data) && $data['subcategoria'] instanceof SubcategoriaDTO) {
            $this->subcategoria = $data['subcategoria'];
        } else {
            $this->subcategoria = new SubcategoriaDTO($data);
        }

    }
}