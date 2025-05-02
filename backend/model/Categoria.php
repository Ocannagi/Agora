<?php
use Utilidades\Obligatorio;

class Categoria extends ClassBase {
    private int $catId;
    #[Obligatorio]
    private string $catDescripcion;
    private ?DateTime $catFechaBaja;

    public static function fromCreacionDTO(ICreacionDTO $dto) : self
    {
        if(!$dto instanceof CategoriaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }
        
        $instance = new self();
        $instance->catDescripcion = $dto->catDescripcion;
        return $instance;
    }
}