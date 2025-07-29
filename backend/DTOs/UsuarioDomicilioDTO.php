<?php

class UsuarioDomicilioDTO implements IDTO
{
    public int $udomId; // Identificador único del registro de usuario-domicilio.
    public UsuarioDTO $usuario;
    public DomicilioDTO $domicilio;

    use TraitMapUsuarioDTO;
    use TraitMapDomicilioDTO;

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('udomId', $data)) {
            $this->udomId = (int)$data['udomId'];
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $this->usuario = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data);
            if ($usuarioDTO !== null) {
                $this->usuario = $usuarioDTO;
            }
        }

        if (array_key_exists('domicilio', $data) && $data['domicilio'] instanceof DomicilioDTO) {
            $this->domicilio = $data['domicilio'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data);
            if ($domicilioDTO !== null) {
                $this->domicilio = $domicilioDTO;
            }
        }
    }
}