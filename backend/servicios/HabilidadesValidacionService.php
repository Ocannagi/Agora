<?php

use Utilidades\Output;
use Utilidades\Input;


class HabilidadesValidacionService extends ValidacionServiceBase
{
    private static $instancia = null; // La única instancia de la clase
    
    
    private function __construct() {}

    // Método público para obtener la instancia única
    public static function getInstancia(): HabilidadesValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self(); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $habilidadCreacionDTO)
    {
        // No se puede modificar una habilidad, sólo se puede dar de baja.
        if (!$habilidadCreacionDTO instanceof habilidadCreacionDTO) {
            Output::outputError(500, 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }
        
        $this->validarDatosObligatorios(classModelName: 'Habilidad', datos: get_object_vars($habilidadCreacionDTO));

        $this->validarDatoIdUsuario($habilidadCreacionDTO->usuario->usrId, $linkExterno);
        $this->validarDatoIdPeriodo($habilidadCreacionDTO->periodo, $linkExterno);
        $this->validarDatoIdSubcategoria($habilidadCreacionDTO->subcategoria, $linkExterno);
        $this->validarSiYaFueRegistrado($habilidadCreacionDTO->usuario->usrId, $habilidadCreacionDTO->periodo->perId, $habilidadCreacionDTO->subcategoria->scatId, $linkExterno);
    }

    private function validarDatoIdUsuario(int $usrId, mysqli $linkExterno)
    {
        if(!isset($usrId)) {
            Output::outputError(400, 'El id del usuario no fue proporcionado.');
        }
        
        if ($usrId <= 0) {
            Output::outputError(400, "El ID del usuario no es válido: $usrId.");
        }

        if (!$this->existeUsuario($usrId, $linkExterno)) {
            Output::outputError(409, "El usuario con ID $usrId no existe.");
        }
    }

    private function existeUsuario(int $usrId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT usrId FROM usuario WHERE usrId = $usrId AND usrFechaBaja IS NULL", msg: 'obtener un usuario por id');
    }

    private function validarDatoIdPeriodo(PeriodoDTO $periodoDTO, mysqli $linkExterno)
    {
        if (!isset($periodoDTO->perId)) {
            Output::outputError(400, 'El id del periodo no fue proporcionado.');
        }
        
        if ($periodoDTO->perId <= 0) {
            Output::outputError(400, "El ID del periodo no es válido: $periodoDTO->perId.");
        }

        if (!$this->existePeriodo($periodoDTO->perId, $linkExterno)) {
            Output::outputError(409, "El periodo con ID $periodoDTO->perId no existe.");
        }
    }

    private function existePeriodo(int $perId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT perId FROM periodo WHERE perId = $perId AND perFechaBaja IS NULL", msg: 'obtener un periodo por id');
    }

    private function validarDatoIdSubcategoria(SubcategoriaDTO $subcategoriaDTO, mysqli $linkExterno)
    {
        if (!isset($subcategoriaDTO->scatId)) {
            Output::outputError(400, 'El id de la subcategoría no fue proporcionado.');
        }
        
        if ($subcategoriaDTO->scatId <= 0) {
            Output::outputError(400, "El ID de la subcategoría no es válido: $subcategoriaDTO->scatId.");
        }

        if (!$this->existeSubcategoria($subcategoriaDTO->scatId, $linkExterno)) {
            Output::outputError(409, "La subcategoría con ID $subcategoriaDTO->scatId no existe.");
        }
    }

    private function existeSubcategoria(int $scatId, mysqli $linkExterno): bool
    {
        return $this->_existeEnBD(link: $linkExterno, query: "SELECT scatId FROM subcategoria WHERE scatId = $scatId AND scatFechaBaja IS NULL", msg: 'obtener una subcategoría por id');
    }

    private function validarSiYaFueRegistrado(int $usrId, int $perId, int $scatId, mysqli $linkExterno)
    {
        if ($this->_existeEnBD(link: $linkExterno, query: "SELECT 1 FROM usuariotasadorhabilidad WHERE utsUsrId = $usrId AND utsPerId = $perId AND utsScatId = $scatId AND utsFechaBaja IS NULL", msg: 'obtener una habilidad por id')) {
            Output::outputError(409, "La habilidad ya fue registrada para el usuario con ID $usrId para el periodo con ID $perId y la subcategoría con ID $scatId.");
        }
    }


}