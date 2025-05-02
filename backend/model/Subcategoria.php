<?php

use Utilidades\Obligatorio;

class Subcategoria extends ClassBase {
    private int $scatId;
    #[Obligatorio]
    private int $scatCatId; // ID de la categoría a la que pertenece
    #[Obligatorio]
    private string $scatDescripcion;
    private ?DateTime $scatFechaBaja;


    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof SubcategoriaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->scatCatId = $dto->scatCatId; // Asignar el ID de la categoría
        $instance->scatDescripcion = $dto->scatDescripcion;
        return $instance;
    }
}