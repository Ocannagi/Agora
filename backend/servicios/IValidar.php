<?php

interface IValidar
{
    public function validarInputUsuario(mysqli $linkExterno, UsuarioCreacionDTO $usuarioCreacionDTO, bool $soyModificacion);
}