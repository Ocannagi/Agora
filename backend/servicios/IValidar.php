<?php

interface IValidar
{
    public function validarInputUsuario(mysqli $linkExterno, UsuarioCreacionDTO | UsuarioDTO $usuario);
}