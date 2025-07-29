<?php

use Model\CustomException;

class UsuariosDomiciliosValidacionService extends ValidacionServiceBase
{
    private static $instancia = null; // La única instancia de la clase
    
    
    private function __construct() {}

    // Método público para obtener la instancia única
    public static function getInstancia(): UsuariosDomiciliosValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self(); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $usuarioDomicilioCreacionDTO, mixed $extraParams = null): void
    {
        // No se puede modificar un usuarioDomicilio, sólo se puede dar de baja.
        if (!$usuarioDomicilioCreacionDTO instanceof UsuarioDomicilioCreacionDTO) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        $this->validarDatosObligatorios(classModelName: 'UsuarioDomicilio', datos: get_object_vars($usuarioDomicilioCreacionDTO));

        $this->validarDatoIdUsuario($usuarioDomicilioCreacionDTO->usuario->usrId, $linkExterno);
        $this->validarDatoIdDomicilio($usuarioDomicilioCreacionDTO->domicilio->domId, $linkExterno);
        $this->validarSiYaFueRegistrado($usuarioDomicilioCreacionDTO->usuario->usrId, $usuarioDomicilioCreacionDTO->domicilio->domId, $linkExterno);
    }

    private function validarDatoIdUsuario(int $usrId, mysqli $linkExterno)
    {
        if(!isset($usrId)) {
            throw new InvalidArgumentException(message: 'El id del usuario no fue proporcionado.');
        }
        
        if ($usrId <= 0) {
            throw new InvalidArgumentException(message: "El ID del usuario no es válido: $usrId.");
        }

        if (!$this->existeUsuario($usrId, $linkExterno)) {
            throw new CustomException(code: 409, message: "El usuario con ID $usrId no existe.");
        }
    }

    private function existeUsuario(int $usrId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT usrId FROM usuario WHERE usrId = $usrId AND usrFechaBaja IS NULL", msg: 'obtener un usuario por id');
    }

    private function validarDatoIdDomicilio(int $domId, mysqli $linkExterno)
    {
        if(!isset($domId)) {
            throw new InvalidArgumentException(message: 'El id del domicilio no fue proporcionado.');
        }
        
        if ($domId <= 0) {
            throw new InvalidArgumentException(message: "El ID del domicilio no es válido: $domId.");
        }

        if (!$this->existeDomicilio($domId, $linkExterno)) {
            throw new CustomException(code: 409, message: "El domicilio con ID $domId no existe.");
        }
    }

    private function existeDomicilio(int $domId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT domId FROM domicilio WHERE domId = $domId AND domFechaBaja IS NULL", msg: 'obtener un domicilio por id');
    }

    private function validarSiYaFueRegistrado(int $usrId, int $domId, mysqli $linkExterno)
    {
        if ($this->_existeEnBD(link: $linkExterno, query: "SELECT udomId FROM usuariodomicilio WHERE udomUsr = $usrId AND udomDom = $domId AND udomFechaBaja IS NULL", msg: 'verificar si el usuario ya tiene un domicilio registrado')) {
            throw new CustomException(code: 409, message: "El usuario con ID $usrId ya tiene el domicilio con ID $domId registrado.");
        }
    }
}