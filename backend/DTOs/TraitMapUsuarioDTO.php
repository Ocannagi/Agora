<?php

trait TraitMapUsuarioDTO
{
    use TraitMapDomicilioDTO; // Trait para mapear DomicilioDTO

    /**
     * Mapea un array o stdClass a un objeto UsuarioDTO.
     * @param array|stdClass $data Datos a mapear.
     * @return UsuarioDTO|array|null Objeto UsuarioDTO, array de dicho objeto, o null si no se puede mapear.
     */
    private function mapUsuarioDTO(array | stdClass $data, bool $returnArray = false): UsuarioDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayUsr = [];

        if (array_key_exists('usuario', $data))
            return $returnArray ? get_object_vars(new UsuarioDTO($data['usuario'])) : new UsuarioDTO($data['usuario']);

        if (array_key_exists('usrId', $data))
            $arrayUsr['usrId'] = (int)$data['usrId'];
        else if (array_key_exists('antUsrId', $data))
            $arrayUsr['usrId'] = (int)$data['antUsrId'];
        else if (array_key_exists('tadUsrTasId', $data))
            $arrayUsr['usrId'] = (int)$data['tadUsrTasId'];
        else if (array_key_exists('tadUsrPropId', $data))
            $arrayUsr['usrId'] = (int)$data['tadUsrPropId'];
        else if (array_key_exists('udomUsr', $data))
            $arrayUsr['usrId'] = (int)$data['udomUsr'];
        else if (array_key_exists('covUsrComprador', $data))
            $arrayUsr['usrId'] = (int)$data['covUsrComprador'];
        else if (array_key_exists('aavUsrIdVendedor', $data))
            $arrayUsr['usrId'] = (int)$data['aavUsrIdVendedor'];
        else
            return null;

        if (array_key_exists('usrDni', $data))
            $arrayUsr['usrDni'] = (string)$data['usrDni'];

        if (array_key_exists('usrApellido', $data))
            $arrayUsr['usrApellido'] = (string)$data['usrApellido'];

        if (array_key_exists('usrNombre', $data))
            $arrayUsr['usrNombre'] = (string)$data['usrNombre'];

        if (array_key_exists('usrRazonSocialFantasia', $data))
            $arrayUsr['usrRazonSocialFantasia'] = (string)$data['usrRazonSocialFantasia'];

        if (array_key_exists('usrCuitCuil', $data))
            $arrayUsr['usrCuitCuil'] = (string)$data['usrCuitCuil'];

        if (array_key_exists('usrTipoUsuario', $data))
            $arrayUsr['usrTipoUsuario'] = (string)$data['usrTipoUsuario'];

        if (array_key_exists('usrMatricula', $data))
            $arrayUsr['usrMatricula'] = (string)$data['usrMatricula'];

        if (array_key_exists('domicilio', $data) && $data['domicilio'] instanceof DomicilioDTO) {
            $arrayUsr['domicilio'] = $data['domicilio'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data, $returnArray);
            if ($domicilioDTO !== null) {
                $arrayUsr['domicilio'] = $domicilioDTO;
            }
        }

        if (array_key_exists('usrFechaNacimiento', $data))
            $arrayUsr['usrFechaNacimiento'] = (string)$data['usrFechaNacimiento'];

        if (array_key_exists('usrDescripcion', $data))
            $arrayUsr['usrDescripcion'] = (string)$data['usrDescripcion'];

        if (array_key_exists('usrScoring', $data))
            $arrayUsr['usrScoring'] = (int)$data['usrScoring'];

        if (array_key_exists('usrEmail', $data))
            $arrayUsr['usrEmail'] = (string)$data['usrEmail'];

        if (array_key_exists('usrPassword', $data))
            $arrayUsr['usrPassword'] = (string)$data['usrPassword'];

        return $returnArray ? $arrayUsr : new UsuarioDTO($arrayUsr);
    }
}
