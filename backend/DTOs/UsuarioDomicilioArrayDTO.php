<?php

class UsuarioDomicilioArrayDTO implements IDTO
{
    public UsuarioDTO $usuario;

    /**
     * @var DomicilioDTO[]
     */
    public array $domicilios = []; // Array de DomicilioDTO

    use TraitMapUsuarioDTO;
    use TraitMapDomicilioDTO;

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $this->usuario = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data);
            if ($usuarioDTO !== null) {
                $this->usuario = $usuarioDTO;
            }
        }

        if (array_key_exists('domicilios', $data) && is_array($data['domicilios'])) {
            foreach ($data['domicilios'] as $domicilio) {
                if ($domicilio instanceof DomicilioDTO) {
                    $this->domicilios[] = $domicilio;
                } else {
                    $domicilioDTO = $this->mapDomicilioDTO($domicilio);
                    if ($domicilioDTO !== null) {
                        $this->domicilios[] = $domicilioDTO;
                    }
                }
            }
        }
    }
}
