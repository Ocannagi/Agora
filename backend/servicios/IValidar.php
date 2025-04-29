<?php

interface IValidar
{
    public function validarInputUsuario(mysqli $linkExterno, UsuarioCreacionDTO | UsuarioDTO $usuario);
    public function validarType(string $className, array $datos);
}