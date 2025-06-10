<?php

use Model\CustomException;
use Utilidades\Input;

class AntiguedadesValidacionService extends ValidacionServiceBase
{
    private static $instancia = null;

    private function __construct()
    {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstancia(): AntiguedadesValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone()
    {
        // Previene la clonación de la instancia
    }

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $antiguedad)
    {
        if (!($antiguedad instanceof AntiguedadCreacionDTO) && !($antiguedad instanceof AntiguedadDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'Antiguedad', datos: get_object_vars($antiguedad));
        Input::trimStringDatos($antiguedad);

        $this->validarPeriodo($antiguedad->periodo, $linkExterno);
        $this->validarSubcategoria($antiguedad->subcategoria, $linkExterno);
        $this->validarDescripcion($antiguedad->antDescripcion);
        $this->validarUsuario($antiguedad->usuario, $linkExterno);

        if ($antiguedad instanceof AntiguedadDTO) {
            $this->validarTipoEstado($antiguedad->tipoEstado);
            $this->validarExisteAntiguedadModificar($antiguedad->antId, $linkExterno);
        } else {
            $this->validarSiYaFueRegistradoPorMismoUsuario($antiguedad, $linkExterno);
        }
    }

    private function validarPeriodo(PeriodoDTO $periodoDTO, mysqli $linkExterno)
    {
        if (!($periodoDTO instanceof PeriodoDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de periodo no es del tipo correcto.');
        }

        if (!isset($periodoDTO->perId)) {
            throw new InvalidArgumentException(message:'El id del periodo no fue proporcionado.');
        }

        if (!is_int($periodoDTO->perId)) {
            throw new InvalidArgumentException(message:'El id del periodo no es un número entero.');
        }

        if ($periodoDTO->perId <= 0) {
            throw new InvalidArgumentException(message:"El id del periodo no es válido: $periodoDTO->perId");
        }

        if (!$this->_existePeriodo($periodoDTO->perId, $linkExterno)) {
            throw new CustomException(code: 409, message: "El periodo con id $periodoDTO->perId no existe.");
        }
    }

    private function _existePeriodo(int $perId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM periodo WHERE perId=$perId";
        return $this->_existeEnBD($linkExterno, $query, "obtener un periodo por id");
    }

    private function validarSubcategoria(SubcategoriaDTO $subcategoriaDTO, mysqli $linkExterno)
    {
        if (!($subcategoriaDTO instanceof SubcategoriaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de subcategoría no es del tipo correcto.');
        }

        if (!isset($subcategoriaDTO->scatId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría no fue proporcionado.');
        }

        if (!is_int($subcategoriaDTO->scatId)) {
            throw new InvalidArgumentException(message: 'El id de la subcategoría no es un número entero.');
        }

        if ($subcategoriaDTO->scatId <= 0) {
            throw new InvalidArgumentException(message: "El id de la subcategoría no es válido: $subcategoriaDTO->scatId");
        }

        if (!$this->_existeSubcategoria($subcategoriaDTO->scatId, $linkExterno)) {
            throw new CustomException(code: 409, message: "La subcategoría con id $subcategoriaDTO->scatId no existe.");
        }
    }

    private function _existeSubcategoria(int $scatId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM subcategoria WHERE scatId=$scatId";
        return $this->_existeEnBD($linkExterno, $query, "obtener una subcategoría por id");
    }

    private function validarDescripcion(string $descripcion)
    {
        if (!$this->_esStringLongitud($descripcion, 1, 500)) {
            throw new InvalidArgumentException(message: 'La Descripción de la antigüedad debe ser un string de al menos un caracter y un máximo de 500.');
        }
    }

    private function validarUsuario(UsuarioDTO $usuarioDTO, mysqli $linkExterno)
    {
        if (!($usuarioDTO instanceof UsuarioDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de usuario no es del tipo correcto.');
        }

        if (!isset($usuarioDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del usuario no fue proporcionado.');
        }

        if (!is_int($usuarioDTO->usrId)) {
            throw new InvalidArgumentException(message: 'El id del usuario no es un número entero.');
        }

        if ($usuarioDTO->usrId <= 0) {
            throw new InvalidArgumentException(message: "El id del usuario no es válido: $usuarioDTO->usrId");
        }

        if (!$this->_existeUsuario($usuarioDTO->usrId, $linkExterno)) {
            throw new CustomException(code: 409, message: "El usuario con id $usuarioDTO->usrId no existe.");
        }
    }

    private function _existeUsuario(int $usrId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM usuario WHERE usrId=$usrId";
        return $this->_existeEnBD($linkExterno, $query, "obtener un usuario por id");
    }

    private function validarTipoEstado(TipoEstadoEnum $tipoEstado)
    {
        if (!($tipoEstado instanceof TipoEstadoEnum)) {
            throw new CustomException(code: 500, message: 'Error interno: el tipo de estado no es del tipo correcto.');
        }

        // El tipo de estado se valida en el DTO de antigüedad, no es necesario validar aquí
    }

    private function validarExisteAntiguedadModificar(int $antId, mysqli $linkExterno)
    {
        if (!isset($antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no fue proporcionado.');
        }

        if (!is_int($antId)) {
            throw new InvalidArgumentException(message: 'El id de la antigüedad no es un número entero.');
        }

        if ($antId <= 0) {
            throw new InvalidArgumentException(message: "El id de la antigüedad no es válido: $antId");
        }

        if (!$this->_existeAntiguedad($antId, $linkExterno)) {
            throw new CustomException(code: 409, message: "La antigüedad con id $antId no existe.");
        }
    }

    private function _existeAntiguedad(int $antId, mysqli $linkExterno): bool
    {
        $query = "SELECT 1 FROM antiguedad WHERE antId=$antId";
        return $this->_existeEnBD($linkExterno, $query, "obtener una antigüedad por id");
    }

    private function validarSiYaFueRegistradoPorMismoUsuario(AntiguedadCreacionDTO $antiguedad, mysqli $linkExterno)
    {
        if (!($antiguedad instanceof AntiguedadCreacionDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO de antigüedad no es del tipo correcto.');
        }

        if ($this->_existeAntiguedadRegistradaPorUsuario($antiguedad, $linkExterno)) {
            throw new CustomException(code: 409, message: "Ya cuenta con una antigüedad con el mismo periodo, subcategoría y descripción.");
        }
    }

    private function _existeAntiguedadRegistradaPorUsuario(AntiguedadCreacionDTO $antiguedad, mysqli $linkExterno): bool
    {
         // Las antiguedades no pueden tener el mismo periodo, subcategoría y descripción para el mismo usuario
         // Se asume que el usuario ya fue validado y existe en la base de datos
         // Se asume que el periodo y la subcategoría ya fueron validados y existen en la base de datos
         // Las antiguedades no pueden darse de baja, solo pueden pasar al estado RN
        $query = "SELECT 1 FROM antiguedad WHERE antPerId={$antiguedad->periodo->perId} AND antScatId={$antiguedad->subcategoria->scatId} AND antDescripcion='{$antiguedad->antDescripcion}' AND antUsrId={$antiguedad->usuario->usrId}";
        return $this->_existeEnBD($linkExterno, $query, "verificar si ya existe una antigüedad registrada por el usuario");
    }

}