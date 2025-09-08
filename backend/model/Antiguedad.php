<?php
use Utilidades\Obligatorio;

class Antiguedad extends ClassBase
{
    private int $antId;
    #[Obligatorio]
    private Periodo $periodo;
    #[Obligatorio]
    private Subcategoria $subcategoria;
    #[Obligatorio]
    private string $antDescripcion;
    #[Obligatorio]
    private Usuario $usuario;
    /** @var ?ImagenAntiguedad[] */
    private ?array $imagenes = null;
    private DateTime $antFechaInsert;
    private TipoEstadoEnum $tipoEstado;
    private DateTime $antFechaEstado;
    
    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof AntiguedadCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->periodo = Periodo::fromArray(get_object_vars($dto->periodo));
        $instance->subcategoria = Subcategoria::fromArray(['scatId' => $dto->subcategoria->scatId]);
        $instance->antDescripcion = $dto->antDescripcion;
        $instance->usuario = Usuario::fromArray(['usrId' => $dto->usuario->usrId]);
        $instance->tipoEstado = $dto->tipoEstado;
        return $instance;
    }
}