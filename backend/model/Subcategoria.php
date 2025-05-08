<?php

use Utilidades\Obligatorio;

class Subcategoria extends ClassBase {
    private int $scatId;
    #[Obligatorio]
    private Categoria $categoria; // Relación con la categoría
    #[Obligatorio]
    private string $scatDescripcion;
    private ?DateTime $scatFechaBaja;


    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof SubcategoriaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->categoria = Categoria::fromArray(get_object_vars($dto->categoria)); // Convertir el DTO de categoría a objeto
        $instance->scatDescripcion = $dto->scatDescripcion;
        return $instance;
    }
}