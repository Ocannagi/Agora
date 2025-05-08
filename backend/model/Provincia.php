<?php

class Provincia extends ClassBase
{
    private int $provId;
    private string $provDescripcion;

    public static function fromCreacionDTO(ICreacionDTO $dto) : never
    {
        throw new Exception("No se puede crear una provincia desde un DTO de creación. Se supone que no puede haber provincias nuevas.");
    }
}