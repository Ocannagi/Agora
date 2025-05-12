<?php

class LocalidadDTO implements IDTO
{
    public int $locId;
    public string $locDescripcion;
    public ProvinciaDTO $provincia; // Relación con la provincia

    use TraitMapProvinciaDTO; // Trait para mapear ProvinciaDTO

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('locId', $data)) {
            $this->locId = (int)$data['locId'];
        }
        if (array_key_exists('locDescripcion', $data)) {
            $this->locDescripcion = (string)$data['locDescripcion'];
        }

        if (array_key_exists('provincia', $data) && $data['provincia'] instanceof ProvinciaDTO) {
            $this->provincia = $data['provincia'];
        } else {
            $provinciaDTO = $this->mapProvinciaDTO($data);
            if ($provinciaDTO !== null) {
                $this->provincia = $provinciaDTO;
            }
        }
    }
}
