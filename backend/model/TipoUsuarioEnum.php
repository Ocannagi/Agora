<?php

enum TipoUsuarioEnum: string
{
    case SeguridadInformatica = 'SI';
    case SoporteTecnico = 'ST';
    case UsuarioAnticuario = 'UA';
    case UsuarioTasador = 'UT';
    case UsuarioGeneral = 'UG';

    public function isCompradorVendedor(): bool
    {
        return in_array($this, [
            self::UsuarioAnticuario,
            self::UsuarioGeneral,
            self::SoporteTecnico // Solo para pruebas
        ]);
    }

    public function isTasador(): bool
    {
        return in_array($this, [
            self::UsuarioTasador,
            self::UsuarioAnticuario,
            self::SoporteTecnico // Solo para pruebas
        ]);
    }

    public function isSolicitanteTasacion(): bool
    {
        return in_array($this, [
            self::UsuarioGeneral,
            self::SoporteTecnico // Solo para pruebas
        ]);
    }

    public static function compradorVendedorToArray(): array
    {
        return [
            self::UsuarioAnticuario->value,
            self::UsuarioGeneral->value,
            self::SoporteTecnico->value // Solo para pruebas
        ];
    }

    public static function compradorVendedorToQuery(): string
    {
        $query = implode("', '", self::compradorVendedorToArray());
        return "usrTipoUsuario IN ('$query')";
    }

    public static function tasadorToArray(): array
    {
        return [
            self::UsuarioTasador->value,
            self::UsuarioAnticuario->value,
            self::SoporteTecnico->value // Solo para pruebas
        ];
    }

    public static function soporteTecnicoToArray(): array
    {
        return [
            self::SoporteTecnico->value
        ];
    }

    public static function solicitanteTasacionToArray(): array
    {
        return [
            self::UsuarioGeneral->value,
            self::SoporteTecnico->value // Solo para pruebas
        ];
    }


}
