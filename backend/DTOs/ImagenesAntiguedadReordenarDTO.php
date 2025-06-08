<?php

class ImagenesAntiguedadReordenarDTO implements IDTO
{
    public int $antId;
    /**
     * @var ImagenAntiguedadOrdenDTO[]
     */
    public array $imagenesAntiguedadOrden = []; // Array de ImagenAntiguedadOrdenDTO

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('antId', $data)) {
            $this->antId = (int)$data['antId'];
        } else if (array_key_exists('imaAntId', $data)) {
            $this->antId = (int)$data['imaAntId'];
        }

        if (array_key_exists('imagenesAntiguedadOrden', $data)) {
            if (is_array($data['imagenesAntiguedadOrden'])) {
                $this->imagenesAntiguedadOrden = [];
                foreach ($data['imagenesAntiguedadOrden'] as $imagenData) {
                    if ($imagenData instanceof ImagenAntiguedadOrdenDTO) {
                        $this->imagenesAntiguedadOrden[] = $imagenData;
                    } else {
                        $this->imagenesAntiguedadOrden[] = new ImagenAntiguedadOrdenDTO($imagenData);
                    }
                }
            }
        }
    }
}
